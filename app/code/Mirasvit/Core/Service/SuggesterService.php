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
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Service;

use Magento\Framework\Module\FullModuleList;

/**
 * @SuppressWarnings(PHPMD)
 */
class SuggesterService
{
    const SUGGESTER_URL = 'http://files.mirasvit.com/feed/suggester.json';
    const KEY_FOR       = 'suggestion_for';
    const KEY_MODULE    = 'module';
    const KEY_PRIORITY  = 'priority';

    private $fullModuleList;

    private $feedService;

    public function __construct(
        FullModuleList $fullModuleList,
        FeedService $feedService
    ) {
        $this->fullModuleList = $fullModuleList;
        $this->feedService    = $feedService;
    }

    /**
     * @param string $moduleName
     *
     * @return array|null
     */
    public function getSuggestion($moduleName)
    {
        $suggestions = $this->feedService->load(self::SUGGESTER_URL);
        if (!$suggestions || !is_array($suggestions)) {
            $suggestions = $this->feedService->loadLocal('suggester.json');
        }

        if (!$suggestions || !is_array($suggestions)) {
            return null;
        }

        $fullModuleList = $this->fullModuleList->getNames();

        $matchedSuggestion = [];
        foreach ($suggestions as $suggestion) {
            if (!is_array($suggestion)
                || !isset($suggestion[self::KEY_FOR])
                || !isset($suggestion[self::KEY_MODULE])) {
                continue;
            }

            if ($suggestion[self::KEY_FOR] !== '*' && $suggestion[self::KEY_FOR] !== $moduleName) {
                continue;
            }

            if (in_array($suggestion[self::KEY_MODULE], $fullModuleList)) {
                continue;
            }

            $matchedSuggestion[] = $suggestion;
        }

        $topPriority = 1000;
        foreach ($matchedSuggestion as $suggestion) {
            if ($suggestion[self::KEY_PRIORITY] < $topPriority) {
                $topPriority = $suggestion[self::KEY_PRIORITY];
            }
        }

        $prioritizedSuggestion = [];
        foreach ($matchedSuggestion as $suggestion) {
            if ($suggestion[self::KEY_PRIORITY] === $topPriority) {
                $prioritizedSuggestion[] = $suggestion;
            }
        }
        shuffle($prioritizedSuggestion);

        return count($prioritizedSuggestion) > 0 ? $prioritizedSuggestion[0] : null;
    }
}
