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



namespace Mirasvit\SearchElastic\Controller\Adminhtml\Command;

use Mirasvit\SearchElastic\Controller\Adminhtml\Command;

class Reset extends Command
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $success = false;
        $note    = '';
        $message = '';

        try {
            $this->manager->reset($note);
            $message .= __('All indexes available for current Elasticsearch connection are deleted successfully. Products are unavailable on your frontend now.
                Please run "bin/magento indexer:reindex catalogsearch_fulltext" command to restore products on your frontend');
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


    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_SearchElastic::command_reset');
    }
}
