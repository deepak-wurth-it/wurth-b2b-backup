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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\SeoFilter\Api\Data\RewriteInterface;

class Rewrite extends AbstractModel implements RewriteInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getAttributeCode(): string
    {
        return (string)$this->getData(self::ATTRIBUTE_CODE);
    }

    public function setAttributeCode(string $value): RewriteInterface
    {
        return $this->setData(self::ATTRIBUTE_CODE, $value);
    }

    public function getOption(): string
    {
        return (string)$this->getData(self::OPTION);
    }

    public function setOption(string $value): RewriteInterface
    {
        return $this->setData(self::OPTION, $value);
    }

    public function getRewrite(): string
    {
        return (string)$this->getData(self::REWRITE);
    }

    public function setRewrite(string $value): RewriteInterface
    {
        return $this->setData(self::REWRITE, $value);
    }

    public function getStoreId(): int
    {
        return (int)$this->getData(self::STORE_ID);
    }

    public function setStoreId(int $value): RewriteInterface
    {
        return $this->setData(self::STORE_ID, $value);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Rewrite::class);
    }

}
