<?php

namespace Wcb\BestSeller\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Wcb\BestSeller\Model\Config\Source\ProductType;

/**
 * Class CommentContent
 * @package Mageplaza\Blog\Ui\Component\Listing\Columns
 */
class SliderType extends Column
{
    /**
     * @var ProductType
     */
    protected $productType;

    /**
     * SliderType constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ProductType $productType
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductType $productType,
        array $components = [],
        array $data = []
    ) {
        $this->productType = $productType;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $productType = $this->productType->getLabel($item[$this->getData('name')]);

                    $item[$this->getData('name')] = '<span>' . $productType . '</span>';
                }
            }
        }

        return $dataSource;
    }
}
