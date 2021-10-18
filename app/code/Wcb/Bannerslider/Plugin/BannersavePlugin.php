<?php

namespace Wcb\Bannerslider\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;

class BannersavePlugin extends \Plazathemes\Bannerslider\Controller\Adminhtml\Banner\Save {
	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	public function aroundExecute(\Plazathemes\Bannerslider\Controller\Adminhtml\Banner\Save $subject, callable $proceed) {
		if ($data = $this->getRequest()->getPostValue()) {
			if (array_key_exists("visible_to",$data)) {
				$data['visible_to'] = implode(',',$data['visible_to']);
			}else{
				$data['visible_to'] = '';
			}
			if (array_key_exists("display_pages",$data)) {
				$data['display_pages'] = implode(',',$data['display_pages']);
			}else{
				$data['display_pages'] = '';
			}
			$result = $proceed();
			// if ($this->canCallProceedCallable($data)) {
			// 	$returnValue = $proceed();
			// }
			
			return $result;
			// $model->setData($data)
			//       ->setStoreViewId($storeViewId);

						//return $data;
		}
	}
}
