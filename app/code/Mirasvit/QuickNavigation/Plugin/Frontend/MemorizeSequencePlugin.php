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

namespace Mirasvit\QuickNavigation\Plugin\Frontend;

use Mirasvit\QuickNavigation\Context;
use Mirasvit\QuickNavigation\Model\ConfigProvider;
use Mirasvit\QuickNavigation\Service\SequenceService;

/**
 * @see \Magento\Framework\Controller\ResultInterface::renderResult()
 */
class MemorizeSequencePlugin
{
    private $context;

    private $sequenceService;

    private $configProvider;

    public function __construct(
        Context $context,
        SequenceService $sequenceService,
        ConfigProvider $configProvider
    ) {
        $this->context         = $context;
        $this->sequenceService = $sequenceService;
        $this->configProvider  = $configProvider;
    }

    public function afterRenderResult(object $subject, object $result): object
    {
        if (!$this->configProvider->isEnabled()) {
            return $result;
        }

        $filters = $this->context->getState()->getFilters();
        if (count($filters) === 0) {
            return $result;
        }

        $sequence = $this->sequenceService->createSequence();

        $sequence = $this->sequenceService->ensureSequence($sequence);

        $this->sequenceService->increasePopularity($sequence);

        return $result;
    }
}
