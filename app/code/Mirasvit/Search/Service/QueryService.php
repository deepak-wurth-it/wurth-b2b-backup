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



namespace Mirasvit\Search\Service;

use Mirasvit\Search\Api\Data\QueryConfigProviderInterface;

class QueryService
{
    private static $cache = [];

    private        $configProvider;

    public function __construct(
        QueryConfigProviderInterface $configProvider
    ) {
        $this->configProvider  = $configProvider;
    }

    public function build(string $query): array
    {
        $query   = urldecode($query);
        $storeId = $this->configProvider->getStoreId();

        if (function_exists('mb_strtolower')) {
            $query = mb_strtolower($query);
        } else {
            $query = strtolower($query);
        }

        $identifier = $storeId . $query;

        if (!array_key_exists($identifier, self::$cache)) {
            if (preg_match('~[\p{Han}]~u', $query)) {
                $query = preg_replace('~([\p{Han}])~u', ' $1', $query);
            }
            // required if synonym contains more 1 word
            $query = ' ' . $query . ' ';

            $result = [];

            $replaceWords = $this->configProvider->getReplaceWords();

            foreach ($replaceWords as $replacement) {
                $query = str_replace(' ' . $replacement['from'] . ' ', ' ' . $replacement['to'] . ' ', $query);
            }

            $terms = preg_split('#\s#siu', $query, -1, PREG_SPLIT_NO_EMPTY);
            $arSynonyms = $this->configProvider->getSynonyms($terms, $storeId);
            foreach ($arSynonyms as $term => $synonyms) {
                $arSynonyms[$term] = array_splice($arSynonyms[$term], 0, 20);
            }

            $condition = '$like';
            foreach ($terms as $term) {
                if (in_array($term, $this->configProvider->getNotWords())) {
                    $condition = '$!like';
                    continue;
                }

                if (iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $term)) {
                    $correctedTerm = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $term);
                    if (strripos($term, '?') === false && strripos($correctedTerm, '?') !== false) {
                        $correctedTerm = $term;
                    }
                }

                if ($this->configProvider->isStopword($term, $storeId) && count($terms) > 1) {
                    continue;
                }

                $wordArr = [];

                if ($condition == '$like') {
                    $this->addTerms($wordArr, [$term]);
                    $this->addTerms($wordArr, [$this->applyLongTail($term)]);
                    $this->addTerms($wordArr, [$this->configProvider->applyStemming($term)]);

                    if (isset($arSynonyms[$term])) {
                        # for synonyms we always disable wildcards
                        $this->addTerms($wordArr, $arSynonyms[$term], QueryConfigProviderInterface::WILDCARD_DISABLED);
                    }

                    if ($this->configProvider->getMatchMode() == QueryConfigProviderInterface::MATCH_MODE_OR) {
                        $mode = '$or';
                    } else {
                        $mode = '$and';
                    }

                    $result[$condition][$mode][] = ['$or' => $wordArr];
                } else {
                    $this->addTerms($wordArr, [$term], QueryConfigProviderInterface::WILDCARD_DISABLED);
                    $result[$condition]['$and'][] = ['$and' => $wordArr];
                }
            }

            self::$cache[$identifier] = $result;
        }

        DebugService::log(\Zend_Json::encode(self::$cache[$identifier]), 'query_service_build');

        return self::$cache[$identifier];
    }

    private function addTerms(array &$to, array $terms, string $wildcard = null): void
    {
        $exceptions = $this->configProvider->getWildcardExceptions();
        if ($wildcard == null) {
            $wildcard = $this->configProvider->getWildcardMode();
        }

        foreach ($terms as $term) {
            $term = trim($term);

            if ($term == '') {
                continue;
            }

            if ($wildcard == QueryConfigProviderInterface::WILDCARD_PREFIX) {
                $item = [
                    '$phrase'   => $term,
                    '$wildcard' => QueryConfigProviderInterface::WILDCARD_PREFIX,
                ];
            } elseif ($wildcard == QueryConfigProviderInterface::WILDCARD_SUFFIX) {
                $item = [
                    '$phrase'   => $term,
                    '$wildcard' => QueryConfigProviderInterface::WILDCARD_SUFFIX,
                ];
            } elseif ($wildcard == QueryConfigProviderInterface::WILDCARD_DISABLED || in_array($term, $exceptions)) {
                $item = [
                    '$phrase'   => $term,
                    '$wildcard' => QueryConfigProviderInterface::WILDCARD_DISABLED,
                ];
            } else {
                $item = [
                    '$phrase'   => $term,
                    '$wildcard' => QueryConfigProviderInterface::WILDCARD_INFIX,
                ];
            }

            $to[implode(array_values($item))]['$term'] = $item;
        }
    }

    private function applyLongTail(string $term): string
    {
        return $this->configProvider->applyLongTail($term);
    }
}
