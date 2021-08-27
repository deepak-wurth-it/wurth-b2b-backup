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


namespace Mirasvit\Core\Api\Service;

interface ManualServiceInterface
{
    const MANUAL_FILE_PATH = 'etc';
    const MANUAL_FILE_NAME = 'manual.xml';

    const DOCS_URL = 'http://mirasvit.com/docs/';
    const TOP_TEMPLATE = '/view/adminhtml/templates/pageactions.phtml';
    const BOTTOM_TEMPLATE = '/view/adminhtml/templates/page/copyright.phtml';
    const GRID_AFTER_TEMPLATE = 'templates/listing/default.xhtml';
    const TOP_POSITION = 'top'; //use as default
    const BOTTOM_POSITION = 'bottom';
    const GRID_AFTER_POSITION = 'grid-after';
    const DEFAULT_TITLE = 'Learn more';

    public function getManualLink();
}
