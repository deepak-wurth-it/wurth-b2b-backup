<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/

declare(strict_types=1);

namespace Amasty\Base\Cron;

use Amasty\Base\Model\Feed\FeedTypes\Ads;
use Amasty\Base\Model\Feed\FeedTypes\Extensions;

class RefreshFeedData
{
    /**
     * @var Ads
     */
    private $adsFeed;

    /**
     * @var Extensions
     */
    private $extensionsFeed;

    public function __construct(
        Ads $adsFeed,
        Extensions $extensionsFeed
    ) {
        $this->adsFeed = $adsFeed;
        $this->extensionsFeed = $extensionsFeed;
    }

    /**
     * Force reload feeds data
     */
    public function execute()
    {
        $this->extensionsFeed->getFeed();
        $this->adsFeed->getFeed();
    }
}
