<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Model\Import\Behavior;

/**
 * @since 1.4.6
 */
interface BehaviorInterface
{
    /**
     * @param array $importData
     *
     * @return \Magento\Framework\DataObject|void
     */
    public function execute(array $importData);
}
