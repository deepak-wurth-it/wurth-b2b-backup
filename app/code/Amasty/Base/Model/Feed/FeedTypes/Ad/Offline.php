<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Model\Feed\FeedTypes\Ad;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;

/**
 * Provides saved ads data.
 * Should not throw any exception.
 */
class Offline
{
    const OFFLINE_ADS_FILENAME = 'offline_ads.json';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ModuleDirReader
     */
    private $moduleReader;

    public function __construct(
        Filesystem $filesystem,
        ModuleDirReader $moduleReader
    ) {
        $this->filesystem = $filesystem;
        $this->moduleReader = $moduleReader;
    }

    /**
     * @param bool $market
     *
     * @return array
     */
    public function getOfflineData($market = false)
    {
        /** @var string $etcDir */
        $etcDirPath = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
            'Amasty_Base'
        );

        $dir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $fileName = $dir->getRelativePath($etcDirPath . '/' . static::OFFLINE_ADS_FILENAME);

        if (!$dir->isExist($fileName)) {
            return [];
        }

        try {
            $content = $dir->readFile($fileName);
        } catch (\Magento\Framework\Exception\FileSystemException $exception) {
            return [];
        }

        // phpcs:disable - Magento functional or Zend functions always throw exception
        $data = json_decode($content, true) ?: [];
        //phpcs:enable

        foreach ($data as &$row) {
            if (isset($row['text_market'])) {
                if ($market) {
                    $row['text'] = $row['text_market'];
                }

                unset($row['text_market']);
            }
        }

        return $data;
    }
}
