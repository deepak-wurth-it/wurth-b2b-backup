<?php

namespace Wcb\Catalog\Controller;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\App\Router\NoRouteHandlerInterface;

class NoRouteHandler implements NoRouteHandlerInterface
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * NoRouteHandler constructor.
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function process(Request $request)
    {
        if ($this->request->getFullActionName() == 'catalog_product_noroute') {
            $request->setModuleName('wcbcatalog')
                ->setControllerName('noroute')
                ->setActionName('product');
        } else {
            $request->setModuleName('cms')
                ->setControllerName('noroute')
                ->setActionName('index');
        }
        return false;
    }
}
