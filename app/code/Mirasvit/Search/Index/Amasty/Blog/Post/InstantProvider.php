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

namespace Mirasvit\Search\Index\Amasty\Blog\Post;

use Mirasvit\Search\Index\AbstractInstantProvider;

class InstantProvider extends AbstractInstantProvider
{
    private $urlBuilder = null;

    public function getItems(int $storeId, int $limit): array
    {
        $items = [];

        foreach ($this->getCollection($limit) as $model) {
            $items[] = $this->mapItem($model, $storeId);
        }

        return $items;
    }

    public function getSize(int $storeId): int
    {
        return $this->getCollection(0)->getSize();
    }

    public function map(array $documentData, int $storeId): array
    {
        return $documentData;
    }

    /**
     * @param object $model
     */
    private function mapItem($model, int $storeId): array
    {
        return [
            'name' => $model->getTitle(),
            'url'  => $model->getPostUrl() ? $model->getPostUrl() : $model->getUrl(),
        ];
    }
}
