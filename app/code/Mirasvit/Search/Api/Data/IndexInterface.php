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



namespace Mirasvit\Search\Api\Data;

interface IndexInterface
{
    const TABLE_NAME = 'mst_search_index';

    const ID                    = 'index_id';
    const IDENTIFIER            = 'identifier';
    const TITLE                 = 'title';
    const POSITION              = 'position';
    const ATTRIBUTES_SERIALIZED = 'attributes_serialized';
    const PROPERTIES_SERIALIZED = 'properties_serialized';
    const STATUS                = 'status';
    const IS_ACTIVE             = 'is_active';

    const STATUS_READY   = 1;
    const STATUS_INVALID = 0;

    public function getId(): int;

    public function getIdentifier(): ?string;

    public function setIdentifier(string $input): IndexInterface;

    public function getTitle(): string;

    public function setTitle(string $input): IndexInterface;

    public function getPosition(): int;

    public function setPosition(int $value): IndexInterface;

    public function getAttributes(): array;

    public function setAttributes(array $value): IndexInterface;

    public function getProperties(): array;

    public function setProperties(array $value): IndexInterface;

    public function getStatus(): int;

    public function setStatus(int $value): IndexInterface;

    public function getIsActive(): bool;

    public function setIsActive(bool $value): IndexInterface;

    public function getProperty(string $key): string;

    /**
     * @param string $key
     *
     * @return mixed|array
     */
    public function getData($key = null);

    /**
     * @param string|array     $key
     * @param string|int|array $value
     *
     * @return $this
     */
    public function setData($key, $value = null);

    /**
     * @param array $data
     *
     * @return $this
     */
    public function addData(array $data);
}
