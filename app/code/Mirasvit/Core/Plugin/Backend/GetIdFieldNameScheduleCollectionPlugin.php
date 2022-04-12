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
 * @package   mirasvit/module-core
 * @version   1.3.3
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Plugin\Backend;

use Mirasvit\Core\Service\CompatibilityService;

/**
 * @see \Magento\Cron\Model\ResourceModel\Schedule\Collection::getIdFieldName()
 */
class GetIdFieldNameScheduleCollectionPlugin
{
    /**
     * @param \Magento\Framework\Data\Collection\AbstractDb $subject
     * @param string $fieldName
     *
     * @return mixed
     */
    public function afterGetIdFieldName($subject, $fieldName)
    {
        if (!$fieldName && CompatibilityService::is21()) {
            return $subject->getResource()->getIdFieldName();
        }

        return $fieldName;
    }
}
