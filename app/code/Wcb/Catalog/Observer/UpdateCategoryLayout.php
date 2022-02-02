<?php

namespace Wcb\Catalog\Observer;

use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class UpdateCategoryLayout implements ObserverInterface
{
    const ACTION_NAME = 'catalog_category_view';

    /** @var Registry */
    private $registry;

    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function execute(Observer $observer)
    {
        if ($observer->getFullActionName() !== self::ACTION_NAME) {
            return;
        }

        $category = $this->registry->registry('current_category');

        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $layout->getUpdate()->addHandle(self::ACTION_NAME . '_level_' . $category->getLevel());
    }
}