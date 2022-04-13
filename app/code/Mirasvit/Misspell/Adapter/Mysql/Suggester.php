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

namespace Mirasvit\Misspell\Adapter\Mysql;

use Mirasvit\Misspell\Api\Data\SuggestInterface;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\Misspell\Service\TextService;
use Mirasvit\Misspell\Service\DamerauService;

class Suggester implements SuggestInterface
{
    private $diffs;

    private $keys;

    private $resource;

    private $connection;

    private $damerauService;

    private $textService;

    public function __construct(
        ResourceConnection $resource,
        TextService $textService,
        DamerauService $damerauService
    ) {
        $this->resource = $resource;
        $this->textService = $textService;
        $this->damerauService = $damerauService;
        $this->connection = $resource->getConnection();
    }

    public function suggest(string $baseQuery): string
    {
        $this->diffs = [];
        $this->keys = [];
        $final = [];

        $baseQuery = $this->textService->cleanString($baseQuery);
        $queries = $this->textService->splitWords($baseQuery);

        foreach ($queries as $query) {
            $len = $this->textService->strlen($query);

            if ($len < $this->textService->getGram() || is_numeric($query)) {
                $final[] = $query;
                continue;
            }

            $result = $this->getBestMatch($query);
            $keyword = $result['keyword'];

            $this->split($query, '', $query);
            $splitKeyword = '';

            if (count($this->diffs)) {
                arsort($this->diffs);
                $keys = array_keys($this->diffs);
                $key = $keys[0];
                $splitKeyword = $this->keys[$key];
            }

            $basePer = $this->damerauService->similarity($query, $keyword);
            $splitPer = $this->damerauService->similarity($query, $splitKeyword);

            if ($basePer > $splitPer) {
                $final[] = $keyword;
            } else {
                $final[] = $splitKeyword;
            }
        }

        $result = implode(' ', $final);

        if ($this->damerauService->similarity($result, $baseQuery) < 50) {
            $result = '';
        }

        return $result;
    }

    protected function split(string $query, string $prefix = '', string $base = ''): bool
    {
        $keyword = $query;
        $len = $this->textService->strlen($query);

        if ($len > 20) {
            return false;
        }

        for ($i = $this->textService->getGram(); $i <= $len - $this->textService->getGram() + 1; $i++) {
            $a = $this->textService->substr($query, 0, $i);
            $b = $this->textService->substr($query, $i);

            $aa = $this->getBestMatch($a);
            $bb = $this->getBestMatch($b);

            $key = $a . '|' . $b;

            if ($prefix) {
                $key = $prefix . '|' . $key;
            }

            $this->keys[$key] = '';
            if ($prefix) {
                $this->keys[$key] = $prefix . ' ';
            }
            $this->keys[$key] .= $aa['keyword'] . ' ' . $bb['keyword'];

            $this->diffs[$key] = ($this->damerauService->similarity($base, $this->keys[$key]) + $aa['diff'] + $bb['diff']) / 3;

            if ($prefix) {
                $kwd = $prefix . '|' . $aa['keyword'];
            } else {
                $kwd = $aa['keyword'];
            }

            if ($aa['diff'] > 50) {
                $this->split($b, $kwd, $query);
            }
        }

        return true;
    }

    public function getBestMatch(string $query): array
    {
        $query = trim($query);

        if (!$query) {
            return ['keyword' => $query, 'diff' => 100];
        }

        $len = (int)$this->textService->strlen($query);
        $trigram = $this->textService->getTrigram($this->textService->strtolower($query));

        $tableName = $this->resource->getTableName('mst_misspell_index');

        $select = $this->connection->select();
        $relevance = '(-ABS(LENGTH(keyword) - ' . $len . ') + MATCH (trigram) AGAINST("' . $trigram . '"))';
        $relevancy = new \Zend_Db_Expr('AVG(' . $relevance . ' + frequency) AS relevancy');
        $select->from($tableName, ['keyword', $relevancy, 'AVG(frequency) as frequency'])
            ->where('MATCH (trigram) AGAINST("' . $trigram . '")')
            ->group('keyword')
            ->order('relevancy desc')
            ->limit(10);

        try {
            $keywords = $this->connection->fetchAll($select);
        } catch (\Exception $e) {
            return ['keyword' => $query, 'diff' => 100];
        }

        $maxFreq = 0.0001;
        foreach ($keywords as $keyword) {
            $maxFreq = max($keyword['frequency'], $maxFreq);
        }

        $preResults = [];
        foreach ($keywords as $keyword) {
            $preResults[$keyword['keyword']] = $this->damerauService->similarity($query, $keyword['keyword'])
                + $keyword['frequency'] * (10 / $maxFreq);
        }
        arsort($preResults);

        $keys = array_keys($preResults);

        if (count($keys) > 0) {
            $keyword = $keys[0];
            $keyword = $this->toSameRegister($keyword, $query);
            $diff = $preResults[$keys[0]];
            $result = ['keyword' => $keyword, 'diff' => $diff];
        } else {
            $result = ['keyword' => $query, 'diff' => 100];
        }

        return $result;
    }

    protected function toSameRegister(string $str, string $base): string
    {
        $minLen = min($this->textService->strlen($base), $this->textService->strlen($str));

        for ($i = 0; $i < $minLen; $i++) {
            $chr = $this->textService->substr($base, $i, 1);

            if ($chr != $this->textService->strtolower($chr)) {
                $chrN = $this->textService->substr($str, $i, 1);
                $chrN = strtoupper($chrN);
                $str = substr_replace($str, $chrN, $i, 1);
            }
        }

        return $str;
    }
}
