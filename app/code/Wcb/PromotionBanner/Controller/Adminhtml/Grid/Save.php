<?php

namespace Wcb\PromotionBanner\Controller\Adminhtml\Grid;
use Magento\Framework\Controller\ResultFactory;
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Wcb\PromotionBanner\Model\PromotionBannerFactory
     */
    var $gridFactory;

    /**
     * @var UploaderFactory
     */
	protected $uploaderFactory;
	
	/**
     * @var AdapterFactory
     */
    protected $adapterFactory;
	
	/**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Wcb\PromotionBanner\Model\PromotionBannerFactory $gridFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->directoryList = $directoryList;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->gridFactory = $gridFactory;
    }


    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);                
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        /*if ((empty($data)) || (empty($data['image'])) || (empty($data['title']))) {
            $this->messageManager->addError(__("Please fill all required values."));
            return $resultRedirect;
        }*/
        try {
            $model = $this->gridFactory->create();
            if (isset($data['entity_id'])) {
                if($data['position']){
                    $banners_count = $model->getCollection()->addFieldToFilter('position', $data['position'])->addFieldToFilter('status', 1)->count();
                    /*if($banners_count){
                        $this->messageManager->addError(__("Banner exists in the selected position. Please choose different one"));
                        return $resultRedirect;
                    }*/
                }
                $model->setEntityId($data['entity_id']);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }

            if (array_key_exists("customer_group",$data)) {
                $data['customer_group'] = implode(',',$data['customer_group']);
            }else{
                $data['customer_group'] = '';
            }
            
			if(isset($data['image']['delete']) && $data['image']['delete'] == '1')
                {
                    $data['image'] = '';
                }

            if(isset($data['image']['value'])){
                $data['image'] = $data['image']['value'];
            }   
        /** File Upload Starts */
      
        if ((isset($_FILES['image']['name'])) && ($_FILES['image']['name'] != '') && (!isset($data['image']['delete'])))
        {
           try
             { 
                 $_FILES['image']['name'];
             
                  $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'image']);
                  $uploaderFactory->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']); 
                  $imageAdapter = $this->adapterFactory->create();
                  $uploaderFactory->setAllowRenameFiles(true);
                  $uploaderFactory->setFilesDispersion(true);
                  $mediaDirectory = $this->_objectManager->get('\Magento\Framework\Filesystem')
                        ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                  $destinationPath = $mediaDirectory->getAbsolutePath('promotionbanner');
                  $result = $uploaderFactory->save($destinationPath);
   
                  if (!$result)
                  {
                       throw new LocalizedException
                          (
                          __('File cannot be saved to path: $1', $destinationPath)
                          );
                  }
   
                $imagePath = 'promotionbanner' . $result['file'];
   
                $data['image'] = $imagePath;
   
            }
            catch (\Exception $e)
            {
                  $this->messageManager->addError(__("Image not Upload Pleae Try Again"));
            }
       }   

       /** File Upload Ends*/

               
        $model->setData($data);

        $model->save();
        
        $this->messageManager->addSuccess(__('Row data has been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('grid/grid/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wcb_PromotionBanner::save');
    }
}
