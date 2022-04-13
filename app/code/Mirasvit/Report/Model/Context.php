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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\Url as BackendUrl;
use Mirasvit\ReportApi\Api\SchemaInterface;

class Context
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SchemaInterface
     */
    protected $provider;
    /**
     * @var BackendUrl
     */
    public $urlManager;

    /**
     * Context constructor.
     * @param SchemaInterface $provider
     * @param RequestInterface $request
     * @param BackendUrl $urlManager
     */
    public function __construct(
        SchemaInterface $provider,
        RequestInterface $request,
        BackendUrl $urlManager
    ) {
        $this->provider = $provider;
        $this->request = $request;
        $this->urlManager = $urlManager;
    }

    /**
     * @return SchemaInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return BackendUrl
     */
    public function getUrlManager()
    {
        return $this->urlManager;
    }
}
