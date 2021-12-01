<?php

namespace Wcb\BestSeller\Block\Adminhtml\Slider\Edit\Tab\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\View\LayoutInterface;

/**
 * Class Responsive
 * @package Wcb\BestSeller\Block\Adminhtml\Slider\Edit\Tab\Renderer
 */
class Responsive extends AbstractElement
{
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * Responsive constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param LayoutInterface $layout
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        LayoutInterface $layout,
        array $data = []
    ) {
        $this->layout = $layout;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = '<div id="' . $this->getHtmlId() . '">';
        $html .= $this->layout->createBlock(\Wcb\BestSeller\Block\Adminhtml\Config\Field\Responsive::class)
            ->setElement($this)
            ->toHtml();
        $html .= '</div>';

        return $html;
    }
}
