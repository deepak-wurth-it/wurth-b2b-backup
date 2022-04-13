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

class Status extends Command
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $success = false;
        $note = '';

        try {
            if ($this->engine->status($note)) {
                $message = __('Sphinx daemon running.');
                $success = true;
            } else {
                $message = __('Sphinx daemon not running.');
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        /** mp uncomment start 
            $message = __('PHP function "exec" is not available.');
            $note = 'Please follow steps listed below to run Sphinx :'. PHP_EOL;
            $note .= ' - make sure you use the Sphinx Search engine (Click "Save Config")'. PHP_EOL;
            $note .= ' - click the "Generate configuration file" button below'. PHP_EOL;
            $note .= ' - execute this command in your CLI to start Sphinx engine'. PHP_EOL;
            $note .= 'searchd --config '. $this->engine->getAbsConfigFilePath() . PHP_EOL;
            $note .= '- then make sure the Sphinx engine is up and running with this command :'. PHP_EOL;
            $note .= 'searchd --config '. $this->engine->getAbsConfigFilePath() .' --status'. PHP_EOL ;
            $note .= '- now please reindex your search indexes'. PHP_EOL ;
            $success = false;
        mp uncomment end **/

        $jsonData = json_encode([
            'message' => $message,
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
        return $this->context->getAuthorization()->isAllowed('Mirasvit_SearchSphinx::command_status');
    }
}
