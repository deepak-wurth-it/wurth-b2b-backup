<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wurth\Landingpage\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LandingPage extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('landing_page', 'landing_page_id');
    }
}
