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
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Plugin\Backend\Model\Menu\Item;

use Magento\Backend\Model\Menu\Item;
use Magento\Framework\DataObject;
use Mirasvit\Core\Block\Adminhtml\Menu as MenuBlock;
use Mirasvit\Core\Service\CompatibilityService;

class DirectUrlPlugin
{
    /**
     * @var MenuBlock
     */
    private $menuBlock;

    /**
     * DirectUrlPlugin constructor.
     * @param MenuBlock $menuBlock
     */
    public function __construct(
        MenuBlock $menuBlock
    ) {
        $this->menuBlock = $menuBlock;
    }

    /**
     * @param Item $subject
     * @param string $url
     * @return mixed
     */
    public function afterGetUrl(Item $subject, $url)
    {
        if (CompatibilityService::is20()
            || CompatibilityService::is21()
        ) {
            return $url;
        }

        if ($url == '#') {
            $data = $subject->toArray();
            if ($data['path']) {
                $items = [];

                if (isset($data['module'])) {
                    $items = $this->menuBlock->getItemsByModuleName($data['module']);
                } elseif (isset($data['module_name'])) {
                    $items = $this->menuBlock->getItemsByModuleName($data['module_name']);
                }

                /** @var DataObject $item */
                foreach ($items as $item) {
                    if (!is_object($item)) {
                        continue;
                    }

                    if ($item->getData('title') == $data['title']) {
                        return $item->getData('url');
                    }
                }

                return $data['path'];
            }
        }

        return $url;
    }
}