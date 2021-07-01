<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Plazathemes\Override\Controller\Adminhtml\Category\Image;

use Magento\Framework\Controller\ResultFactory;

class Upload extends \Magento\Catalog\Controller\Adminhtml\Category\Image\Upload
{

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->imageUploader->saveFileToTmpDir('image');

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
		
		if($result['error'] != '')
		{
			try {
				$result = $this->imageUploader->saveFileToTmpDir('thumb_nail');

				$result['cookie'] = [
					'name' => $this->_getSession()->getName(),
					'value' => $this->_getSession()->getSessionId(),
					'lifetime' => $this->_getSession()->getCookieLifetime(),
					'path' => $this->_getSession()->getCookiePath(),
					'domain' => $this->_getSession()->getCookieDomain(),
				];
			} catch (\Exception $e) {
				$result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
			}
		}
		
		if($result['error'] != '')
		{
			try {
				$result = $this->imageUploader->saveFileToTmpDir('thumb_popular');

				$result['cookie'] = [
					'name' => $this->_getSession()->getName(),
					'value' => $this->_getSession()->getSessionId(),
					'lifetime' => $this->_getSession()->getCookieLifetime(),
					'path' => $this->_getSession()->getCookiePath(),
					'domain' => $this->_getSession()->getCookieDomain(),
				];
			} catch (\Exception $e) {
				$result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
			}
		}
		
		
		if($result['error'] != '')
		{
			try {
				$result = $this->imageUploader->saveFileToTmpDir('categorytab_image');

				$result['cookie'] = [
					'name' => $this->_getSession()->getName(),
					'value' => $this->_getSession()->getSessionId(),
					'lifetime' => $this->_getSession()->getCookieLifetime(),
					'path' => $this->_getSession()->getCookiePath(),
					'domain' => $this->_getSession()->getCookieDomain(),
				];
			} catch (\Exception $e) {
				$result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
			}
		}
		
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
