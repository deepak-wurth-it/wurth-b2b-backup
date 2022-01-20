<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Plugin\Config\Block\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field as NativeField;

class Field
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->assetRepo = $assetRepo;
    }

    /**
     * @param NativeField $field
     * @param string $html
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function afterRender(
        NativeField $field,
        $html,
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        if (strpos($html, 'tooltip-content') !== false) {
            $html = $this->replaceString($html);
        }

        $elementTooltip = $element->getTooltip();
        if ($elementTooltip) {
            $elementTooltip = $this->replaceString($elementTooltip);
            $element->setTooltip($elementTooltip);
        }

        return $html;
    }

    /**
     * @param string $content
     * @return string
     */
    private function replaceString(string $content): string
    {
        preg_match('/<img.*?src="(Amasty.*?)"/', $content, $result);
        if (count($result) >=2) {
            $path = $result[1];
            $newPath = $this->assetRepo->getUrl($path);
            if ($newPath) {
                $content = str_replace($path, $newPath, $content);
            }
        }

        return $content;
    }
}
