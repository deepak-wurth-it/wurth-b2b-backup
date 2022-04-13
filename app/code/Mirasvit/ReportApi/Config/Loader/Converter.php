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
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Config\Loader;

use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * The key attributes of a node
     */
    const DATA_ATTRIBUTES_KEY = '@attributes';

    /**
     * The key for the data arguments
     */
    const DATA_ARGUMENTS_KEY = '@arguments';

    /**
     * @var array
     */
    protected $mergeNames = ['config', 'tables', 'relations', 'columns'];

    /**
     * Convert configuration
     * @param \DOMDocument|null $source
     * @return array
     */
    public function convert($source)
    {
        if ($source === null) {
            return [];
        }

        $array = $this->toArray($source);

        $array = $this->simplifyArray($array);

        return $array;
    }

    /**
     * Transform Xml to array @SuppressWarnings(PHPMD.CyclomaticComplexity) 
     * @param \DOMNode $node
     * @return array|string
     */
    protected function toArray(\DOMNode $node)
    {
        $result     = [];
        $attributes = [];
        // Collect data from attributes
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attribute) {
                $attributes[$attribute->name] = $attribute->value;
            }
        }

        switch ($node->nodeType) {
            case XML_TEXT_NODE:
            case XML_COMMENT_NODE:
            case XML_CDATA_SECTION_NODE:
                break;
            default:
                $arguments = [];
                for ($i = 0, $iLength = $node->childNodes->length; $i < $iLength; ++$i) {
                    $itemNode = $node->childNodes->item($i);
                    if (empty($itemNode->localName)) {
                        continue;
                    }

                    if ($itemNode->childNodes->length == 1) {
                        if ($itemNode->localName == 'column') {
                            $arguments[] = $itemNode->textContent;
                        } else {
                            $arguments[$itemNode->localName] = $itemNode->textContent;
                        }
                    } else {
                        $result[$itemNode->localName][] = $this->toArray($itemNode);
                    }
                }

                if (!empty($arguments)) {
                    $result[static::DATA_ARGUMENTS_KEY] = $arguments;
                }
                if (!empty($attributes)) {
                    $result[static::DATA_ATTRIBUTES_KEY] = $attributes;
                }
        }

        return $result;
    }

    /**
     * Simplify array
     * @param array $array
     * @return array
     */
    protected function simplifyArray($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $result = [];

        foreach ($array as $name => $values) {
            if (is_string($name) && in_array($name, $this->mergeNames)) {
                $merged = [];

                if (is_array($values)) {
                    foreach ($values as $value) {
                        $merged = array_merge_recursive($merged, $value);
                    }
                    $merged = $this->simplifyArray($merged);
                } else {
                    $merged = $values;
                }

                $result[$name] = $merged;
            } else {
                $result[$name] = $this->simplifyArray($values);
            }
        }

        return $result;
    }
}
