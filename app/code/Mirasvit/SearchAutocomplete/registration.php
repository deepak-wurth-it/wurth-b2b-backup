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


// @codingStandardsIgnoreStart
declare(strict_types=1);

if (isset($_SERVER) && is_array($_SERVER) && isset($_SERVER['REQUEST_URI'])) {
    /** mp comment start */
    if (! Mirasvit\Core\Service\CompatibilityService::isMarketplace()) {
        if (strpos($_SERVER['REQUEST_URI'], 'searchautocomplete/ajax/typeahead') !== false) {
            require_once 'InstantProvider/TypeaheadProvider.php';
        }
        if (strpos($_SERVER['REQUEST_URI'], 'searchautocomplete/ajax/suggest') !== false) {
            require_once 'InstantProvider/InstantProvider.php';
        }
    }
    /** mp comment end */
    if (strpos($_SERVER['REQUEST_URI'], 'search/ajax/suggest') !== false) {
        return \Zend_Json::encode([]);
    }
}

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Mirasvit_SearchAutocomplete',
    __DIR__
);
// @codingStandardsIgnoreEnd
