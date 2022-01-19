<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Model\Feed;

use Amasty\Base\Model\Feed\Response\FeedResponseInterface;
use Amasty\Base\Model\Feed\Response\FeedResponseInterfaceFactory;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class FeedContentProvider for reading file content by url
 */
class FeedContentProvider
{
    /**
     * Path to NEWS
     */
    const URN_NEWS = 'cdn.amasty.com/feed-news-segments.xml';//do not use https:// or http

    /**
     * Path to ADS
     */
    const URN_ADS = 'cdn.amasty.com/media/marketing/upsells.csv';

    /**
     * Path to EXTENSIONS
     */
    const URN_EXTENSIONS = 'cdn.amasty.com/feed-extensions-m2.xml';

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Zend\Uri\Uri
     */
    private $baseUrlObject;

    /**
     * @var FeedResponseInterfaceFactory
     */
    private $feedResponseFactory;

    public function __construct(
        CurlFactory $curlFactory,
        StoreManagerInterface $storeManager,
        FeedResponseInterfaceFactory $feedResponseFactory
    ) {
        $this->curlFactory = $curlFactory;
        $this->storeManager = $storeManager;
        $this->feedResponseFactory = $feedResponseFactory;
    }

    /**
     * @param string $url
     * @param array $options
     *
     * @return FeedResponseInterface
     */
    public function getFeedResponse(string $url, array $options = []): FeedResponseInterface
    {
        /** @var Curl $curlObject */
        $curlObject = $this->curlFactory->create();
        $curlObject->addOption(CURLOPT_ACCEPT_ENCODING, 'gzip');
        $curlObject->setConfig(
            [
                'timeout' => 2,
                'useragent' => 'Amasty Base Feed'
            ]
        );
        $headers = [];
        if (isset($options['modified_since'])) {
            $headers = ['If-Modified-Since: ' . $options['modified_since']];
        }
        $curlObject->write(\Zend_Http_Client::GET, $url, '1.1', $headers);
        $result = $curlObject->read();

        /** @var FeedResponseInterface $feedResponse */
        $feedResponse = $this->feedResponseFactory->create();
        if ($result === false || $result === '') {
            return $feedResponse;
        }
        $result = preg_split('/^\r?$/m', $result, 2);
        preg_match("/(?i)(\W|^)(Status: 404 File not found)(\W|$)/", $result[0], $notFoundFile);
        if ($notFoundFile) {
            return $feedResponse->setStatus('404');
        }
        preg_match("/(?i)(\W|^)(HTTP\/1.1 304)(\W|$)/", $result[0], $notModifiedFile);
        if ($notModifiedFile) {
            return $feedResponse->setStatus('304');
        }

        $result = trim($result[1]);
        $feedResponse->setContent($result);
        $curlObject->close();

        return $feedResponse;
    }

    public function getFeedUrl(string $urn): string
    {
        return 'https://' . $urn;
    }

    /**
     * @return string
     */
    public function getDomainZone()
    {
        $host = $this->getBaseUrlObject()->getHost();
        $host = explode('.', $host);

        return end($host);
    }

    /**
     * @return \Zend\Uri\Uri
     */
    private function getBaseUrlObject()
    {
        if ($this->baseUrlObject === null) {
            $url = $this->storeManager->getStore()->getBaseUrl();
            $this->baseUrlObject = \Zend\Uri\UriFactory::factory($url);
        }

        return $this->baseUrlObject;
    }
}
