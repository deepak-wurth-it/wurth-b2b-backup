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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Plugin\Frontend;

use Mirasvit\Search\Model\ConfigProvider;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlFactory;

class NoRoutePlugin
{
    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Request\Http|\Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * @var array
     */
    private $mediaTypes = [
        'jpg',
        'jpeg',
        'gif',
        'png',
        'css',
        'js',
        'ttf',
        'eot',
        'svg',
        'woff',
        'woff2',
        'ico',
        'map',
        'txt',
        'xml',
    ];

    /**
     * NoRoutePlugin constructor.
     *
     * @param ConfigProvider   $config
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param UrlFactory       $urlFactory
     */
    public function __construct(
        ConfigProvider $config,
        RequestInterface $request,
        ManagerInterface $messageManager,
        UrlFactory $urlFactory
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->urlFactory = $urlFactory;
    }

    /**
     * @param HttpInterface $response
     * @return void
     */
    public function beforeSendResponse(HttpInterface $response)
    {
        /** @var \Magento\Framework\App\Response\Http $response */
        if ($response->getHttpResponseCode() == 404 && $this->config->is404ToSearch()) {
            $extension = pathinfo($this->request->getRequestString(), PATHINFO_EXTENSION);

            if (!$this->request->isGet() || in_array($extension, $this->mediaTypes)) {
                return;
            }

            $searchQuery = $this->getSearchQuery($this->request);

            if (!$searchQuery) {
                return;
            }

            $message = (string)__('The page you requested was not found, but we have searched for relevant content.');

            $this->messageManager->addNoticeMessage($message);

            $url = $this->urlFactory->create()
                ->addQueryParams(['q' => $searchQuery, '404' => true])
                ->getUrl('catalogsearch/result');

            $response
                ->setRedirect($url, 301)
                ->setStatusCode(301)
                ->setHttpResponseCode(301)
                ->setHeader('Status', 301, true);
        }
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @return string
     */
    protected function getSearchQuery($request)
    {
        $ignored = [
            'html',
            'php',
            'catalog',
            'catalogsearch',
            'search',
            'rma',
            'account',
            'customer',
            'helpdesk',
            'wishlist',
            'newsletter',
            'contact',
            'sendfriend',
            'product_compare',
            'review',
            'product',
            'checkout',
            'paypal',
            'sales',
            'downloadable',
            'rewards',
            'credit',
        ];

        $maxQueryLength = 128;
        $expr = '/(\W|' . implode('|', $ignored) . ')+/';
        $requestString = preg_replace($expr, ' ', $request->getRequestString());

        $terms = preg_split('/[ \- \\/_]/', $requestString);
        $terms = array_filter(array_unique($terms));

        return trim(substr(implode(' ', $terms), 0, $maxQueryLength));
    }
}
