<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\Catalog\Api\Data;

interface ExtensionAttributeGetterSetterInterface
{

    const ID = 'id';
    const VALUE = 'value';

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * @param $id
     * @return mixed
     */
    public function setId($id);

    /**
     * Get content
     * @return string|null
     */
    public function getValue();

    /**
     * Set value
     * @param string $value
     * @return \Wcb\Catalog\Model\ExtensionAttributeGetterSetter
     */
    public function setValue($value);
}

