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



namespace Mirasvit\SearchMysql\SearchAdapter;

use Mirasvit\Core\Service\CompatibilityService;

if (version_compare(CompatibilityService::getVersion(), '2.4.4', '<')) {
    class MatchCompatibility extends \Magento\Framework\Search\Request\Query\Match
    {

    }
} else {
    class MatchCompatibility extends \Magento\Framework\Search\Request\Query\MatchQuery
    {

    }
}
