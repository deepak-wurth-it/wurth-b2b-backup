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

namespace Mirasvit\Search\Index\Magento\Catalog\Attribute;

use Magento\Framework\UrlFactory;
use Mirasvit\Search\Index\AbstractInstantProvider;
use Mirasvit\Search\Service\IndexService;

class InstantProvider extends AbstractInstantProvider
{
    private $url;

    public function __construct(
        UrlFactory $url,
        IndexService $indexService
    ) {
        $this->url = $url;

        parent::__construct($indexService);
    }

    public function getItems(int $storeId, int $limit): array
    {
        $items = [];

        /** @var \Magento\Framework\DataObject $model */
        foreach ($this->getCollection($limit) as $model) {
            $items[] = $this->mapItem($model->toArray(), $storeId);
        }

        return $items;
    }

    public function getSize(int $storeId): int
    {
        return $this->getCollection(0)->getSize();
    }

    public function map(array $documentData, int $storeId): array
    {
        foreach ($documentData as $entityId => $itm) {

            $map = $this->mapItem($itm, $storeId);

            $documentData[$entityId]['_instant'] = $map;
        }

        return $documentData;
    }

    private function mapItem(array $data, int $storeId): ?array
    {
        if (!isset($data['value']) || !isset($data['label'])) {
            return null;
        }

        $attr = $this->index->getProperties()['attribute'];

        $url = $this->url->create()
            ->getUrl('catalogsearch/advanced/result', ['_query' => ["{$attr}[]" => $data['value']]]);

        return [
            'name' => $data['label'],
            'url'  => $url,
        ];
    }
}
