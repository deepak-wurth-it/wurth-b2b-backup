<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Model\Feed;

use Amasty\Base\Model\Feed\FeedTypes\Ads;
use Magento\Framework\Module\FullModuleList;

/**
 * Class AdsProvider provide ads data
 */
class AdsProvider
{
    /**
     * @var FullModuleList
     */
    private $moduleList;

    /**
     * @var Ads
     */
    private $adsFeed;

    /**
     * @var array
     */
    private $upsellModuleAds = [];

    public function __construct(
        FullModuleList $moduleList,
        Ads $adsFeed
    ) {
        $this->moduleList = $moduleList;
        $this->adsFeed = $adsFeed;
    }

    /**
     * @param string $moduleCode
     *
     * @return array
     */
    public function getDisplayAdvertise($moduleCode)
    {
        $adsData = $this->adsFeed->execute();

        return $this->getActiveAdvertise($adsData, $moduleCode);
    }

    /**
     * Sorting $sortAds by priority in CSV
     *
     * @param array $currentElement
     * @param array $nextElement
     *
     * @return int
     */
    protected function sortByPriority($currentElement, $nextElement)
    {
        if ($currentElement['priority'] == $nextElement['priority']) {
            return 0;
        }

        return ($currentElement['priority'] < $nextElement['priority']) ? -1 : 1;
    }

    /**
     * @param array $adsData
     * @param string $moduleCode
     *
     * @return array
     */
    private function getActiveAdvertise($adsData, $moduleCode)
    {
        $upsellModuleAds = $this->getUpsell($adsData, $moduleCode);

        foreach ($upsellModuleAds as $advertise) {
            if (isset($advertise['upsell_module_code']) && !empty($advertise['upsell_module_code'])
                && !$this->moduleList->has($advertise['upsell_module_code'])
            ) {
                return $advertise;
            }
        }

        return [];
    }

    /**
     * @param array $adsData
     * @param string $moduleCode
     *
     * @return array
     */
    private function getUpsell($adsData, $moduleCode)
    {
        if (isset($this->upsellModuleAds[$moduleCode])) {
            return $this->upsellModuleAds[$moduleCode];
        }
// If you need to return information sorted by priority, uncomment the following line
//        return $this->upsellModuleAds[$moduleCode] = $this->getUpsellSortedByPriority($adsData, $moduleCode);
        return $this->upsellModuleAds[$moduleCode] = $this->getUpsellRandom($adsData, $moduleCode);
    }

    /**
     * @param array $adsData
     * @param string $moduleCode
     *
     * @return array
     */
    private function getUpsellSortedByPriority($adsData, $moduleCode)
    {
        $sortAds = [];
        $emptyPriority = [];

        foreach ($adsData as $moduleAds) {
            if ((isset($moduleAds['module_code']) && $moduleAds['module_code'] === $moduleCode)
                || ($this->isAllowedEverywhere($moduleAds) && 'Amasty_Base' !== $moduleCode)
            ) {
                if (isset($moduleAds['priority'])) {
                    $moduleAds['priority'] = str_replace(' ', '', $moduleAds['priority']);

                    if ($moduleAds['priority'] === '' || !is_numeric($moduleAds['priority'])) {
                        $emptyPriority[] = $moduleAds;
                    } else {
                        $priority = (int)$moduleAds['priority'];

                        while (isset($sortAds[$priority])) {
                            $priority++;
                        }
                        $sortAds[$priority] = $moduleAds;
                    }
                }
            }
        }

        usort($sortAds, [$this, 'sortByPriority']);

        if (!empty($emptyPriority)) {
            end($sortAds);         // move the internal pointer to the end of the array
            $lastKeySortAds = key($sortAds) + 1;

            foreach ($emptyPriority as $emptyPriorityElement) {
                $sortAds[$lastKeySortAds] = $emptyPriorityElement;
                $lastKeySortAds++;
            }
        }

        return $sortAds;
    }

    /**
     * @param array $adsData
     * @param string $moduleCode
     *
     * @return array
     */
    private function getUpsellRandom($adsData, $moduleCode)
    {
        $sortAds = $this->getUpsellSortedByPriority($adsData, $moduleCode);
        shuffle($sortAds);

        return $sortAds;
    }

    /**
     * @param array $moduleAds
     *
     * @return bool
     */
    private function isAllowedEverywhere($moduleAds)
    {
        return isset($moduleAds['module_code']) && '*' === $moduleAds['module_code'];
    }
}
