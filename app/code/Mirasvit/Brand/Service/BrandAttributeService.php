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

namespace Mirasvit\Brand\Service;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Brand\Api\Data\BrandInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Repository\BrandPageRepository;

class BrandAttributeService
{
    private $brandPagesByOptions = [];

    private $config;

    private $productAttributeRepository;

    private $brandPageRepository;

    private $storeManager;

    public function __construct(
        Config $config,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        BrandPageRepository $brandPageRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->config                     = $config;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->brandPageRepository        = $brandPageRepository;
        $this->storeManager               = $storeManager;
    }

    public function getBrandAttributeId(): ?int
    {
        $brandAttributeId = null;
        if ($brandAttributeCode = $this->config->getGeneralConfig()->getBrandAttribute()) {
            $brandAttributeId = (int)$this->getAttribute()->getAttributeId();
        }

        return $brandAttributeId;
    }

    public function getVisibleBrandOptions(): array
    {
        $visibleOptions = [];

        if ($this->config->getGeneralConfig()->getBrandAttribute()) {
            $isShowNotConfiguredBrands = $this->config->getGeneralConfig()->isShowNotConfiguredBrands();
            $brandPages                = $this->getBrandPagesByOptions();
            $attribute                 = $this->getAttribute();

            foreach ($this->getBrandOptions() as $idx => $option) {
                $page = isset($brandPages[$option['value']]) ? $brandPages[$option['value']] : null;

                if ($isShowNotConfiguredBrands || $page) {
                    $option[BrandInterface::PAGE]           = $page;
                    $option[BrandInterface::ATTRIBUTE_ID]   = $attribute->getId();
                    $option[BrandInterface::ATTRIBUTE_CODE] = $attribute->getAttributeCode();

                    $visibleOptions[] = $option;
                }
            }
        }

        return $visibleOptions;
    }

    public function getAllBrandOptions(): array
    {
        $options = [];

        if ($this->config->getGeneralConfig()->getBrandAttribute()) {
            $brandPages = $this->getBrandPagesByOptions();
            $attribute  = $this->getAttribute();

            foreach ($this->getBrandOptions() as $idx => $option) {
                $page = isset($brandPages[$option['value']]) ? $brandPages[$option['value']] : null;

                if ($page == null) {
                    $page = $this->brandPageRepository->create()
                        ->setAttributeOptionId((int)$option['value']);
                }

                $option[BrandInterface::PAGE]           = $page;
                $option[BrandInterface::ATTRIBUTE_ID]   = $attribute->getId();
                $option[BrandInterface::ATTRIBUTE_CODE] = $attribute->getAttributeCode();

                $options[] = $option;
            }
        }

        return $options;
    }


    private function getBrandOptions(): array
    {
        $options = $this->getAttribute()->getSource()->getAllOptions();

        foreach ($options as $idx => $option) {
            if (!$option['value'] || !$option['label']) {
                unset($options[$idx]);
            }
        }

        return $options;
    }

    /**
     * {{@inheritdoc}}
     */
    private function getBrandPagesByOptions()
    {
        if (!$this->brandPagesByOptions) {
            $brandPageCollection = $this->brandPageRepository->getCollection()
                ->addStoreFilter($this->storeManager->getStore())
                ->addFieldToFilter(BrandPageInterface::ATTRIBUTE_ID, $this->getAttribute()->getId())
                ->addFieldToFilter(BrandPageInterface::IS_ACTIVE, 1);

            /** @var BrandPageInterface $item */
            foreach ($brandPageCollection as $item) {
                $this->brandPagesByOptions[$item->getAttributeOptionId()] = $item;
            }
        }

        return $this->brandPagesByOptions;
    }

    /**
     * Get attribute used as the brand.
     * @return AbstractAttribute
     */
    private function getAttribute()
    {
        return $this->productAttributeRepository->get($this->config->getGeneralConfig()->getBrandAttribute());
    }
}
