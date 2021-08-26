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

namespace Mirasvit\SeoFilter\Plugin\Backend;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Mirasvit\SeoFilter\Repository\RewriteRepository;
use Mirasvit\SeoFilter\Service\RewriteService;

/**
 * @see \Magento\Catalog\Model\ResourceModel\Eav\Attribute::save()
 * @SuppressWarnings(PHPMD)
 */
class SaveRewriteOnAttributeSavePlugin
{
    private $rewriteService;

    private $rewriteRepository;

    public function __construct(
        RewriteService $rewriteService,
        RewriteRepository $rewriteRepository
    ) {
        $this->rewriteService    = $rewriteService;
        $this->rewriteRepository = $rewriteRepository;
    }

    /**
     * @param Attribute $subject
     * @param \Closure  $proceed
     *
     * @return Attribute
     */
    public function aroundSave($subject, \Closure $proceed)
    {
        $attributeCode = (string)$subject->getAttributeCode();

        if (!$attributeCode) {
            return $proceed();
        }

        $seoFilterData = $subject->getData('seo_filter');


        if (isset($seoFilterData['attribute'])) {
            foreach ($seoFilterData['attribute'] as $storeId => $urlRewrite) {
                $storeId    = (int)$storeId;
                $urlRewrite = $urlRewrite ? (string)$urlRewrite : $attributeCode;

                $rewrite = $this->rewriteService->getAttributeRewrite(
                    $attributeCode,
                    $storeId
                );

                if ($rewrite) {
                    $rewrite->setRewrite($urlRewrite);
                    $this->rewriteRepository->save($rewrite);
                }
            }
        }

        if (isset($seoFilterData['options'])) {
            foreach ($seoFilterData['options'] as $optionId => $item) {
                $optionId = (string)$optionId;
                foreach ($item as $storeId => $urlRewrite) {
                    $storeId    = (int)$storeId;
                    $urlRewrite = (string)$urlRewrite;

                    if (!$urlRewrite) {
                        continue;
                    }

                    $rewrite = $this->rewriteService->getOptionRewrite(
                        $attributeCode,
                        $optionId,
                        $storeId
                    );

                    if ($rewrite) {
                        $rewrite->setRewrite($urlRewrite);
                        $this->rewriteRepository->save($rewrite);
                    }
                }
            }

        }

        return $proceed();
    }
}
