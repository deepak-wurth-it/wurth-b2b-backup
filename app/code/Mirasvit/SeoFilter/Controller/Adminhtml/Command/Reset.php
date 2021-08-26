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


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Controller\Adminhtml\Command;

use Mirasvit\SeoFilter\Controller\Adminhtml\Command;

class Reset extends Command
{
    public function execute(): void
    {
        $success = false;
        $note    = '';
        $message = '';

        try {
            $count = 0;
            foreach ($this->rewriteRepository->getCollection() as $item) {
                $this->rewriteRepository->delete($item);
                $count++;
            }
            $message .= __('All (%1) attribute and option aliases were removed.', $count);
            $success = true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $jsonData = \Zend_Json::encode([
            'message' => nl2br($message),
            'note'    => $note,
            'success' => $success,
        ]);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    protected function _isAllowed(): bool
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_SeoFilter::config_seo_filter_reset');
    }
}
