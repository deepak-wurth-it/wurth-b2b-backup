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



namespace Mirasvit\SearchSphinx\SearchAdapter\Query;

use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Mirasvit\SearchSphinx\SphinxQL\SphinxQL;

class QueryContainer
{
    const DERIVED_QUERY_PREFIX = 'derived_';

    private $queries = [];

    private $matchContainerFactory;

    public function __construct(
        MatchContainerFactory $matchContainerFactory
    ) {
        $this->matchContainerFactory = $matchContainerFactory;
    }

    public function addMatchQuery(SphinxQL $select, RequestQueryInterface $query, string $conditionType): SphinxQL
    {
        $container            = $this->matchContainerFactory->create([
            'request'       => $query,
            'conditionType' => $conditionType,
        ]);
        $name                 = self::DERIVED_QUERY_PREFIX . count($this->queries);
        $this->queries[$name] = $container;

        return $select;
    }

    public function getMatchQueries(): array
    {
        return $this->queries;
    }
}
