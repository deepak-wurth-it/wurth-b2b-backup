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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\QuickNavigation\Model\ResourceModel\Sequence;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mirasvit\QuickNavigation\Api\Data\SequenceInterface;

class Collection extends AbstractCollection
{
    protected $_idFieldName = SequenceInterface::ID;

    protected function _construct()
    {
        $this->_init(
            \Mirasvit\QuickNavigation\Model\Sequence::class,
            \Mirasvit\QuickNavigation\Model\ResourceModel\Sequence::class
        );
    }
}
