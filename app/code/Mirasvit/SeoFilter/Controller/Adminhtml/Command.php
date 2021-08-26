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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SeoFilter\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mirasvit\SeoFilter\Repository\RewriteRepository;

abstract class Command extends Action
{
    protected $rewriteRepository;

    protected $context;

    public function __construct(
        RewriteRepository $rewriteRepository,
        Context $context
    ) {
        $this->rewriteRepository = $rewriteRepository;
        $this->context           = $context;

        parent::__construct($context);
    }
}
