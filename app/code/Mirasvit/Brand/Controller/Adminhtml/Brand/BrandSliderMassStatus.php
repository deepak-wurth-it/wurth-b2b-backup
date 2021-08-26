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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Controller\Adminhtml\Brand;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Controller\Adminhtml\Brand;
use Mirasvit\Brand\Model\Brand\PostData\Processor as PostDataProcessor;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Model\ResourceModel\BrandPage\CollectionFactory;
use Mirasvit\Brand\Repository\BrandPageRepository;

class BrandSliderMassStatus extends Brand
{
    protected $brandPageRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    public function __construct(
        BrandPageRepository $brandPageRepository,
        Context $context,
        PostDataProcessor $postDataProcessor,
        Config $config,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct(
            $brandPageRepository,
            $context,
            $postDataProcessor,
            $config
        );
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $status = $this->getRequest()->getParam(BrandPageInterface::IS_SHOW_IN_BRAND_SLIDER);
        $status = ($status == 1) ? true : false;
        $ids    = [];

        if ($this->getRequest()->getParam(BrandPageInterface::ID)) {
            $ids = $this->getRequest()->getParam(BrandPageInterface::ID);
        }

        if ($this->getRequest()->getParam(Filter::SELECTED_PARAM)) {
            $ids = $this->getRequest()->getParam(Filter::SELECTED_PARAM);
        }

        if (!$ids) {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $ids        = $collection->getAllIds();
        }

        if ($ids) {
            try {
                foreach ($ids as $id) {
                    $model = $this->brandPageRepository->get($id)->setIsShowInBrandSlider($status);
                    $this->brandPageRepository->save($model);
                }
                $this->messageManager->addSuccessMessage(
                    (string)__('%1 item(s) have changed brand sliderstatus', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage((string)__('Please select item(s)'));
            }
        } else {
            $this->messageManager->addErrorMessage((string)__('Please select item(s)'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
