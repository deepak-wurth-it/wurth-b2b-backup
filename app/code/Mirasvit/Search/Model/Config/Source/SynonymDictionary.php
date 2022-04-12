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



namespace Mirasvit\Search\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Service\CloudService;

class SynonymDictionary implements ArrayInterface
{
    private $cloudService;

    private $configProvider;

    public function __construct(
        CloudService $cloudService,
        ConfigProvider $configProvider
    ) {
        $this->cloudService   = $cloudService;
        $this->configProvider = $configProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $files = array_merge(
            $this->cloudService->getList('search', 'synonym'),
            $this->getLocalFiles()
        );

        return $files;
    }

    /**
     * Synonym files
     * @return array
     */
    private function getLocalFiles()
    {
        $options = [];

        $path = $this->configProvider->getSynonymDirectoryPath();

        if (file_exists($path)) {
            $dh = opendir($path);
            while (false !== ($filename = readdir($dh))) {
                if (substr($filename, 0, 1) != '.') {
                    $info      = pathinfo($filename);
                    $options[] = [
                        'label' => $info['filename'],
                        'value' => $path . DIRECTORY_SEPARATOR . $filename,
                    ];
                }
            }
        }

        return $options;
    }
}
