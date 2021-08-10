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
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Service;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\HTTP\Adapter\CurlFactory;

class FeedService
{
    private $curlFactory;

    private $cache;

    public function __construct(
        CurlFactory $curlFactory,
        CacheInterface $cache
    ) {
        $this->curlFactory = $curlFactory;
        $this->cache       = $cache;
    }

    /**
     * @param string $url
     * @param int    $cacheLifetime
     *
     * @return array|null
     */
    public function load($url, $cacheLifetime = 60 * 60)
    {
        if ($cache = $this->cache->load($url)) {
            $response = $cache;
        } else {
            $curl = $this->curlFactory->create();

            $curl->setConfig([
                'timeout' => 5,
            ]);

            $curl->write(\Zend_Http_Client::GET, $url, '1.1');

            $response = $curl->read();

            $response = preg_split('/^\r?$/m', $response, 2);
            $response = trim($response[1]);

            $this->cache->save($response, $url, [$url], $cacheLifetime);
        }

        $response = SerializeService::decode($response);

        return $response;
    }

    /**
     * @param string $fileName
     *
     * @return array|null
     */
    public function loadLocal($fileName)
    {
        $filePath = dirname(dirname(__FILE__)) . '/Setup/data/' . $fileName;

        try {
            return SerializeService::decode(file_get_contents($filePath));
        } catch (\Exception $e) {
        }

        return null;
    }
}
