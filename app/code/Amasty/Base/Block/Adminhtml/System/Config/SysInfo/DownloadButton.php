<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


declare(strict_types=1);

namespace Amasty\Base\Block\Adminhtml\System\Config\SysInfo;

use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;

class DownloadButton extends Field
{
    const ELEMENT_ID = 'download_info';
    protected const ACTION_URL = 'ambase/sysInfo/download';

    protected function _toHtml()
    {
        $button = $this->getLayout()->createBlock(Button::class)
            ->setData([
                'id' => self::ELEMENT_ID,
                'label' => __('Download'),
                'onclick' => $this->getOnClickAction()
            ]);

        return $button->toHtml();
    }

    private function getOnClickAction(): string
    {
        return sprintf(
            "location.href = '%s'",
            $this->_urlBuilder->getUrl(self::ACTION_URL)
        );
    }
}
