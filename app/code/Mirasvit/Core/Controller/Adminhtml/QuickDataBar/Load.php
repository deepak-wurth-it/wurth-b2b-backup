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
 * @version   1.3.3
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Core\Controller\Adminhtml\QuickDataBar;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mirasvit\Core\Service\SerializeService;

class Load extends Action
{
    private $objectManager;

    public function __construct(
        Context                $context
    ) {
        $this->objectManager = $context->getObjectManager();

        parent::__construct($context);
    }

    public function execute()
    {
        $class     = (string)$this->getRequest()->getParam('block');
        $dateRange = (int)$this->getRequest()->getParam('dateRange');

        $dataBlock = $this->objectManager->create($class);

        $to   = new \DateTime();
        $from = (new \DateTime())->sub(new \DateInterval('P' . $dateRange . 'D'));

        $result = $dataBlock
            ->setDateRange($from, $to)
            ->toArray();

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();

        $response->representJson(SerializeService::encode([
            'success' => true,
            'data'    => $result,
        ]));
    }

    protected function _isAllowed(): bool
    {
        return true;
    }
}
