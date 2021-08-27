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

namespace Mirasvit\LayeredNavigation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\LayeredNavigation\Service\CssCreatorService;

class Css extends Template
{
    protected $_template = 'Mirasvit_LayeredNavigation::css.phtml';

    private   $cssCreatorService;

    public function __construct(
        Context $context,
        CssCreatorService $cssCreatorService,
        array $data = []
    ) {
        $this->cssCreatorService = $cssCreatorService;

        parent::__construct($context, $data);
    }

    public function getCss(): string
    {
        return $this->cssCreatorService->getCssContent(
            (int)$this->_storeManager->getStore()->getId()
        );
    }
}
