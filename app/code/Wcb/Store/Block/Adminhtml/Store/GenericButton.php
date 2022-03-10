<?php

/**
 *
 * @category  Wcb
 * @package   Wcb_Store
 * @author    Deepak Kumar <deepak.kumar.rai@wuerth-it.com>
 * @copyright 2019 Wcb technologies (I) Pvt. Ltd
 */

namespace Wcb\Store\Block\Adminhtml\Store;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    )
    {
        $this->context = $context;
        $this->registry = $registry;
    }

    /**
     * Return Current Store ID
     *
     * @return int|null
     */
    public function getId()
    {
        $store = $this->registry->registry('store_data');
        return $store ? $store->getId() : null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
