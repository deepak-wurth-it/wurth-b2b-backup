<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wcb\Sales\Block\Order;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Framework\App\ResourceConnection;

class Undelivered extends \Wcb\Sales\Block\Order\History
{

    
    protected $_template = 'Wcb_Sales::order/undelivered-lines.phtml';




    public function _prepareLayout()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('wcb_breadcrumb');
        $baseUrl = $this->context->getStoreManager()->getStore()->getBaseUrl();

        if ($breadcrumbsBlock) {

            $breadcrumbsBlock->addCrumb(
                'online_shop',
                [
                    'label' => __('Online Shop'), //lable on breadCrumbes
                    'title' => __('Online Shop'),
                    'link' => $baseUrl
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'tracking_order',
                [
                    'label' => __('Undelivered Item'),
                    'title' => __('Undelivered Item'),
                    'link' => '/wcbsales/order/undelivered/'
                ]
            );
        }
        $this->pageConfig->getTitle()->set(__('Undelivered Item')); // set page name
        return parent::_prepareLayout();
    }
}
