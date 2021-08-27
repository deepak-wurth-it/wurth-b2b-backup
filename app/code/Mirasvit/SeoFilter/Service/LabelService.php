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

namespace Mirasvit\SeoFilter\Service;

use Magento\Framework\Filter\FilterManager;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Mirasvit\SeoFilter\Api\Data\RewriteInterface;
use Mirasvit\SeoFilter\Model\ConfigProvider;
use Mirasvit\SeoFilter\Model\Context;
use Mirasvit\SeoFilter\Repository\RewriteRepository;

class LabelService
{
    private $filterManager;

    private $rewriteRepository;

    private $configProvider;

    private $context;

    private $urlRewriteCollectionFactory;

    private $urlService;


    public function __construct(
        FilterManager $filter,
        RewriteRepository $rewriteRepository,
        UrlRewriteCollectionFactory $urlRewriteCollectionFactory,
        UrlService $urlService,
        ConfigProvider $configProvider,
        Context $context
    ) {
        $this->filterManager               = $filter;
        $this->rewriteRepository           = $rewriteRepository;
        $this->urlRewriteCollectionFactory = $urlRewriteCollectionFactory;
        $this->urlService                  = $urlService;
        $this->configProvider              = $configProvider;
        $this->context                     = $context;
    }

    public function createLabel(string $attributeCode, string $itemValue): string
    {
        if ($this->context->isDecimalAttribute($attributeCode)) {
            if ($this->configProvider->getUrlFormat() == ConfigProvider::URL_FORMAT_ATTR_OPTIONS) {
                $label = $itemValue;
            } else {
                $label = str_replace('-', ConfigProvider::SEPARATOR_DECIMAL, $itemValue);
                $label = $attributeCode . ConfigProvider::SEPARATOR_DECIMAL . $label;
            }
        } else {
            $itemValue = preg_replace('/[™℠®©]/', '', $itemValue);

            $label = strtolower($this->filterManager->translitUrl($itemValue));

            $label = $this->getLabelWithSeparator($label);
        }

        return $label;
    }

    public function uniqueLabel(string $label, ?int $storeId = null, int $suffix = 0): string
    {
        if ($storeId === null) {
            $storeId = $this->context->getStoreId();
        }

        $newLabel = $suffix ? $label . '_' . $suffix : $label;

        $path = $this->urlService->trimCategorySuffix($this->context->getRequest()->getOriginalPathInfo());

        $possiblePath = $path . '/' . $newLabel;

        $isExists = $this->urlRewriteCollectionFactory->create()
            ->addFieldToFilter('entity_type', 'category')
            ->addFieldToFilter('redirect_type', 0)
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('request_path', $possiblePath)
            ->getSize();

        if ($isExists) {
            return $this->uniqueLabel($label, $storeId, $suffix + 1);
        }

        $isExists = $this->rewriteRepository->getCollection()
            ->addFieldToFilter(RewriteInterface::REWRITE, $newLabel)
            ->addFieldToFilter(RewriteInterface::STORE_ID, $storeId)
            ->getSize();

        if ($isExists) {
            return $this->uniqueLabel($label, $storeId, $suffix + 1);
        }

        return $newLabel;
    }

    private function getLabelWithSeparator(string $label): string
    {
        $label = str_replace('__', '_', $label);

        switch ($this->configProvider->getNameSeparator()) {
            case ConfigProvider::NAME_SEPARATOR_NONE:
                $label = str_replace(ConfigProvider::SEPARATOR_FILTERS, '', $label);
                break;

            case ConfigProvider::NAME_SEPARATOR_DASH:
                $label = str_replace(ConfigProvider::SEPARATOR_FILTERS, '_', $label);
                break;

            case ConfigProvider::NAME_SEPARATOR_CAPITAL:
                $labelExploded = explode(ConfigProvider::SEPARATOR_FILTERS, $label);
                $labelExploded = array_map('ucfirst', $labelExploded);

                $label = implode('', $labelExploded);
                $label = lcfirst($label);
                break;
        }

        return $label;
    }
}
