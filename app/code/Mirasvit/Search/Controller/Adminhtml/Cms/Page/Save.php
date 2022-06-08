<?php
namespace Mirasvit\Search\Controller\Adminhtml\Cms\Page;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory as cmsPageFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Cms\Controller\Adminhtml\Page\Save
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::save';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var cmsPageFactory
     */
    protected $cmsPageFactory;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param cmsPageFactory $cmsPageFactory
     * @param cmsPageFactory|null $pageFactory
     * @param PageRepositoryInterface|null $pageRepository
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        cmsPageFactory $cmsPageFactory,
        cmsPageFactory $pageFactory = null,
        PageRepositoryInterface $pageRepository = null
    ) {
        $this->cmsPageFactory = $cmsPageFactory;
        parent::__construct($context, $dataProcessor, $dataPersistor, $pageFactory, $pageRepository);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Page::STATUS_ENABLED;
            }
            if (empty($data['page_id'])) {
                $data['page_id'] = null;
            }

            /** @var Page $model */
            $model = $this->cmsPageFactory->create();

            $id = $this->getRequest()->getParam('page_id');
            if ($id) {
                $model->load($id);
            }

            // Add custom image field to data
            if (isset($data['cms_image']) && is_array($data['cms_image'])) {
                $data['cms_image'] = $data['cms_image'][0]['name'];
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'cms_page_prepare_save',
                ['page' => $model, 'request' => $this->getRequest()]
            );

            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['page_id' => $model->getId(), '_current' => true]);
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the page.'));
                $this->dataPersistor->clear('cms_page');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['page_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the page.'));
            }

            $this->dataPersistor->set('cms_page', $data);
            return $resultRedirect->setPath('*/*/edit', ['page_id' => $this->getRequest()->getParam('page_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
