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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\QuickNavigation\Service;

use Mirasvit\QuickNavigation\Api\Data\SequenceInterface;
use Mirasvit\QuickNavigation\Context;
use Mirasvit\QuickNavigation\Repository\SequenceRepository;

class SequenceService
{
    private $sequenceRepository;

    private $context;

    public function __construct(
        SequenceRepository $sequenceRepository,
        Context $context
    ) {
        $this->sequenceRepository = $sequenceRepository;
        $this->context            = $context;
    }

    public function createSequence(): SequenceInterface
    {
        $sequence = $this->sequenceRepository->create();

        $sequence->setStoreId($this->context->getStoreId())
            ->setCategoryId($this->context->getCategoryId());

        $sequence->setSequence($this->context->getSequenceString())
            ->setLength($this->context->getSequenceLength());

        return $sequence;
    }

    public function ensureSequence(SequenceInterface $sequence): SequenceInterface
    {
        $collection = $this->sequenceRepository->getCollection();
        $collection->addFieldToFilter(SequenceInterface::STORE_ID, $sequence->getStoreId())
            ->addFieldToFilter(SequenceInterface::CATEGORY_ID, $sequence->getCategoryId())
            ->addFieldToFilter(SequenceInterface::SEQUENCE, $sequence->getSequence())
            ->setPageSize(1);

        if ($collection->getFirstItem()->getId()) {
            return $collection->getFirstItem();
        }

        return $this->sequenceRepository->save($sequence);
    }

    public function increasePopularity(SequenceInterface $sequence): SequenceInterface
    {
        $sequence->setPopularity($sequence->getPopularity() + 1);
        $this->sequenceRepository->save($sequence);

        return $sequence;
    }
}
