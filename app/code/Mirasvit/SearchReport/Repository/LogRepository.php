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

namespace Mirasvit\SearchReport\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\SearchReport\Api\Data\LogInterface;
use Mirasvit\SearchReport\Api\Data\LogInterfaceFactory;

class LogRepository
{
    private $logFactory;

    private $entityManager;

    public function __construct(
        LogInterfaceFactory $logFactory,
        EntityManager $entityManager
    ) {
        $this->logFactory    = $logFactory;
        $this->entityManager = $entityManager;
    }

    public function create(): LogInterface
    {
        return $this->logFactory->create();
    }

    public function get(int $id): ?LogInterface
    {
        $log = $this->logFactory->create();

        $this->entityManager->load($log, $id);

        return $log->getId() ? $log : null;
    }

    public function save(LogInterface $log): LogInterface
    {
        $log->setQuery(strtolower($log->getQuery()))
            ->setMisspellQuery(strtolower($log->getMisspellQuery()))
            ->setFallbackQuery(strtolower($log->getFallbackQuery()));

        return $this->entityManager->save($log);
    }
}
