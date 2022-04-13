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


namespace Mirasvit\SearchSphinx\Controller\Adminhtml\Command;

use Mirasvit\SearchSphinx\Controller\Adminhtml\Command;

class Restart extends Command
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $success = true;
        $note = '';
        $message = '';

        try {
            if ($this->engine->restart($note)) {
                $message .= __('Sphinx daemon successfully restarted.');
            } else {
                $message .= __('Sphinx daemon not running.');
                $success = false;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $success = false;
        }

        $jsonData = json_encode([
            'message' => nl2br($message),
            'note'    => ($note),
            'success' => $success,
        ]);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_SearchSphinx::command_restart');
    }
}
