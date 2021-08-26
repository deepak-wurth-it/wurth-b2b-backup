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

namespace Mirasvit\QuickNavigation\Block;

use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\View\Element\Template;
use Mirasvit\QuickNavigation\Model\ConfigProvider;
use Mirasvit\QuickNavigation\Service\PredictService;

class FilterList extends Template
{
    private $configProvider;

    private $predictService;

    private $layerResolver;

    public function __construct(
        ConfigProvider $configProvider,
        PredictService $predictService,
        LayerResolver $layerResolver,
        Template\Context $context,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->predictService = $predictService;
        $this->layerResolver  = $layerResolver;

        parent::__construct($context, $data);
    }

    /**  @return Item[] */
    public function getFilterItems(): array
    {
        if (!$this->configProvider->isEnabled()) {
            return [];
        }

        return $this->predictService->getFilterItems();
    }

    public function isSelected(Item $item): bool
    {
        foreach ($this->layerResolver->get()->getState()->getFilters() as $filterItem) {
            $values = explode(',', (string)$filterItem->getValueString());

            if ($filterItem->getName() == $item->getName()
                && ($item->getValueString() === $filterItem->getValueString()
                    || in_array($item->getValueString(), $values))) {
                return true;
            }
        }

        return false;
    }
}
