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



namespace Mirasvit\SearchReport\Service;

use Magento\Customer\Model\SessionFactory;
use Mirasvit\SearchReport\Api\Data\LogInterface;
use Mirasvit\SearchReport\Repository\LogRepository;

class LogService
{
    const MAX_QUERY_LOG_SIZE = 50000;

    private $logRepository;

    private $sessionFactory;

    public function __construct(
        LogRepository $logRepository,
        SessionFactory $sessionFactory
    ) {
        $this->logRepository  = $logRepository;
        $this->sessionFactory = $sessionFactory;
    }

    public function logQuery(string $query, int $results, string $source, ?string $misspellQuery, ?string $fallbackQuery): ?LogInterface
    {
        if (trim($query) == "") {
            return null;
        }

        $log = $this->logRepository->create();

        $session = $this->sessionFactory->create();

        $log->setQuery($query)
            ->setResults($results)
            ->setIp($this->getIp())
            ->setSession($session->getSessionId())
            ->setSource($source);

        if ($misspellQuery) {
            $log->setMisspellQuery($misspellQuery);
        }

        if ($fallbackQuery) {
            $log->setFallbackQuery($fallbackQuery);
        }

        if ($session->getCustomerId()) {
            $log->setCustomerId((int)$session->getCustomerId());
        }

        return $this->logRepository->save($log);
    }

    public function logClick(int $logId): ?LogInterface
    {
        $log = $this->logRepository->get($logId);

        if ($log) {
            $log->setClicks($log->getClicks() + 1);

            return $this->logRepository->save($log);
        }

        return null;
    }

    public function getIp(): string
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}
