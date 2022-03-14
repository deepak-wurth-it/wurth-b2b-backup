<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wurth\Landingpage\Model;

use Magento\Framework\Model\AbstractModel;
use Wurth\Landingpage\Api\Data\LandingPageInterface;

class LandingPage extends AbstractModel implements LandingPageInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Wurth\Landingpage\Model\ResourceModel\LandingPage::class);
    }

    /**
     * @inheritDoc
     */
    public function getLandingPageId()
    {
        return $this->getData(self::LANDING_PAGE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setLandingPageId($landingPageId)
    {
        return $this->setData(self::LANDING_PAGE_ID, $landingPageId);
    }

    /**
     * @inheritDoc
     */
    public function getCmsPage()
    {
        return $this->getData(self::CMS_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setCmsPage($cmsPage)
    {
        return $this->setData(self::CMS_PAGE, $cmsPage);
    }

    /**
     * @inheritDoc
     */
    public function getProduct()
    {
        return $this->getData(self::PRODUCT);
    }

    /**
     * @inheritDoc
     */
    public function setProduct($product)
    {
        return $this->setData(self::PRODUCT, $product);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }
}
