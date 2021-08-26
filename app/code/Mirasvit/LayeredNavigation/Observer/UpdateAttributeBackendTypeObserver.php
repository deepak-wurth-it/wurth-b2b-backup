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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class UpdateAttributeBackendTypeObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $object */
        $object = $observer->getEvent()->getAttribute();

        if ($object->getFrontendInput() == 'text'
            && in_array($object->getFrontendClass(), ['validate-number', 'validate-digits'])) {
            $object->setBackendType('decimal');
            $object->setData('is_filterable_in_search', 1);
            $object->setData('is_filterable', 1);
        }

        return $this;
    }
}
