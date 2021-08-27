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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

class Upload extends Action
{
    /**
     * @var string
     */
    protected $field;

    private $imageUploader;

    public function __construct(
        Context $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);

        $this->imageUploader = $imageUploader;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $result = $this->getImageUploader()->saveFileToTmpDir($this->field);
        } catch (\Exception $exception) {
            $result = [
                'error'     => $exception->getMessage(),
                'errorcode' => $exception->getCode(),
            ];
        }

        return $resultJson->setData($result);
    }

    private function getImageUploader()
    {
        $allowed        = $this->imageUploader->getAllowedExtensions();
        $allowed['svg'] = 'svg';

        $this->imageUploader->setAllowedExtensions($allowed);

        return $this->imageUploader;
    }
}
