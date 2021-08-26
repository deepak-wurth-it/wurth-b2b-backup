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

namespace Mirasvit\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\View\Element\Template\Context;
use Magento\LayeredNavigation\Block\Navigation\State as NavigationState;
use Mirasvit\LayeredNavigation\Model\Config\HorizontalBarConfigProvider;
use Mirasvit\LayeredNavigation\Model\Config\StateBarConfigProvider;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;

/**
 * di.preference @see \Magento\LayeredNavigation\Block\Navigation\State
 */
class State extends NavigationState
{
    /** @var string */
    protected $_template = 'navigation/state.phtml';

    private   $configProvider;

    private   $stateBarConfigProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Context $context,
        LayerResolver $layerResolver,
        StateBarConfigProvider $stateBarConfigProvider,
        array $data = []
    ) {
        $this->configProvider         = $configProvider;
        $this->stateBarConfigProvider = $stateBarConfigProvider;

        parent::__construct($context, $layerResolver, $data);
    }

    /** @return Item[] */
    public function getActiveFilters(): array
    {
        $nameInLayout = $this->getNameInLayout();

        if ($this->stateBarConfigProvider->isHidden()) {
            return [];
        }

        if (($nameInLayout == HorizontalBarConfigProvider::STATE_HORIZONTAL_BLOCK_NAME)
            && !$this->stateBarConfigProvider->isHorizontalPosition()) {
            return [];
        }

        if (($nameInLayout == HorizontalBarConfigProvider::STATE_BLOCK_NAME
                || $nameInLayout == HorizontalBarConfigProvider::STATE_SEARCH_BLOCK_NAME)
            && $this->stateBarConfigProvider->isHorizontalPosition()) {
            return [];
        }

        $filters = $this->getLayer()->getState()->getFilters();

        if (!is_array($filters)) {
            $filters = [];
        }

        return $filters;
    }

    public function isAjaxEnabled(): bool
    {
        return $this->configProvider->isAjaxEnabled();
    }

    public function isHorizontalFilter(): bool
    {
        $nameInLayout = $this->getNameInLayout();
        if ($nameInLayout == HorizontalBarConfigProvider::STATE_HORIZONTAL_BLOCK_NAME) {
            return true;
        }

        return false;
    }
}
