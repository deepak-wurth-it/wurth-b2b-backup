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


use Magento\Framework\Locale\Resolver as LocaleResolver;

class StemmingService
{
    /**
     * @var Stemming\StemmerInterface[]
     */
    private $pool;

    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    public function __construct(
        LocaleResolver $localeResolver,
        array $pool = []
    ) {
        $this->localeResolver = $localeResolver;
        $this->pool           = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function singularize(string $string)
    {
        // string is too short
        if (strlen($string) < 3) {
            return $string;
        }

        $locale = strtolower(explode('_', $this->localeResolver->getLocale())[0]);

        if (array_key_exists($locale, $this->pool)) {
            return $this->pool[$locale]->singularize($string);
        }

        return $string;
    }
}
