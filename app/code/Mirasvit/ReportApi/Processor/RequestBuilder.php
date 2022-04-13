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
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Processor;

use Mirasvit\ReportApi\Api\RequestBuilderInterface;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Api\RequestInterfaceFactory;

class RequestBuilder implements RequestBuilderInterface
{
    /**
     * @var RequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * RequestBuilder constructor.
     * @param RequestInterfaceFactory $requestFactory
     */
    public function __construct(
        RequestInterfaceFactory $requestFactory
    ) {
        $this->requestFactory = $requestFactory;
    }

    /**
     * @return RequestInterface
     */
    public function create()
    {
        return $this->requestFactory->create();
    }
}
