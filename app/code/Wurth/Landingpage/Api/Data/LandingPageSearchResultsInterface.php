<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wurth\Landingpage\Api\Data;

interface LandingPageSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get landing_page list.
     * @return \Wurth\Landingpage\Api\Data\LandingPageInterface[]
     */
    public function getItems();

    /**
     * Set cms_page list.
     * @param \Wurth\Landingpage\Api\Data\LandingPageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
