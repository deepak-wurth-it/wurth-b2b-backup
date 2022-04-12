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

namespace Mirasvit\Misspell\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class OnAjaxSuggestObserver extends OnCatalogSearchObserver implements ObserverInterface
{
    public function execute(EventObserver $observer): void
    {
        if ((bool) $this->queryService->getNumResults() == false) {
            if ($this->configProvider->isMisspellEnabled()) {
                $result = $this->doSpellCorrection();
            }
        }
    }
}
