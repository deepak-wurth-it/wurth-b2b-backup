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

namespace Mirasvit\AllProducts\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Registry;
use Mirasvit\AllProducts\Service\UrlService;

class Router implements RouterInterface
{
    private $actionFactory;

    private $registry;

    private $url;

    private $eventManager;

    public function __construct(
        UrlService $url,
        ActionFactory $actionFactory,
        EventManagerInterface $eventManager,
        Registry $registry
    ) {
        $this->url           = $url;
        $this->actionFactory = $actionFactory;
        $this->eventManager  = $eventManager;
        $this->registry      = $registry;
    }

    /** {@inheritdoc} */
    public function match(RequestInterface $request)
    {
        $pathInfo = $request->getPathInfo();

        $result = $this->url->match($pathInfo);

        if ($result) {
            $params = $result->getParams();

            $request
                ->setAlias(
                    \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
                    ltrim($request->getOriginalPathInfo(), '/')
                )
                ->setModuleName($result->getModuleName())
                ->setControllerName($result->getControllerName())
                ->setActionName($result->getActionName())
                ->setParams($params);

            $this->registry->register(UrlService::IS_CORRECT_URL, true, true);

            return $this->actionFactory->create(
                'Magento\Framework\App\Action\Forward'
            //                ['request' => $request] //invoked with 2 parameters, 1 required
            );
        }

        return false;
    }
}
