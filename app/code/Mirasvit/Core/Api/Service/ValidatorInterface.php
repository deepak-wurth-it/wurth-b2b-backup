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

interface ValidatorInterface
{
    /**
     * Possible Validation Results
     */
    const SUCCESS = 1;
    const INFO = 2;
    const WARNING = 3;
    const FAILED = 4;

    const STATUS_CODE = 'status_code';
    const TEST_NAME = 'test_name';
    const MODULE_NAME = 'module_name';
    const MESSAGE = 'message';

    /**
     * Execute validator tests.
     *
     * @return string[]
     */
    public function validate();

    /**
     * Get validator module name.
     *
     * @return string
     */
    public function getModuleName();
}
