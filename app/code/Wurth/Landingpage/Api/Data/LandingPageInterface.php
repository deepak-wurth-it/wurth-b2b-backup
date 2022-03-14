<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wurth\Landingpage\Api\Data;

interface LandingPageInterface
{

    const CMS_PAGE = 'cms_page';
    const TITLE = 'title';
    const PRODUCT = 'product';
    const LANDING_PAGE_ID = 'landing_page_id';

    /**
     * Get landing_page_id
     * @return string|null
     */
    public function getLandingPageId();

    /**
     * Set landing_page_id
     * @param string $landingPageId
     * @return \Wurth\Landingpage\LandingPage\Api\Data\LandingPageInterface
     */
    public function setLandingPageId($landingPageId);

    /**
     * Get cms_page
     * @return string|null
     */
    public function getCmsPage();

    /**
     * Set cms_page
     * @param string $cmsPage
     * @return \Wurth\Landingpage\LandingPage\Api\Data\LandingPageInterface
     */
    public function setCmsPage($cmsPage);

    /**
     * Get product
     * @return string|null
     */
    public function getProduct();

    /**
     * Set product
     * @param string $product
     * @return \Wurth\Landingpage\LandingPage\Api\Data\LandingPageInterface
     */
    public function setProduct($product);

    /**
     * Get title
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     * @param string $title
     * @return \Wurth\Landingpage\LandingPage\Api\Data\LandingPageInterface
     */
    public function setTitle($title);
}
