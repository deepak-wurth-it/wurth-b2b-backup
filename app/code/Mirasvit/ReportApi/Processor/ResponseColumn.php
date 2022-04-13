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
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Processor;

use Magento\Framework\Api\AbstractSimpleObject;
use Mirasvit\ReportApi\Api\Processor\ResponseColumnInterface;

class ResponseColumn extends AbstractSimpleObject implements ResponseColumnInterface
{
    const NAME  = 'name';
    const LABEL = 'label';
    const TYPE  = 'type';

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::NAME  => $this->getName(),
            self::LABEL => $this->getLabel(),
            self::TYPE  => $this->getType(),
        ];
    }

    /**
     * @return mixed|string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * @return mixed|string|null
     */
    public function getLabel()
    {
        return $this->_get(self::LABEL);
    }

    /**
     * @return mixed|string|null
     */
    public function getType()
    {
        return $this->_get(self::TYPE);
    }
}
