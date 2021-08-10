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



namespace Mirasvit\Core\Plugin;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

class UrlRewritePlugin
{
    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * UrlRewritePlugin constructor.
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        EventManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * Dispatch our event before dispatch Frontend Controller
     * @param mixed $subject
     * @param mixed $request
     */
    public function beforeDispatch($subject, $request)
    {
        $this->eventManager->dispatch('core_register_urlrewrite');
    }
}
