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

use Mirasvit\Search\Model\ConfigProvider;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Mirasvit\SearchReport\Service\LogService;

class BotDetectorService
{
    public $possibleInjectionTerms = [
            'admin', 'wp', 'login', 'db', 'zip', 'rar', 'tar', 'gz', 'sql', '7z', 'bz2', 'bak',
            'bck', 'database', 'sid', 'localhost', 'backup', 'magento', 'config', 'passwd',
            'panel', 'mysql', 'admo', 'ajaxplorer', 'dump', 'select', 'where', 'union', 'teal', 'sweatshirt',
        ];

    private $configProvider;

    private $logService;

    public function __construct(
        ConfigProvider $configProvider,
        LogService $logService
    ) {
        $this->configProvider   = $configProvider;
        $this->logService       = $logService;
    }

    public function isBotQuery(string $query): bool
    {
        $result = false;
        $query   = filter_input(INPUT_GET, 'q', FILTER_UNSAFE_RAW);

        if (empty($query)) {
            return $result;
        }

        $ignoredIps = $this->configProvider->getIgnoredIps();

        if (in_array($this->logService->getIp(), $ignoredIps)) {
            $result = true;
        } elseif (preg_match('~' . implode('|', $this->possibleInjectionTerms) . '~', $query)) {
            $result = true;
        } elseif (preg_match('~\.' . implode('|\.', $this->possibleInjectionTerms) . '~', $query)) {
            $result = true;
        } elseif (preg_match('~.*' . implode('|.*', $this->possibleInjectionTerms) . '~', $query)) {
            $result = true;
        } elseif (preg_match(
            "~('(''|[^'])*')|(;)|(\b(ALTER|CREATE|DELETE|DROP|EXEC(UTE){0,1}|INSERT( +INTO){0,1}|MERGE|SELECT|UPDATE|UNION( +ALL){0,1})\b)~i",
            $query)
        ) {
            $result = true;
        }

        return $result;
    }
}
