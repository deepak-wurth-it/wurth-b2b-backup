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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Search\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Search\Service\DebugService;

class Debug extends Template
{
    protected $_template = 'Mirasvit_Search::debug.phtml';

    private   $debugService;

    public function __construct(
        DebugService $debugService,
        Context $context
    ) {
        $this->debugService = $debugService;
        parent::__construct($context);
    }

    public function getLogs(): array
    {
        return $this->debugService->getLogs();
    }

    public function getPreparedLogs(): array
    {
        $results = [];

        foreach ($this->getLogs() as $row) {
            foreach ($row as $key => $log) {
                $results[$key] = $log;
            }
        }

        return $results;
    }

    public function _toHtml(): ?string
    {
        if (!$this->debugService->isEnabled()) {
            return null;
        }

        return parent::_toHtml();
    }
}
