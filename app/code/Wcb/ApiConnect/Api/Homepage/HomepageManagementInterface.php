<?php
declare(strict_types=1);

namespace Wcb\ApiConnect\Api\Homepage;

use Magento\Framework\Controller\Result\Json;

interface HomepageManagementInterface
{
    /**
     * @return mixed
     */
    public function getHomePageInfo();
}
