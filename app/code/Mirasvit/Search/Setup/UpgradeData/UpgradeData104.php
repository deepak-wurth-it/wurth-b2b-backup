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



namespace Mirasvit\Search\Setup\UpgradeData;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData104 implements UpgradeDataInterface
{
    private $moduleList;

    private $blockFactory;

    private $blockRepository;

    public function __construct(
        ModuleList $moduleList,
        BlockInterfaceFactory $blockFactory,
        BlockRepositoryInterface $blockRepository
    ) {
        $this->moduleList      = $moduleList;
        $this->blockFactory    = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        if ($this->moduleList->has('WeltPixel_CmsBlockScheduler')) {
            throw new \Exception('Please disable WeltPixel_CmsBlockScheduler before upgrade. And enable it after upgrade');
        }

        /** @var \Magento\Cms\Api\Data\BlockInterface $block */
        $block = $this->blockFactory->create();

        $block->setIdentifier('no-results')
            ->setTitle('Search: No Results Suggestions')
            ->setContent($this->getBlockContent('no_results'))
            ->setIsActive(true);

        try {
            $this->blockRepository->save($block);
        } catch (\Exception $e) {
        }
    }

    private function getBlockContent(string $name): string
    {
        return file_get_contents(dirname(__FILE__) . "/data/$name.html");
    }
}
