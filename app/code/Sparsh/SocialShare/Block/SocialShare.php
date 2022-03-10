<?php
/**
 * Class SocialShare
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SocialShare\Block;

use Magento\Cms\Block\Page;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Sparsh\SocialShare\Helper\Data as HelperData;
use Sparsh\SocialShare\Model\System\ButtonSize;
use Sparsh\SocialShare\Model\System\DisplayMenuType;
use Sparsh\SocialShare\Model\System\FloatApply;
use Sparsh\SocialShare\Model\System\FloatPosition;
use Sparsh\SocialShare\Model\System\InlinePosition;
use Sparsh\SocialShare\Model\System\Style;

/**
 * Class SocialShare
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class SocialShare extends Template
{
    const SERVICES        = [
        'socialoption/facebook',
        'socialoption/twitter',
        'socialoption/google_gmail',
        'socialoption/pinterest',
        'socialoption/linkedin',
        'socialoption/reddit',
        'socialoption/whatsapp'
    ];
    const CLICK_FULL_MENU = '2';
    const CLICK_MENU      = '1';
    const HOVER_MENU      = '0';

    /**
     * Helper
     *
     * @var HelperData
     */
    private $helperData;

    /**
     * Cms page block
     *
     * @var \Magento\Cms\Block\Page
     */
    private $page;

    /**
     * SocialShare constructor.
     *
     * @param Context    $context
     * @param HelperData $helperData
     * @param Page       $page
     * @param array      $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        Page $page,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->page = $page;
        parent::__construct($context, $data);
    }

    /**
     * Is social share enabled ot not
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->helperData->isEnabled();
    }

    /**
     * Check if icon's color schema is set to custom or default
     *
     * @return bool
     */
    public function isCustomColorIcon()
    {
        return $this->helperData->isCustomColorIcon();
    }

    /**
     * Return icon color
     *
     * @return mixed
     */
    public function getIconColor()
    {
        return $this->helperData->getIconColor();
    }

    /**
     * Return Button color
     *
     * @return mixed
     */
    public function getButtonColor()
    {
        return $this->helperData->getButtonColor();
    }

    /**
     * Return background color
     *
     * @return mixed
     */
    public function getBackgroundColor()
    {
        $color = $this->helperData->getBackgroundColor();

        return "background: " . $color . ";";
    }

    /**
     * Return border radius
     *
     * @return mixed
     */
    public function getBorderRadius()
    {
        return $this->helperData->getBorderRadius() . "%";
    }

    /**
     * Return is add more share
     *
     * @return bool
     */
    public function isAddMoreShare()
    {
        return $this->helperData->isAddMoreShare();
    }

    /**
     * Return share counter
     *
     * @return string
     */
    public function getShareCounter()
    {
        if ($this->helperData->isShareCounter()) {
            return "a2a_counter";
        }

        return "";
    }

    /**
     * Return thank you popup enabled or not
     *
     * @return string
     */
    public function isThankYou()
    {
        if ($this->helperData->isThankYouPopup()) {
            return "true";
        }

        return "false";
    }

    /**
     * Return enabled services
     *
     * @return array
     */
    public function getEnableService()
    {
        $enableServices = [];
        foreach (self::SERVICES as $service) {
            if ($this->helperData->isServiceEnable($service)) {
                array_push($enableServices, explode('/', $service)[1]);
            }
        }

        return $enableServices;
    }

    /**
     * Return disabled services
     *
     * @return string
     */
    public function getDisabledServices()
    {
        $disabledServices = [];
        foreach (self::SERVICES as $service) {
            if ($this->helperData->getDisableService($service) != null) {
                array_push($disabledServices, explode('/', $this->helperData->getDisableService($service))[1]);
            }
        }

        return implode(",", $disabledServices);
    }

    /**
     * Return no. of services to display on click or hover of share more
     *
     * @return array|mixed
     */
    public function getNumberOfService()
    {
        return $this->helperData->getNumberOfServices();
    }

    /**
     * Return menu type
     *
     * @return string
     */
    public function getMenuType()
    {
        $menuType = $this->helperData->getDisplayMenuType();
        if ($menuType == DisplayMenuType::ON_CLICK) {
            if ($this->helperData->isFullMenuOnClick()) {
                return self::CLICK_FULL_MENU;
            }

            return self::CLICK_MENU;
        }

        return self::HOVER_MENU;
    }

    /**
     * Return Display type
     *
     * @return string
     */
    public function getDisplayType()
    {
        $type = $this->getData('type');
        if ($type == 'float') {
            return 'a2a_floating_style sp_social_share_widget';
        }
        if ($type == 'inline') {
            return 'a2a_default_style';
        }

        return null;
    }

    /**
     * Is inline widget
     *
     * @return bool
     */
    public function isDisplayInline()
    {
        $type = $this->getData('type');

        return $type == 'inline';
    }

    /**
     * Return container class
     *
     * @param string $displayType
     *
     * @return string|null
     */
    public function getContainerClass($displayType)
    {
        $position = $this->getData('position');
        if ($displayType == 'a2a_default_style') {
            if ($position == 'under_cart') {
                return "sp_social_share_inline_widget_widget_under_cart";
            }

            return "sp_social_share_inline_widget";
        }

        return null;
    }

    /**
     * Return current page is enabled ot not
     *
     * @return bool
     */
    public function isThisPageEnable()
    {
        $type = $this->getData('type');
        $thisPage = $this->getData('page');
        $allowPages = null;

        if ($type == 'inline') {
            $allowPages = explode(',', $this->helperData->getInlineApplyPages());
            if ($this->getShowUnderCart()) {
                array_push($allowPages, "under_cart");
            }
            if (in_array($thisPage, $allowPages)) {
                return true;
            }
        }
        if ($type == 'float') {
            if ($this->helperData->getFloatApplyPages() == FloatApply::ALL_PAGES) {
                return true;
            }
            if ($this->helperData->getFloatApplyPages() == FloatApply::SELECT_PAGES) {
                $selectPages = explode(',', $this->helperData->getFloatPages());
                $cmsPages = explode(',', $this->helperData->getCmsPages());
                if ($thisPage == "cms_page") {
                    $pageId = $this->page->getPage()->getId();
                    if (in_array($pageId, $cmsPages)) {
                        return true;
                    }
                } elseif (in_array($thisPage, $selectPages)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return show under add-to-cart or not
     *
     * @return bool
     */
    public function getShowUnderCart()
    {
        return $this->helperData->getShowUnderCart();
    }

    /**
     * Return position is enabled or not
     *
     * @return bool
     */
    public function isThisPositionEnable()
    {
        $thisPosition = $this->getData('position');
        $positionArray = [];
        if ($thisPosition == "float_position") {
            return true;
        }
        $selectPosition = $this->helperData->getInlinePosition();
        array_push($positionArray, $selectPosition);
        if ($this->getShowUnderCart()) {
            array_push($positionArray, "under_cart");
        }
        if (in_array($thisPosition, $positionArray)) {
            return true;
        }

        return false;
    }

    /**
     * Return button size
     *
     * @return string
     */
    public function getButtonSize()
    {
        $type = $this->getData('type');
        if ($type == 'inline') {
            $inlineSize = $this->helperData->getInlineButtonSize();

            return $this->setButtonSize($inlineSize);
        }
        $floatSize = $this->helperData->getButtonSize();

        return $this->setButtonSize($floatSize);
    }

    /**
     * Get button size
     *
     * @param string $buttonSize
     *
     * @return string
     */
    public function setButtonSize($buttonSize)
    {
        switch ($buttonSize) {
            case ButtonSize::SMALL:
                return "a2a_kit_size_16";
            case ButtonSize::MEDIUM:
                return "a2a_kit_size_32";
            case ButtonSize::LARGE:
                return "a2a_kit_size_64";
            default:
                return "a2a_kit_size_32";
        }
    }

    /**
     * Return style
     *
     * @return string
     */
    public function getStyle()
    {
        if (!$this->isDisplayInline()) {
            $floatStyle = $this->helperData->getStyle();
            if ($floatStyle == Style::VERTICAL) {
                return "a2a_vertical_style";
            }

            return "a2a_default_style";
        }

        return null;
    }

    /**
     * Return is style vertical or not
     *
     * @param $floatStyle
     *
     * @return bool
     */
    public function isVerticalStyle($floatStyle)
    {
        return $floatStyle == "a2a_vertical_style";
    }

    /**
     * Return position
     *
     * @return string
     */
    public function getPosition()
    {
        $floatPosition = $this->helperData->getPosition();

        if ($floatPosition == FloatPosition::LEFT) {
            return "left: 0px;";
        }

        return "right: 0px;";
    }

    /**
     * Return inline position
     *
     * @return string
     */
    public function getInlinePosition()
    {

        $floatPosition = $this->helperData->getInlinePosition();
        if ($floatPosition == InlinePosition::NONE && $this->getData('type') =='inline') {
            return "display: none;";
        }

        return "";
    }
    /**
     * Return margin
     *
     * @param $type
     *
     * @return string
     */
    public function getFloatMargin($type)
    {
        if ($type == "bottom") {
            $floatMarginBottom = $this->helperData->getMarginBottom();

            return "bottom: " . $floatMarginBottom . "px;";
        }
        $floatMarginTop = $this->helperData->getMarginTop();

        return "top: " . $floatMarginTop . "px;";
    }
}
