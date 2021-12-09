<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Api\Data;

interface SoapClientSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get SoapClient list.
     * @return \Wcb\ApiConnect\Api\Data\SoapClientInterface[]
     */
    public function getItems();

    /**
     * Set content list.
     * @param \Wcb\ApiConnect\Api\Data\SoapClientInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

