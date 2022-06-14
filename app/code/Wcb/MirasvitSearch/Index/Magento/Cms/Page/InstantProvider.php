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

namespace Wcb\MirasvitSearch\Index\Magento\Cms\Page;

use Magento\Cms\Model\Page;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Mirasvit\Search\Service\IndexService;

class InstantProvider extends \Mirasvit\Search\Index\Magento\Cms\Page\InstantProvider
{
    private $urlBuilder;

    public function __construct(
        Url $urlBuilder,
        IndexService $indexService
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($urlBuilder, $indexService);
    }

    public function getItems(int $storeId, int $limit): array
    {
        $items = [];

        foreach ($this->getCollection($limit) as $page) {
            $items[] = $this->mapPage($page, $storeId);
        }

        return $items;
    }

    private function mapPage(Page $page, int $storeId): array
    {
        $page = $page->setStoreId($storeId);
        $page = $page->load($page->getId());
        if ($page->getCmsImage()) {
            $imageUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . 'cmsimage/' . $page->getCmsImage();
        } else {
            $imageUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . "catalog/product/placeholder/default/replacement_product_2.png";
        }
        return [
            'name' => "CMS" . $page->getTitle(),
            'image' => $imageUrl,
            'url' => $this->urlBuilder->getUrl($page->getIdentifier(), ['_scope' => $storeId]),
        ];
    }

    public function getSize(int $storeId): int
    {
        return $this->getCollection(0)->getSize();
    }

    public function map(array $documentData, int $storeId): array
    {
        foreach ($documentData as $entityId => $itm) {
            $om = ObjectManager::getInstance();

            $entity = $om->create(Page::class)->load($entityId);

            $map = $this->mapPage($entity, $storeId);

            $documentData[$entityId][self::INSTANT_KEY] = $map;
        }

        return $documentData;
    }
}
