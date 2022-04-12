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
 * @version   1.3.3
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Core\Ui\QuickDataBar;

use Magento\Backend\Block\Template;

abstract class AbstractDataBlock extends Template
{
    /** @var \DateTime */
    protected $dateFrom;

    /** @var \DateTime */
    protected $dateTo;

    public function setDateRange(\DateTime $from, \DateTime $to): AbstractDataBlock
    {
        $this->dateFrom = $from;
        $this->dateTo   = $to;

        return $this;
    }

    public abstract function getLabel(): string;

    public function toArray(array $keys = []): array
    {
        return [
            'label' => $this->getLabel(),
        ];
    }
}
