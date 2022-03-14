<?php

namespace Wurth\Landingpage\Ui\Component\Listing\Column;

use Magento\Cms\Model\PageFactory;

class Cmspage extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $pageFactory;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        PageFactory $pageFactory,
        array $components = [],
        array $data = []
    ) {
        $this->pageFactory = $pageFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $page = $this->pageFactory->create()->load($item['cms_page']);
                if ($page->getId()) {
                    $item['cms_page'] = $page->getTitle();
                }
            }
        }
        return $dataSource;
    }
}
