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

namespace Mirasvit\SearchAutocomplete\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Mirasvit\SearchAutocomplete\InstantProvider\ConfigMaker;

class OnConfigChanged implements ObserverInterface
{
    private $configMaker;

    private $messageManager;

    public function __construct(
        ConfigMaker $configMaker,
        ManagerInterface $messageManager
    ) {
        $this->configMaker    = $configMaker;
        $this->messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {
        try {
            $this->configMaker->ensure();
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }
    }
}
