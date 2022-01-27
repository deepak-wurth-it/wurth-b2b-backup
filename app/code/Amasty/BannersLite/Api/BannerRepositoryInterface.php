<?php

namespace Amasty\BannersLite\Api;

/**
 * @api
 */
interface BannerRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\BannersLite\Api\Data\BannerInterface $banner
     *
     * @return \Amasty\BannersLite\Api\Data\BannerInterface
     */
    public function save(\Amasty\BannersLite\Api\Data\BannerInterface $banner);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\BannersLite\Api\Data\BannerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Get by id
     *
     * @param int $ruleId
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySalesruleId($ruleId);

    /**
     * Get by id
     *
     * @param int $ruleId
     * @param int $bannerType
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByBannerType($ruleId, $bannerType);

    /**
     * Delete
     *
     * @param \Amasty\BannersLite\Api\Data\BannerInterface $banner
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\BannersLite\Api\Data\BannerInterface $banner);

    /**
     * Delete by id
     *
     * @param int $entityId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
