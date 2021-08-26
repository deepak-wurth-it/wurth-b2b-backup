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

namespace Mirasvit\Brand\Api\Data;

interface BrandInterface
{
    const ID             = 'value';
    const LABEL          = 'label';
    const PAGE           = 'page';
    const ATTRIBUTE_ID   = 'attribute_id';
    const ATTRIBUTE_CODE = 'attribute_code';


    public function getId(): int;

    public function getAttributeId(): int;

    public function getAttributeCode(): string;

    public function getLabel(): string;

    public function getUrl(): string;

    public function getUrlKey(): string;

    public function getImage(): string;

    public function getPage(): ?BrandPageInterface;

    /**
     * @param string|array $key
     * @param mixed        $value
     *
     * @return $this
     */
    public function setData($key, $value = null);

    /**
     * @param string     $key
     * @param string|int $index
     *
     * @return mixed
     */
    public function getData($key = '', $index = null);
}
