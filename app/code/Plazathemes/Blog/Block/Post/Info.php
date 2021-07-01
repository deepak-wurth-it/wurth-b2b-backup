<?php
/**
 * Copyright © 2015 PlazaThemes.com. All rights reserved.

 * @author PlazaThemes Team <contact@plazathemes.com>
 */

namespace Plazathemes\Blog\Block\Post;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog post info block
 */
class Info extends \Magento\Framework\View\Element\Template
{
	/**
     * Block template file
     * @var string
     */
    protected $_template = 'post/info.phtml';

    /**
     * Retrieve formated posted date
     * @var string
     * @return string
     */
    public function getPostedOn($format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($this->getPost()->getData('publish_time')));
    }

}
