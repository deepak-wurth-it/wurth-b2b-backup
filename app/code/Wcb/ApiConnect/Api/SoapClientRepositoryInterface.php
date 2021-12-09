<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface SoapClientRepositoryInterface
{

    /**
     * Save SoapClient
     * @param \Wcb\ApiConnect\Api\Data\SoapClientInterface $soapClient
     * @return \Wcb\ApiConnect\Api\Data\SoapClientInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Wcb\ApiConnect\Api\Data\SoapClientInterface $soapClient
    );

    /**
     * Retrieve SoapClient
     * @param string $soapclientId
     * @return \Wcb\ApiConnect\Api\Data\SoapClientInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($soapclientId);

    /**
     * Retrieve SoapClient matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Wcb\ApiConnect\Api\Data\SoapClientSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete SoapClient
     * @param \Wcb\ApiConnect\Api\Data\SoapClientInterface $soapClient
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Wcb\ApiConnect\Api\Data\SoapClientInterface $soapClient
    );

    /**
     * Delete SoapClient by ID
     * @param string $soapclientId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($soapclientId);
}

