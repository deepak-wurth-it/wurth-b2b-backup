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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\SeoNavigation\Service;

trait SorterTrait
{
    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private function sortStrategies($a, $b)
    {
        // in case if sort_order not set for strategies
        $a = array_merge(['sort_order' => 999], $a);
        $b = array_merge(['sort_order' => 999], $b);

        if ($a['sort_order'] === $b['sort_order']) {
            return 0;
        }

        return $a['sort_order'] > $b['sort_order'] ? 1 : -1;
    }
}
