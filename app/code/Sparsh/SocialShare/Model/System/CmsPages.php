<?php
/**
 * Class CmsPages
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SocialShare\Model\System;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageFactory;

/**
 * Class CmsPages
 *
 * @category Sparsh
 * @package  Sparsh_SocialShare
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class CmsPages extends OptionArray
{
    /**
     * PageFactory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * CmsPages constructor.
     *
     * @param PageFactory $pageFactory
     */
    public function __construct(PageFactory $pageFactory)
    {
        $this->_pageFactory = $pageFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptionHash()
    {
        $pages = $this->_pageFactory->create();
        $cmsPages = [];

        foreach ($pages as $page) {
            $cmsPages[$page->getId()] = $page->getTitle();
        }

        return $cmsPages;
    }
}
