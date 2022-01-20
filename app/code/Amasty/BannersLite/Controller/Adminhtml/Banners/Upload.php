<?php

namespace Amasty\BannersLite\Controller\Adminhtml\Banners;

use Magento\Framework\Controller\ResultFactory;

class Upload extends \Magento\Backend\App\Action
{
    const PARAM_NAME = 'banner_image';

    /**
     * Image uploader
     *
     * @var \Amasty\BannersLite\Model\BannerImageUpload
     */
    private $imageUploader;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\BannersLite\Model\BannerImageUpload $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->imageUploader->saveFileToTmpDir(self::PARAM_NAME);

            $session = $this->_getSession();
            $result['cookie'] = [
                'name' => $session->getName(),
                'value' => $session->getSessionId(),
                'lifetime' => $session->getCookieLifetime(),
                'path' => $session->getCookiePath(),
                'domain' => $session->getCookieDomain(),
                'upload' => true
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_SalesRule::quote');
    }
}
