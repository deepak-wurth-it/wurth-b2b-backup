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

namespace Mirasvit\Misspell\Service;

class DamerauService
{
    private $textService;

    public function __construct(
        TextService $textService
    ) {
        $this->textService = $textService;
    }

    /**
     * Measures the Damerau-Levenshtein distance of two words
     */
    public function distance(string $strA, string $strB): int
    {
        $d = [];
        $strA = $this->textService->strtolower($strA);
        $strB = $this->textService->strtolower($strB);
        $lenA = $this->textService->strlen($strA);
        $lenB = $this->textService->strlen($strB);

        if ($lenA == 0) {
            return $lenB;
        }

        if ($lenB == 0) {
            return $lenA;
        }

        for ($i = 0; $i <= $lenA; $i++) {
            $d[$i]    = [];
            $d[$i][0] = $i;
        }

        for ($j = 0; $j <= $lenB; $j++) {
            $d[0][$j] = $j;
        }

        for ($i = 1; $i <= $lenA; $i++) {
            for ($j = 1; $j <= $lenB; $j++) {
                $cost = substr($strA, $i - 1, 1) == substr($strB, $j - 1, 1) ? 0 : 1;

                $d[$i][$j] = min(
                    $d[$i - 1][$j] + 1, // deletion
                    $d[$i][$j - 1] + 1, // insertion
                    $d[$i - 1][$j - 1] + $cost // substitution
                );

                if ($i > 1 &&
                    $j > 1 &&
                    substr($strA, $i - 1, 1) == substr($strB, $j - 2, 1) &&
                    substr($strA, $i - 2, 1) == substr($strB, $j - 1, 1)
                ) {
                    $d[$i][$j] = min(
                        $d[$i][$j],
                        $d[$i - 2][$j - 2] + $cost // transposition
                    );
                }
            }
        }

        return $d[$lenA][$lenB];
    }

    /**
     * An attempt to measure word similarity in percent
     */
    public function similarity(string $strA, string $strB): int
    {
        $strA = $this->textService->strtolower($strA);
        $strB = $this->textService->strtolower($strB);
        $lenA = $this->textService->strlen($strA);
        $lenB = $this->textService->strlen($strB);

        if ($lenA == 0 && $lenB == 0) {
            return 100;
        }

        $distance   = $this->distance($strA, $strB);
        $similarity = 100 - (int)round(200 * $distance / ($lenA + $lenB));

        return $similarity >= 100 ? 100 : $similarity;
    }
}
