<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wurth\Landingpage\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface LandingPageRepositoryInterface
{

    /**
     * Save landing_page
     * @param \Wurth\Landingpage\Api\Data\LandingPageInterface $landingPage
     * @return \Wurth\Landingpage\Api\Data\LandingPageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Wurth\Landingpage\Api\Data\LandingPageInterface $landingPage
    );

    /**
     * Retrieve landing_page
     * @param string $landingPageId
     * @return \Wurth\Landingpage\Api\Data\LandingPageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($landingPageId);

    /**
     * Retrieve landing_page matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Wurth\Landingpage\Api\Data\LandingPageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete landing_page
     * @param \Wurth\Landingpage\Api\Data\LandingPageInterface $landingPage
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Wurth\Landingpage\Api\Data\LandingPageInterface $landingPage
    );

    /**
     * Delete landing_page by ID
     * @param string $landingPageId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($landingPageId);
}
