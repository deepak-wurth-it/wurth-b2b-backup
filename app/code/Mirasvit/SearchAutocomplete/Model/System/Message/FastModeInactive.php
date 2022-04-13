<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SearchAutocomplete\Model\System\Message;

use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Notification\MessageInterface;
use Mirasvit\SearchAutocomplete\Model\ConfigProvider;

class FastModeInactive implements MessageInterface
{
    private $backendHelper;

    private $directoryList;

    private $configProvider;

    public function __construct(
        BackendHelper $backendHelper,
        DirectoryList $directoryList,
        ConfigProvider $configProvider
    ) {
        $this->backendHelper  = $backendHelper;
        $this->directoryList  = $directoryList;
        $this->configProvider = $configProvider;
    }

    public function getIdentity(): string
    {
        return hash('sha256', get_class($this));
    }

    public function isDisplayed(): bool
    {
        return $this->configProvider->getSearchEngine() === 'mysql2' && $this->configProvider->isFastModeEnabled();
    }

    /**
     * Retrieve message text
     * @return string
     */
    public function getText()
    {
        return __("Mirasvit Search Autocomplete Fast Mode doesn't support MySQL search engine.");
    }

    /**
     * Retrieve problem management url
     * @return string|null
     */
    public function getLink()
    {
        return $this->backendHelper->getUrl('admin/system_config/edit/section/searchautocomplete', []);
    }

    /**
     * Retrieve message severity
     * @return int
     */
    public function getSeverity()
    {
        return MessageInterface::SEVERITY_CRITICAL;
    }

    /**
     * Get array of cache types which require data refresh
     * @return bool
     */
    protected function isConfigExists()
    {
        return file_exists($this->directoryList->getRoot() . '/app/etc/instant.json');
    }
}
