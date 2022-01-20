<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Model;

use Magento\Framework\Escaper;

/**
 * Class Parser for parsing xml/csv formats
 */
class Parser
{
    const RESTRICTED_CHARS = [
        "\r\n",
        "\n",
        "\r"
    ];

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
    }

    /**
     * @param string $xmlContent
     *
     * @return bool|\SimpleXMLElement
     */
    public function parseXml($xmlContent)
    {
        try {
            $xml = new \SimpleXMLElement($xmlContent);
        } catch (\Exception $e) {
            return false;
        }

        return $xml;
    }

    /**
     * @codingStandardsIgnoreStart
     * Using fgetcsv for multiline values. Most optimized variant, Magento don't have alternatives.
     *
     * @param string $csvContent
     *
     * @return array
     */
    public function parseCsv($csvContent)
    {
        try {
            $fp = fopen('php://temp', 'r+');
            fwrite($fp, $csvContent);
            rewind($fp);

            $data = [];
            $header = [];
            $isFirstLine = true;
            while (($row = fgetcsv($fp)) !== false) { // for multiline values
                $row = array_map([$this, "escape"], $row);

                if ($isFirstLine) {
                    $isFirstLine = false;
                    $header = $row;

                    $row = array_combine($header, $row);
                    if (!isset($row['module_code'], $row['tab_name'], $row['upsell_module_code'], $row['text'], $row['priority'])) {
                        return [];
                    }

                    continue;
                }

                $data[] = array_combine($header, $row);
            }

            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }
    /** @codingStandardsIgnoreEnd */

    /**
     * Delete space from selected data
     * @param array $data
     * @param array $columns
     *
     * @return array
     */
    public function trimCsvData($data, $columns = [])
    {
        foreach ($data as $k => $element) {
            foreach ($columns as $column) {
                if (isset($element[$column])) {
                    $data[$k][$column] = preg_replace(
                        '/\s+/',
                        '',
                        $element[$column]
                    );
                }
            }
        }

        return $data;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function escape($value)
    {
        $value = $this->escaper->escapeHtml($value);
        $value = str_replace(static::RESTRICTED_CHARS, ' ', $value);

        return $value;
    }
}
