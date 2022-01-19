<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/

declare(strict_types=1);

namespace Amasty\Base\Block\Adminhtml\System\Config\InformationBlocks;

use Amasty\Base\Block\Adminhtml\System\Config\Information;
use Amasty\Base\Model\ModuleInfoProvider;
use Magento\Framework\View\Element\Template;

class FeatureRequest extends Template
{
    const FEATURE_LINK = 'https://products.amasty.com/request-a-feature';
    const CAMPAIGN_NAME = 'request_a_feature';

    /**
     * @var string
     */
    protected $_template = 'Amasty_Base::config/information/feature_request.phtml';

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        Template\Context $context,
        ModuleInfoProvider $moduleInfoProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    public function getFeatureRequestLink(): string
    {
        return self::FEATURE_LINK . Information::SEO_PARAMS . self::CAMPAIGN_NAME;
    }

    public function isOriginMarketplace(): bool
    {
        return $this->moduleInfoProvider->isOriginMarketplace();
    }
}
