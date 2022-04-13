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


declare(strict_types=1);

namespace Mirasvit\SearchElastic\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface as TypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerPool;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Service\QueryService;
use Mirasvit\Core\Service\CompatibilityService;

if (version_compare(CompatibilityService::getVersion(), '2.4.4', '<')) {
    class MatchCompatibility extends \Magento\Elasticsearch\SearchAdapter\Query\Builder\Match
    {
        public function __construct(
            FieldMapperInterface $fieldMapper,
            AttributeProvider $attributeProvider,
            TypeResolver $fieldTypeResolver,
            ValueTransformerPool $valueTransformerPool,
            Config $config
        ) {
            parent::__construct($fieldMapper, $attributeProvider, $fieldTypeResolver, $valueTransformerPool, $config);
        }
    }
} else {
class MatchCompatibility extends \Magento\Elasticsearch\SearchAdapter\Query\Builder\MatchQuery
    {
        public function __construct(
            FieldMapperInterface $fieldMapper,
            AttributeProvider $attributeProvider,
            TypeResolver $fieldTypeResolver,
            ValueTransformerPool $valueTransformerPool,
            Config $config
        ) {
            parent::__construct($fieldMapper, $attributeProvider, $fieldTypeResolver, $valueTransformerPool, $config);
        }
    }
}
