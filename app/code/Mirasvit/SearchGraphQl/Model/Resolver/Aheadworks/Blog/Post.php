<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SearchGraphQl\Model\Resolver\Aheadworks\Blog;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Post implements ResolverInterface
{
    private $objectManager;

    private $urlBuilder = null;

    public function __construct() {
        $this->objectManager = ObjectManager::getInstance();
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $collection = $value['collection'];
        $collection->setPageSize($args['pageSize'])
            ->setCurPage($args['currentPage']);

        $items = [];

        foreach ($collection as $post) {
            $items[] = [
                'name' => $post->getPostTitle(),
                'url'  => $this->getUrlBulder()->getPostUrl($post),
            ];
        }

        return $items;
    }

    private function getUrlBulder()
    {
        if ($this->urlBuilder === null) {
            $this->urlBuilder = $this->objectManager->create('Aheadworks\Blog\Model\Url');
        }

        return $this->urlBuilder;
    }
}
