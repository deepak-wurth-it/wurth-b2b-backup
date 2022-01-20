<?php

namespace Amasty\BannersLite\Api;

/**
 * @api
 */
interface BannerRuleRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\BannersLite\Api\Data\BannerRuleInterface $bannerRule
     *
     * @return \Amasty\BannersLite\Api\Data\BannerRuleInterface
     */
    public function save(\Amasty\BannersLite\Api\Data\BannerRuleInterface $bannerRule);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\BannersLite\Api\Data\BannerRuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\BannersLite\Api\Data\BannerRuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySalesruleId($entityId);

    /**
     * Delete
     *
     * @param \Amasty\BannersLite\Api\Data\BannerRuleInterface $bannerRule
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\BannersLite\Api\Data\BannerRuleInterface $bannerRule);

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
