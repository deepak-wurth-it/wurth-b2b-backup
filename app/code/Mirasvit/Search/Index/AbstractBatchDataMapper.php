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



namespace Mirasvit\Search\Index;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;

abstract class AbstractBatchDataMapper implements BatchDataMapperInterface
{
    protected $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function map(array $documentData, $storeId, array $context = [])
    {
        if (isset($context['entityType'])) {
            $identifier = (string)$context['entityType'];

            $instance = $this->context->getIndexRepository()->getInstanceByIdentifier($identifier);

            foreach ($documentData as $entityId => $indexData) {
                $data = [];

                foreach ($indexData as $attrId => $value) {
                    $attributeCode = $instance->getAttributeCode($attrId);

                    $data[$attributeCode] = $value;
                }

                $documentData[$entityId] = $data;
            }
        }

        $documentData = $this->recursiveMap($documentData);

        return $documentData;
    }

    /**
     * @param array|string $data
     * @param string       $attrPattern
     *
     * @return array|string
     */
    public function recursiveMap($data, string $attrPattern = '/./')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    if (preg_match($attrPattern, (string)$key)) {
                        $data[$key] = $this->recursiveMap($value, $attrPattern);
                    }
                } else {
                    $data[$key] = $this->recursiveMap($value, $attrPattern);
                }
            }
        } elseif (is_string($data)) {
            $string = strip_tags($data);

            $expressions = $this->context->getConfigProvider()->getLongTailExpressions();

            foreach ($expressions as $expr) {
                $matches = null;
                preg_match_all($expr['match_expr'], $string, $matches);

                foreach ($matches[0] as $math) {
                    $math   = preg_replace($expr['replace_expr'], $expr['replace_char'], $math);
                    $string .= ' ' . $math;
                }
            }

            $string = preg_replace('/\s\s+/', ' ', $string);

            return $string;
        }

        return $data;
    }
}
