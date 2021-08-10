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

namespace Mirasvit\QuickNavigation\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\QuickNavigation\Api\Data\SequenceInterface;

class Sequence extends AbstractModel implements SequenceInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID)
            ? (int)$this->getData(self::ID)
            : null;
    }

    public function getStoreId(): int
    {
        return (int)$this->getData(self::STORE_ID);
    }

    public function setStoreId(int $value): SequenceInterface
    {
        return $this->setData(self::STORE_ID, $value);
    }

    public function getCategoryId(): int
    {
        return (int)$this->getData(self::CATEGORY_ID);
    }

    public function setCategoryId(int $value): SequenceInterface
    {
        return $this->setData(self::CATEGORY_ID, $value);
    }

    public function getSequence(): string
    {
        return (string)$this->getData(self::SEQUENCE);
    }

    public function setSequence(string $value): SequenceInterface
    {
        return $this->setData(self::SEQUENCE, $value);
    }

    public function getLength(): int
    {
        return (int)$this->getData(self::LENGTH);
    }

    public function setLength(int $value): SequenceInterface
    {
        return $this->setData(self::LENGTH, $value);
    }

    public function getPopularity(): int
    {
        return (int)$this->getData(self::POPULARITY);
    }

    public function setPopularity(int $value): SequenceInterface
    {
        return $this->setData(self::POPULARITY, $value);
    }

    protected function _construct(): void
    {
        $this->_init(ResourceModel\Sequence::class);
    }
}
