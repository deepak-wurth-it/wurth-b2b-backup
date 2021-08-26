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



namespace Mirasvit\Core\Controller\Adminhtml\Validator;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Core\Block\Adminhtml\Validator;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session.
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mirasvit_Core::validator';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        /** @var \Mirasvit\Core\Block\Adminhtml\Validator $validator */
        $validator = $this->_view->getLayout()->createBlock(Validator::class);

        $resultJson->setData([
            'content'  => $validator->toHtml(),
            'isPassed' => $validator->isPassed(),
        ]);

        return $resultJson;
    }
}
