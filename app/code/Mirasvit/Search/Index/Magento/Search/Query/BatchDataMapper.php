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



namespace Mirasvit\Search\Index\Magento\Search\Query;

use Mirasvit\Search\Index\AbstractBatchDataMapper;

class BatchDataMapper extends AbstractBatchDataMapper
{
    public function map(array $documentData, $storeId, array $context = [])
    {
//        foreach ($documentData as &$item) {
//            foreach ($item as &$value) {
//                $value = strtolower($value);
//            }
//        }

        return parent::map($documentData, $storeId, $context);
    }
}
