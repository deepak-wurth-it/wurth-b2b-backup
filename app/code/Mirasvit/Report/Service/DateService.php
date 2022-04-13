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



namespace Mirasvit\Report\Service;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Report\Api\Service\DateServiceInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class DateService implements DateServiceInterface
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * DateService constructor.
     * @param TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        TimezoneInterface $localeDate,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->dateTime     = $dateTime;
        $this->storeManager = $storeManager;
        $this->scopeConfig  = $scopeConfig;
        $this->localeDate   = $localeDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getIntervals($addHint = false)
    {
        $intervals = [];

        $intervals[self::TODAY]        = 'Today';
        $intervals[self::YESTERDAY]    = 'Yesterday';
        $intervals[self::LAST_7_DAYS]  = 'Last 7 days';
        $intervals[self::LAST_30_DAYS] = 'Last 30 days';
        $intervals[self::LAST_90_DAYS] = 'Last 90 days';

        $intervals[self::THIS_WEEK]    = 'Week to date';
        $intervals[self::THIS_MONTH]   = 'Month to date';
        $intervals[self::THIS_QUARTER] = 'Quarter to date';
        $intervals[self::THIS_YEAR]    = 'Year to date';

        $intervals[self::PREVIOUS_WEEK]  = 'Last week';
        $intervals[self::PREVIOUS_MONTH] = 'Last month';
        $intervals[self::PREVIOUS_YEAR]  = 'Last year';

        $intervals[self::LIFETIME] = 'Lifetime';

        if ($addHint) {
            foreach ($intervals as $code => $label) {
                $label = __($label);

                $hint = $this->getIntervalHint($code);

                if ($hint) {
                    $label .= ' / ' . $hint;

                    $intervals[$code] = $label . '';
                }
            }
        }

        return $intervals;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval($code, $inStoreTZ = false)
    {
        // we use local timestamp for select correct day (UTC 12 Aug, 00:10, EST 11 Aug, 20:10)
        $timestamp = strtotime($this->localeDate->date()->format('Y-m-d H:i:s'));

        $firstDay = (int)$this->scopeConfig->getValue('general/locale/firstday');
        $locale   = $this->scopeConfig->getValue(DirectoryHelper::XML_PATH_DEFAULT_LOCALE);


        $from = new \Zend_Date(
            $timestamp,
            null,
            $locale
        );

        $to = clone $from;

        switch ($code) {
            case self::TODAY:
                $from->setTime('00:00:00');

                $to->setTime('23:59:59');

                break;

            case self::YESTERDAY:
                $from->subDay(1)
                    ->setTime('00:00:00');

                $to->subDay(1)
                    ->setTime('23:59:59');

                break;

            case self::THIS_MONTH:
                $from->setDay(1)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->addDay($to->get(\Zend_Date::MONTH_DAYS) - 1)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_MONTH:
            case 'last_month':
                $from->setDay(1)
                    ->subMonth(1)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->subMonth(1)
                    ->addDay($to->get(\Zend_Date::MONTH_DAYS) - 1)
                    ->setTime('23:59:59');

                break;

            case self::THIS_QUARTER:
                $month = intval($from->get(\Zend_Date::MONTH) / 4) * 3 + 1;
                $from->setDay(1)
                    ->setMonth($month)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->setMonth($month)
                    ->addMonth(3)
                    ->subDay(1)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_QUARTER:
            case 'last_quarter':
                $month = intval($from->get(\Zend_Date::MONTH) / 4) * 3 + 1;

                $from->setDay(1)
                    ->setMonth($month)
                    ->subMonth(3)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->setMonth($month)
                    ->addMonth(3)
                    ->subDay(1)
                    ->subMonth(3)
                    ->setTime('23:59:59');

                break;

            case self::THIS_YEAR:
                $from->setDay(1)
                    ->setMonth(1)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->setMonth(1)
                    ->addDay($to->get(\Zend_Date::LEAPYEAR) ? 365 : 364)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_YEAR:
            case 'last_year':
                $from->setDay(1)
                    ->setMonth(1)
                    ->subYear(1)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->setMonth(1)
                    ->addDay($to->get(\Zend_Date::LEAPYEAR) ? 365 : 364)
                    ->subYear(1)
                    ->setTime('23:59:59');

                break;

            case self::THIS_WEEK:
                $weekday = $from->get(\Zend_Date::WEEKDAY_DIGIT); #0-6

                if ($weekday < $firstDay) {
                    $weekday += 7;
                }

                $from->subDay($weekday - $firstDay)
                    ->setTime('00:00:00');

                $to->addDay(6 - $weekday + $firstDay)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_WEEK:
            case 'last_week':
                $weekday = $from->get(\Zend_Date::WEEKDAY_DIGIT); #0-6

                if ($weekday < $firstDay) {
                    $weekday += 7;
                }

                $from->subDay($weekday - $firstDay)
                    ->subWeek(1)
                    ->setTime('00:00:00');

                $to->addDay(6 - $weekday + $firstDay)
                    ->subWeek(1)
                    ->setTime('23:59:59');

                break;

            case self::LAST_7_DAYS:
                $from->subDay(6)->setTime('00:00:00');
                $to->setTime('23:59:59');

                break;

            case self::LAST_30_DAYS:
                $from->subDay(29)->setTime('00:00:00');
                $to->setTime('23:59:59');

                break;

            case self::LAST_90_DAYS:
                $from->subDay(89)->setTime('00:00:00');
                $to->setTime('23:59:59');

                break;

            case self::LIFETIME:
            case 'life':
                $from->subYear(10);

                $to->addYear(10);

                break;
        }

        return new \Magento\Framework\DataObject([
            'from' => $from,
            'to'   => $to,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIntervalHint($code)
    {
        $hint = '';

        $interval = $this->getInterval($code, true);
        $from     = $interval->getFrom();
        $to       = $interval->getTo();

        switch ($code) {
            case self::TODAY:
            case self::YESTERDAY:
                $hint = $from->get('MMM, d HH:mm') . ' - ' . $to->get('HH:mm');
                break;

            case self::THIS_WEEK:
            case self::PREVIOUS_WEEK:
            case self::LAST_7_DAYS:
            case self::LAST_30_DAYS:
            case self::LAST_90_DAYS:
            case self::THIS_MONTH:
            case self::PREVIOUS_MONTH:
            case self::THIS_QUARTER:
            case self::PREVIOUS_QUARTER:
                if ($from->get('YYYY') == $to->get('YYYY') && $from->get('YYYY') == date('Y')) {
                    if ($from->get('MMM') == $to->get('MMM')) {
                        $hint = $from->get('MMM, d') . ' - ' . $to->get('d');
                    } else {
                        $hint = $from->get('MMM, d') . ' - ' . $to->get('MMM, d');
                    }
                } else {
                    $hint = $from->get('MMM, d YYYY') . ' - ' . $to->get('MMM, d YYYY');
                }

                break;

            case self::THIS_YEAR:
            case self::PREVIOUS_YEAR:
                $hint = $from->get('MMM, d YYYY') . ' - ' . $to->get('MMM, d');
                break;

            //            case self::LAST_24H:
            //                $hint = $from->get('MMM, d HH:mm') . ' - ' . $to->get('MMM, d HH:mm');
            //                break;

            case self::LIFETIME:
                $hint = $from->get('MMM, d YYYY') . ' - ' . $to->get('MMM, d YYYY');
                break;
        }

        return $hint;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousInterval($interval, $offset, $inStoreTZ = false)
    {
        $diff = clone $interval->getTo();
        $diff->sub($interval->getFrom());

        if ($inStoreTZ) {
            $diff->sub($this->dateTime->getGmtOffset());
        }

        $now = new \Zend_Date(
            $this->dateTime->gmtTimestamp(),
            null,
            $this->storeManager->getStore()->getLocaleCode()
        );
        if ($interval->getTo()->getTimestamp() > $now->getTimestamp()) {
            $interval->getTo()->subTimestamp($interval->getTo()->getTimestamp() - $now->getTimestamp());
        }

        $interval->getTo()->setTime('23:59:59');

        if ($offset === self::OFFSET_YEAR) {
            $interval->getFrom()->subYear(1);
            $interval->getTo()->subYear(1);
        } elseif ($offset === self::OFFSET_MONTH) {
            $interval->getFrom()->subMonth(1);
            $interval->getTo()->subMonth(1);
        } elseif ($offset === self::OFFSET_WEEK) {
            $interval->getFrom()->subWeek(1);
            $interval->getTo()->subWeek(1);
        } else {
            $interval->getFrom()->sub($diff);
            $interval->getTo()->sub($diff);
        }

        return $interval;
    }

    /**
     * {@inheritdoc}
     */
    public function toZendDate($date, $format = \Zend_Date::ISO_8601)
    {
        if ($date instanceof \Zend_Date) {
            return $date;
        }

        return new \Zend_Date($date, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function toMysqlDate($date)
    {
        if ($date instanceof \Zend_Date) {
            return $date->toString('Y-MM-dd HH:mm:ss');
        }

        return date('YYYY-MM-dd HH:mm:ss', strtotime($date));
    }

    /**
     * @param string|\Zend_Date $from
     * @param string|\Zend_Date $to
     * @return \Magento\Framework\DataObject|\Mirasvit\Report\Api\Service\IntervalInterface
     */
    public function toInterval($from, $to)
    {
        return new \Magento\Framework\DataObject([
            'from' => $this->toZendDate($from),
            'to'   => $this->toZendDate($to),
        ]);
    }
}
