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


//@codingStandardsIgnoreFile
namespace Mirasvit\Search\Service\Stemming;


/**
 * @SuppressWarnings(PHPMD)
 */
class NlStemmer implements StemmerInterface
{
    private $R1;

    private $R2;

    private $removed_E;

    public function singularize(string $term): string
    {
        $term = rtrim($term);
        $term = strtolower($term);
        if ($this->isStemmable($term)) {
            $term     = $this->replaceSpecialCharacters($term);
            $term     = $this->substituteIAndY($term);
            $this->R1 = $this->getRIndex($term, 0);
            $this->R2 = $this->getRIndex($term, $this->R1);
            $term     = $this->step1($term);
            $term     = $this->step2($term);
            $term     = $this->step3a($term);
            $term     = $this->step3b($term);
            $term     = $this->step4($term);
            $term     = strtolower($term);
        }

        return $term;
    }

    /**
     * Search for the longest among the following suffixes, and perform the action indicated.
     */
    public function step1(string $term): string
    {
        $num_letters = strlen($term);

        if ($this->R1 >= $num_letters) {
            return $term;
        }

        if ($this->endsWith($term, 'heden')) {
            $term = $this->replace($term, '/heden$/', 'heid', $this->R1);

            return $term;
        }

        if (preg_match('/(?<![aeiouyè]|gem)(ene?)$/', $term, $matches, 0, $this->R1)) {
            $term = $this->undouble($this->replace($term, '/(?<![aeiouyè]|gem)(ene?)$/', '', $this->R1));

            return $term;
        }

        if (preg_match('/(?<![aeiouyèj])(se?)$/', $term, $matches, 0, $this->R1)) {
            $term = $this->replace($term, '/(?<![aeiouyèj])(se?)$/', '', $this->R1);

            return $term;
        }

        return $term;
    }

    /**
     * Delete suffix e if in R1 and preceded by a non-vowel, and then undouble the ending.
     */
    public function step2(string $term): string
    {
        if ($this->endsWith($term, 'e')) {
            $letters     = str_split($term);
            $num_letters = count($letters);

            if (!$this->isVowel($letters[$num_letters - 2])) {
                $letters         = array_slice($letters, 0, ($num_letters - 1));
                $term            = implode('', $letters);
                $term            = $this->undouble($term);
                $this->removed_E = true;
            }
        }

        return $term;
    }

    /**
     * Delete heid if in R2 and not preceded by c, and treat a preceding en as in step 1(b).
     */
    public function step3a(string $term): string
    {
        if (preg_match('/(?<![c])(heid)/', $term, $matches, 0, $this->R2)) {
            $term = $this->replace($term, '/(?<![c])(heid)/', '', $this->R2);
            if (preg_match('/(?<![aeiouyè]|gem)(ene?)/', $term, $matches, 0, $this->R2)) {
                $term = $this->undouble($this->replace($term, '/(?<![aeiouyè]|gem)(ene?)/', '', $this->R2));
            }

            return $term;
        }

        return $term;
    }

    /**
     * D-suffixes. Search for the longest among the following suffixes, and perform the action indicated.
     */
    public function step3b(string $term): string
    {
        if (preg_match('/eig(end|ing)$/', $term, $matches, 0, $this->R2)) {
            $term = $this->replace($term, '/(eig)end|ing$/', '', $this->R2);
            $term = $this->undouble($term);

            return $term;
        } elseif (preg_match('/ig(end|ing)$/', $term, $matches, 0, $this->R2)) {
            $term = $this->replace($term, '/(igend|iging)$/', '', $this->R2);

            return $term;
        } elseif (preg_match('/end|ing/', $term, $matches, 0, $this->R2)) {
            $term = $this->replace($term, '/(end|ing)$/', '', $this->R2);

            return $term;
        }

        if (preg_match('/(?<![e])ig$/', $term, $matches, 0, $this->R2)) {
            $term = $this->replace($term, '/(?<![e])ig$/', '', $this->R2);

            return $term;
        }

        if (preg_match('/lijk$/', $term, $matches, 0, $this->R2)) {
            $term = $this->replace($term, '/lijk$/', '', $this->R2);
            $term = $this->step2($term);

            return $term;
        }

        if (preg_match('/baar$/', $term, $matches, 0, $this->R2)) {
            $term = $this->replace($term, '/baar$/', '', $this->R2);

            return $term;
        }

        if (preg_match('/bar$/', $term, $matches, 0, $this->R2)) {
            if ($this->removed_E) {
                $term = $this->replace($term, '/bar$/', '', $this->R2);
            }

            return $term;
        }

        return $term;
    }

    /**
     * If the words ends CVD, where C is a non-vowel, D is a non-vowel other than I, and V is double a, e, o or u, remove one of the vowels from V (for example, maan -> man, brood -> brod).
     */
    public function step4(string $term): string
    {
        $letters     = str_split($term);
        $num_letters = count($letters);

        if ($num_letters > 4) {
            $c  = $letters[$num_letters - 4];
            $v1 = $letters[$num_letters - 3];
            $v2 = $letters[$num_letters - 2];
            $d  = $letters[$num_letters - 1];

            if (!$this->isVowel($c) &&
                $this->isVowel($v1) &&
                $this->isVowel($v2) &&
                !$this->isVowel($d) &&
                $v1 == $v2 &&
                $d != 'I' &&
                $v1 != 'i'
            ) {
                unset($letters[$num_letters - 2]);

                $term = implode('', $letters);
            }
        }

        return $term;
    }

    public function isStemmable(string $term): bool
    {
        return ctype_alpha($term);
    }

    public function getRIndex(string $term, int $start): int
    {
        if ($start == 0) {
            $start = 1;
        }

        $letters     = str_split($term);
        $num_letters = count($letters);

        for ($i = $start; $i < $num_letters; $i++) {
            if (!$this->isVowel($letters[$i]) && $this->isVowel($letters[$i - 1])) {
                return $i + 1;
            }
        }

        return $i + 1;
    }

    /**
     * Substitute I and Y ( Put initial y, y after a vowel, and i between vowels into upper case. ).
     */
    public function substituteIAndY(string $term): string
    {
        $letters     = str_split($term);
        $num_letters = count($letters);

        if ($letters[0] == 'y') {
            $letters[0] = 'Y';
        }

        for ($i = 1; $i < $num_letters; $i++) {
            if ($letters[$i] == 'i' && $i + 1 != $num_letters) {
                if ($this->isVowel($letters[$i - 1]) && $this->isVowel($letters[$i + 1])) {
                    $letters[$i] = 'I';
                }
            } elseif ($letters[$i] == 'y') {
                if ($this->isVowel($letters[$i - 1])) {
                    $letters[$i] = 'Y';
                }
            }
        }

        if ($num_letters > 1) {
            $num_letters--;
            if ($letters[$num_letters] == 'y' && $this->isVowel($letters[$num_letters - 1])) {
                $letters[$num_letters] = 'Y';
            }
        }

        $term = implode('', $letters);

        return $term;
    }

    /**
     * Undoubles a word (Define undoubling the ending as removing the last letter if the word ends kk, dd or tt.).
     */
    public function undouble(string $term): string
    {
        if ($this->endsWith($term, 'kk') ||
            $this->endsWith($term, 'tt') ||
            $this->endsWith($term, 'dd')
        ) {
            $term = substr($term, 0, strlen($term) - 1);
        }

        return $term;
    }

    public function replaceSpecialCharacters(string $term): string
    {
        $term = preg_replace("/\é|\ë|\ê/", 'e', $term);
        $term = preg_replace("/\á|\à|ä/", 'a', $term);
        $term = preg_replace("/\ó|\ò|ö/", 'o', $term);
        $term = preg_replace("/\ç/", 'c', $term);
        $term = preg_replace("/\ï/", 'i', $term);
        $term = preg_replace("/\ü/", 'u', $term);
        $term = preg_replace("/\û/", 'u', $term);
        $term = preg_replace("/\î/", 'i', $term);

        return $term;
    }

    public function isVowel(string $letter): bool
    {
        switch ($letter) {
            case 'e':
            case 'a':
            case 'o':
            case 'i':
            case 'u':
            case 'y':
            case 'è':
                return true;
                break;
        }

        return false;
    }

    /**
     * Checks if a strings ends with string
     *
     * @param string $haystack
     * @param string $needle
     * @param bool   $case
     *
     * @return bool
     */
    public function endsWith(string $haystack, string $needle, bool $case = true): bool
    {
        if ($case) {
            return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
        }

        return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
    }

    public function replace(string $word, string $regex, string $replace, int $offset): ?string
    {
        if ($offset > 0) {
            $part1 = substr($word, 0, $offset);
            $part2 = substr($word, $offset, strlen($word));
            $part2 = preg_replace($regex, $replace, $part2);

            return $part1 . '' . $part2;
        } else {
            return preg_replace($regex, $replace, $word);
        }
    }
}
