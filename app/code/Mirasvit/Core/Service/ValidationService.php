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



namespace Mirasvit\Core\Service;

use Mirasvit\Core\Api\Service\ValidationServiceInterface;
use Mirasvit\Core\Api\Service\ValidatorInterface;

class ValidationService implements ValidationServiceInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
    }

    /**
     * Validation run scenario:
     * 1. Run all validations if no modules passed.
     * 2. Run validation for every module dependency @see \Mirasvit\Core\Api\Service\ValidatorInterface::getModules()
     * 3. Run validation if a validator's module name matches a passed module name.
     * {@inheritdoc}
     */
    public function runValidation(array $modules = [])
    {
        $merged = [];
        foreach ($this->validators as $validator) {
            if ($this->canValidate($validator->getModuleName(), $modules) || count($modules) == 0) {
                $result = $validator->validate();

                $merged = array_merge($merged, $result);
            }
        }

        return $merged;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * @param string $validatorModuleName
     * @param array  $requestedModules
     *
     * @return bool
     */
    private function canValidate($validatorModuleName, array $requestedModules)
    {
        if (empty($requestedModules) || in_array($validatorModuleName, $requestedModules)) {
            return true;
        }

        return false;
    }
}
