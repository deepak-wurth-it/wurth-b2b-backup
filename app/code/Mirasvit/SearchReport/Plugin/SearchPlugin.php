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



namespace Mirasvit\SearchReport\Plugin;

use Magento\Framework\Api\Search\SearchResult;
use Magento\Framework\Registry;
use Magento\Search\Api\SearchInterface;

class SearchPlugin
{
    const REGISTRY_KEY = 'QueryTotalCount';

    private $registry;

    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function afterSearch(SearchInterface $subject, SearchResult $result): SearchResult
    {
        $this->registry->register(self::REGISTRY_KEY, $result->getTotalCount(), true);

        return $result;
    }
}
