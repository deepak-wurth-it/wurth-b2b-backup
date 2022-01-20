<?php

namespace Amasty\Promo\Plugin\Reorder;

class Reorder
{
    /**
     * @param \Magento\Sales\Controller\AbstractController\Reorder $subject
     */
    public function beforeExecute(\Magento\Sales\Controller\AbstractController\Reorder $subject)
    {
        \Amasty\Promo\Model\Storage::$isReorder = true;
    }
}
