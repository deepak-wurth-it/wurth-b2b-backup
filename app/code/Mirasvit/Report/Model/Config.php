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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Model;

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Backend\Model\Locale\Resolver as LocaleResolver;
use Magento\Framework\App\Filesystem\DirectoryList;

class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem           $filesystem
     * @param LocaleResolver       $localeResolver
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        LocaleResolver $localeResolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeResolver = $localeResolver;
        $this->filesystem = $filesystem;
    }

    /**
     * @return array
     */
    public function getLocaleData()
    {
        /** @var mixed $localeData */
        $localeData = (new DataBundle())->get($this->localeResolver->getLocale());

        $daysData = $localeData['calendar']['gregorian']['dayNames'];
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];

        $data = [
            'days'     => [
                'wide'        => array_values(iterator_to_array($daysData['format']['wide'])),
                'abbreviated' => array_values(iterator_to_array($daysData['format']['abbreviated']))

            ],
            'months'   => [
                'wide'        => array_values(iterator_to_array($monthsData['format']['wide'])),
                'abbreviated' => array_values(iterator_to_array($monthsData['format']['abbreviated']))
            ],
            'firstDay' => $this->scopeConfig->getValue('general/locale/firstday')
        ];

        return $data;
    }
}
