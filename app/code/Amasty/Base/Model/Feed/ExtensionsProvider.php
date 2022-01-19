<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Model\Feed;

class ExtensionsProvider
{
    protected $modulesData = null;

    /**
     * @var FeedTypes\Extensions
     */
    private $extensionsFeed;

    public function __construct(
        FeedTypes\Extensions $extensionsFeed
    ) {
        $this->extensionsFeed = $extensionsFeed;
    }

    /**
     * @return array
     */
    public function getAllFeedExtensions()
    {
        if ($this->modulesData === null) {
            $this->modulesData = $this->extensionsFeed->execute();
        }

        return $this->modulesData;
    }

    /**
     * @param string $moduleCode
     *
     * @return array
     */
    public function getFeedModuleData($moduleCode)
    {
        $allModules = $this->getAllFeedExtensions();
        $moduleData = [];

        if ($allModules && isset($allModules[$moduleCode])) {
            $module = $allModules[$moduleCode];
            if ($module && is_array($module)) {
                $module = array_shift($module);
            }
            $moduleData = $module;
        }

        return $moduleData;
    }
}
