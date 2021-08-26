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

namespace Mirasvit\Brand\Ui\BrandPage\Form\Component\Option;

use Mirasvit\Brand\Model\ResourceModel\BrandPage as BrandPageResource;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;

class AttributeOptionIdSource implements OptionSourceInterface
{
    /**
     * @var string
     */
    private $attributeCode;

    /**
     * @var int
     */
    private $currentOptionId;
    private $options;
    private $brandPageResource;
    private $attributeRepository;

    public function __construct(
        BrandPageResource $brandPageResource,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->brandPageResource = $brandPageResource;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param int $optionId
     * @return $this
     */
    public function setCurrentOptionId($optionId)
    {
        $this->currentOptionId = $optionId;
        return $this;
    }

    /**
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        $this->attributeCode = $attributeCode;
        return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray()
    {
        if ($this->attributeCode) {
            $attributeCode = $this->attributeCode;
        } else {
            return [];
        }

        if (isset($this->options[$attributeCode])) {
            return $this->options[$attributeCode];
        }

        $attribute = $this->attributeRepository->get(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );

        $this->options[$attributeCode] = [];
        $options = $attribute->getSource()->getAllOptions(true, true);
        $appliedOptionIds = $this->brandPageResource->getAppliedOptionIds();
        foreach ($options as $option) {
            $value = $option['value'];
            if (!in_array($value, $appliedOptionIds)
                || ($this->currentOptionId && $value == $this->currentOptionId)
            ) {
                $this->options[$attributeCode][] = [
                    'value' => $value,
                    'label' => __($option['label'])
                ];
            }
        }

        return $this->options[$attributeCode];
    }
}
