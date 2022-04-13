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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Service;

use Mirasvit\Report\Api\Service\CastingServiceInterface;

class CastingService implements CastingServiceInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function toCamelCase(array $data)
    {
        $newData = [];

        foreach ($data as $key => $value) {
            $key = str_replace('_', '', ucwords($key, '_'));
            $key = lcfirst($key);

            if (is_array($value)) {
                $value = $this->toCamelCase($value);
            }

            $newData[$key] = $value;
        }

        return $newData;
    }

    /**
     * @param array $data
     * @return array
     */
    public function toUnderscore(array $data)
    {
        $newData = [];

        foreach ($data as $key => $value) {
            $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));

            if (is_array($value)) {
                $value = $this->toUnderscore($value);
            }

            $newData[$key] = $value;
        }

        return $newData;
    }
}
