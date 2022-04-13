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



namespace Mirasvit\SearchSphinx\SearchAdapter\Field;

class Field implements FieldInterface
{
    private $column;

    private $attributeId;

    private $type;

    public function __construct(
        string $column,
        ?int $attributeId = null,
        int $type = self::TYPE_FULLTEXT
    ) {
        $this->column      = $column;
        $this->attributeId = $attributeId;
        $this->type        = $type;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getAttributeId(): ?int
    {
        return $this->attributeId;
    }

    public function getType(): int
    {
        return $this->type;
    }
}
