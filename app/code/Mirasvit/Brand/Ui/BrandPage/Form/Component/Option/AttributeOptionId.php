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

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Select;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Repository\BrandPageRepository;

class AttributeOptionId extends Select
{
    private $config;

    private $brandPageRepository;

    public function __construct(
        ContextInterface $context,
        BrandPageRepository $brandPageRepository,
        Config $config,
        AttributeOptionIdSource $optionIdSource,
        array $options = null,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $options,
            $components,
            $data
        );

        $this->brandPageRepository = $brandPageRepository;
        $this->config              = $config;

        $this->init($optionIdSource);
    }

    public function prepare()
    {
        $config = $this->getData('config');
        if ($brandPageId = $this->getBrandPageId()) {
            $this->getPreparedConfig($brandPageId, $config);
        }
        $this->setData('config', $config);

        parent::prepare();
    }

    private function init(AttributeOptionIdSource $optionIdSource): void
    {
        $brandPageId = $this->getBrandPageId();

        if ($brandPageId) {
            $attributeCode = $this->getBrandAttributeCode($brandPageId);
            $optionIdSource->setCurrentOptionId($this->getBrand($brandPageId)->getAttributeOptionId());
        } else {
            $attributeCode = $this->config->getGeneralConfig()->getBrandAttribute();
        }
        $this->options = $optionIdSource->setAttributeCode($attributeCode)->toOptionArray();
    }

    private function getBrandPageId(): int
    {
        $context = $this->getContext();

        return (int)$context->getRequestParam($context->getDataProvider()->getRequestFieldName());
    }

    private function getBrandAttributeCode(int $brandPageId): string
    {
        return $this->getBrand($brandPageId)->getAttributeCode();
    }


    private function getBrand(int $brandPageId): BrandPageInterface
    {
        return $this->brandPageRepository->get($brandPageId);
    }


    private function getPreparedConfig(int $brandPageId, array $config): array
    {
        $brandAttributeCode  = $this->getBrandAttributeCode($brandPageId);
        $configAttributeCode = $this->config->getGeneralConfig()->getBrandAttribute();
        if ($brandAttributeCode != $configAttributeCode) {
            $config['disabled'] = true;
        }

        return $config;
    }
}
