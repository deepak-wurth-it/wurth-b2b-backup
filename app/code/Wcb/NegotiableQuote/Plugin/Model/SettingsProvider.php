<?php

namespace Wcb\NegotiableQuote\Plugin\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\NegotiableQuote\Model\SettingsProvider as VendorSettingsProvider;
use Magento\Store\Model\StoreManagerInterface;

class SettingsProvider
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * SettingsProvider constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @param VendorSettingsProvider $subject
     * @param $data
     * @param string $message
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeRetrieveJsonSuccess(VendorSettingsProvider $subject, $data, $message = '')
    {
        if (isset($data['url'])) {
            if (strpos($data['url'], 'negotiable_quote/quote') !== false) {
                $data['url'] = $this->storeManager->getStore()->getUrl('requestquote/index/success');
            }
        }
        return [$data, $message];
    }
}
