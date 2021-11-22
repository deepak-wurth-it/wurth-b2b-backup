<?php
namespace Wcb\Catalogslider\Controller\Adminhtml\catalogslider;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Save extends \Magento\Backend\App\Action
{

    /**
     * @var UploaderFactory
     */
	protected $uploaderFactory;
		/**
     * @var AdapterFactory
     */
    protected $adapterFactory;
	
    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();


        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Wcb\Catalogslider\Model\Catalogslider');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
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
                  $destinationPath = $mediaDirectory->getAbsolutePath('catalogslider');
                  $result = $uploaderFactory->save($destinationPath);
   
                  if (!$result)
                  {
                       throw new LocalizedException
                          (
                          __('File cannot be saved to path: $1', $destinationPath)
                          );
                  }
   
                $imagePath = 'catalogslider' . $result['file'];
   
                $data['image'] = $imagePath;
   
            }
            catch (\Exception $e)
            {
                  $this->messageManager->addError(__("Image not Upload Pleae Try Again"));
            }
       }

       /** File Upload Ends*/

               
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Catalogslider has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Catalogslider.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}