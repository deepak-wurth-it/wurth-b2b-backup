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



namespace Mirasvit\Misspell\Service;

use Magento\Framework\App\Helper\AbstractHelper;

class TextService extends AbstractHelper
{
    const ICONV_CHARSET = 'UTF-8';

    /**
     * Split string to words
     *
     * @param string $string
     *
     * @return array
     */
    public function splitWords($string)
    {
        $split = preg_split('#\s#siu', $string, -1, PREG_SPLIT_NO_EMPTY);

        $result = [];

        foreach ($split as $word) {
            $result[$word] = $word;
        }

        return array_values($result);
    }

    /**
     * Split string to trigrams
     *
     * @param string $keyword
     *
     * @return string
     */
    public function getTrigram($keyword)
    {
        $keyword = $this->toUTF8($keyword);

        $trigram = [];

        $len = mb_strlen($keyword);

        for ($i = 1; $i < $len + $this->getGram(); $i++) {
            $trig = '';
            for ($j = $i - $this->getGram(); $j < $i; $j++) {
                if ($j >= 0 && $j < $len) {
                    $trig .= mb_substr($keyword, $j, 1);
                } else {
                    $trig .= '_';
                }
            }

            $trigram[] = $trig;
        }

        return implode(' ', $trigram);
    }

    /**
     * Convert string to UTF8
     *
     * @param string $string
     *
     * @return string
     */
    public function toUTF8($string)
    {
        $newString = false;
        $newString = mb_convert_encoding((string) $string, "UTF-8", "UTF-8");

        if ($newString === false) {
            return $string;
        }

        return $newString;
    }

    /**
     * Minimum allowed fulltext gram
     * @return int
     */
    public function getGram()
    {
        return 4;
    }

    /**
     * Sub string
     *
     * @param string $string
     * @param int    $offset
     * @param null   $length
     *
     * @return string
     */
    public function substr($string, $offset, $length = null)
    {
        $string = $this->cleanString($string);
        if (is_null($length)) {
            $length = $this->strlen($string) - $offset;
        }

        return iconv_substr($string, $offset, $length, self::ICONV_CHARSET);
    }

    /**
     * Clean string (remove not utf chars)
     *
     * @param string $string
     *
     * @return string
     */
    public function cleanString($string)
    {
        $string = $this->toUTF8($string);

        $string = preg_replace('/[^\p{L}0-9\-]/u', ' ', $string);
        $string = trim(preg_replace('/\s+/', ' ', $string));

        return $string;
    }

    /**
     * String length
     *
     * @param string $string
     *
     * @return int
     */
    public function strlen($string)
    {
        return mb_strlen($this->toUTF8($string));
    }

    /**
     * Convert string to lower registry
     *
     * @param string $string
     *
     * @return string
     */
    public function strtolower($string)
    {
        return mb_strtolower($string);
    }

    /**
     * Highlight difference between 2 string using html tag
     *
     * @param string $new
     * @param string $old
     * @param string $tag
     *
     * @return string
     */
    public function htmlDiff($new, $old, $tag)
    {
        $ret  = '';
        $diff = $this->diff(explode(' ', $old), explode(' ', $new));
        foreach ($diff as $k) {
            if (is_array($k)) {
                $ret .= !empty($k['i']) ? "<" . $tag . ">" . implode(' ', $k['i']) . "</" . $tag . "> " : '';
            } else {
                $ret .= $k . ' ';
            }
        }

        return $ret;
    }

    /**
     * Find difference
     *
     * @param array<string> $old
     * @param array<string> $new
     *
     * @return array
     */
    protected function diff($old, $new)
    {
        $maxLen = $oldMax = $newMax = 0;
        foreach ($old as $oldIndex => $oldChar) {
            $keys = array_keys($new, $oldChar);
            foreach ($keys as $newIndex) {
                $matrix[$oldIndex][$newIndex] = isset($matrix[$oldIndex - 1][$newIndex - 1]) ?
                    $matrix[$oldIndex - 1][$newIndex - 1] + 1 : 1;
                if ($matrix[$oldIndex][$newIndex] > $maxLen) {
                    $maxLen = $matrix[$oldIndex][$newIndex];
                    $oldMax = $oldIndex + 1 - $maxLen;
                    $newMax = $newIndex + 1 - $maxLen;
                }
            }
        }

        if ($maxLen == 0) {
            return [['d' => $old, 'i' => $new]];
        }

        return array_merge(
            $this->diff(array_slice($old, 0, $oldMax), array_slice($new, 0, $newMax)),
            array_slice($new, $newMax, $maxLen),
            $this->diff(array_slice($old, $oldMax + $maxLen), array_slice($new, $newMax + $maxLen))
        );
    }
}
