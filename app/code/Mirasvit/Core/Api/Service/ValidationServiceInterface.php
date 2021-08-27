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

interface ValidationServiceInterface
{
    /**
     * Run validation process.
     *
     * @param string[] $modules - name of modules to run validation for. E.g. Mirasvit_Email
     *
     * @return array - result of validation
     */
    public function runValidation(array $modules = []);

    /**
     * Get list of available validators.
     *
     * @return ValidatorInterface[]
     */
    public function getValidators();
}
