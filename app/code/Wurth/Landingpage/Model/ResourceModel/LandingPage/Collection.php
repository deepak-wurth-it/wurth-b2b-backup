<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wurth\Landingpage\Model\ResourceModel\LandingPage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'landing_page_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Wurth\Landingpage\Model\LandingPage::class,
            \Wurth\Landingpage\Model\ResourceModel\LandingPage::class
        );
    }
}
