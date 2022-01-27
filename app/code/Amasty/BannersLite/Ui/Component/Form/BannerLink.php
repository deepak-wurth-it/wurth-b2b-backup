<?php

namespace Amasty\BannersLite\Ui\Component\Form;

use Amasty\Base\Helper\Module;
use Magento\Ui\Component\Form\Field;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Module\Manager;
use Magento\Framework\UrlInterface;

class BannerLink extends Field
{
    const MODULE_NAME = 'Amasty_PromoBanners';

    const PROMO_BANNERS_GUIDE_URL = 'https://amasty.com/promo-banners-for-magento-2.html'
    . '?utm_source=extension&utm_medium=link&utm_campaign=sp-pbanners-m2';
    const MARKETPLACE_URL = 'https://marketplace.magento.com/amasty-module-promo-banners.html';

    const PROMO_BANNERS_URL = 'ampromobanners/banners/index';

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Module
     */
    private $moduleHelper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Manager $manager,
        Module $moduleHelper,
        $components,
        array $data = []
    ) {
        $this->manager = $manager;
        $this->urlBuilder = $urlBuilder;
        $this->moduleHelper = $moduleHelper;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare()
    {
        $config = $this->getData('config');

        if ($this->manager->isEnabled(self::MODULE_NAME)) {
            $url = $this->urlBuilder->getUrl(self::PROMO_BANNERS_URL);
            $config['additionalInfo'] = 'Banner will appear on product pages only.'
                . ' To highlight your promotions more effectively, use our extension Promo Banners for Magento 2'
                . ' which allows to display banners on cart page, category pages, etc. '
                . "<a href= ". $url ." target='_blank'>Configure Promo Banners</a>";
        } else {
            $config['additionalInfo'] = 'Banner will appear on product pages only.'
                . ' To highlight your promotions more effectively, consider installing our extension'
                . ' Promo Banners for Magento 2 which allows to display banners on cart page, category pages, etc. '
                . "<a href= ". self::MARKETPLACE_URL ." target='_blank'>See more details here</a>";
        }

        $this->setData('config', $config);

        parent::prepare();
    }
}
