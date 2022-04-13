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

namespace Mirasvit\Search\Index\Magento\Search\Query;

use Magento\Cms\Model\Page;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url;
use Magento\Search\Model\Query;
use Mirasvit\Search\Index\AbstractInstantProvider;
use Mirasvit\Search\Service\IndexService;

class InstantProvider extends AbstractInstantProvider
{
    private $urlBuilder;

    private $request;

    public function __construct(
        Url              $urlBuilder,
        IndexService     $indexService,
        RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request    = $request;

        parent::__construct($indexService);
    }

    public function getItems(int $storeId, int $limit): array
    {
        $query = $this->request->getParam('q');
        $items = [];

        foreach ($this->getCollection($limit) as $itm) {
            $item = $this->mapQuery($itm);
            if (trim($item['query_text']) == trim($query)) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }

    public function getSize(int $storeId): int
    {
        $size = $this->getCollection(0)->getSize();
        return  $size > 1 ? $size : 0;
    }

    public function map(array $documentData, int $storeId): array
    {
        foreach ($documentData as $entityId => $itm) {
            $om = ObjectManager::getInstance();

            $entity = $om->create(Page::class)->load($entityId);

            $map = $this->mapQuery($entity);

            $documentData[$entityId][self::INSTANT_KEY] = $map;
        }

        return $documentData;
    }

    private function mapQuery(Query $query): array
    {
        return [
            'query_text'  => strtolower($query->getQueryText()),
            'num_results' => $query->getNumResults(),
            'url'         => $this->urlBuilder->getUrl('catalogsearch/result', ['_query' => ['q' => $query->getQueryText()]]),
        ];
    }
}
