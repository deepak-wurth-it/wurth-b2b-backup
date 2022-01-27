<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Plugin\Backend\Model\Menu;

use Amasty\Base\Model\Feed\ExtensionsProvider;
use Amasty\Base\Model\ModuleInfoProvider;
use Magento\Backend\Model\Menu\Item as NativeItem;

class Item
{
    const BASE_MARKETPLACE = 'Amasty_Base::marketplace';

    const SEO_PARAMS = '?utm_source=extension&utm_medium=backend&utm_campaign=main_menu_to_user_guide';

    const MARKET_URL = 'https://amasty.com/magento-2-extensions.html'
    . '?utm_source=extension&utm_medium=backend&utm_campaign=main_menu_to_catalog';

    const MAGENTO_MARKET_URL = 'https://marketplace.magento.com/partner/Amasty';

    /**
     * @var ExtensionsProvider
     */
    private $extensionsProvider;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        ExtensionsProvider $extensionsProvider,
        ModuleInfoProvider $moduleInfoProvider
    ) {
        $this->extensionsProvider = $extensionsProvider;
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * @param NativeItem $subject
     * @param $url
     *
     * @return string
     */
    public function afterGetUrl(NativeItem $subject, $url)
    {
        $id = $subject->getId();
        if ($id === self::BASE_MARKETPLACE) {
            $url = $this->moduleInfoProvider->isOriginMarketplace() ? self::MAGENTO_MARKET_URL : self::MARKET_URL;
        }

        /* we can't add guide link into item object - find link again */
        if (strpos($id, '::menuguide') !== false
            && strpos($id, 'Amasty') !== false
        ) {
            $moduleCode = explode('::', $subject->getId());
            $moduleCode = $moduleCode[0];
            $moduleInfo = $this->extensionsProvider->getFeedModuleData($moduleCode);
            if (isset($moduleInfo['guide']) && $moduleInfo['guide']) {
                $url = $moduleInfo['guide'];
                $seoLink = self::SEO_PARAMS;
                if (strpos($url, '?') !== false) {
                    $seoLink = str_replace('?', '&', $seoLink);
                }
                $url .= $seoLink;
            } else {
                $url = '';
            }
        }

        return $url;
    }
}
