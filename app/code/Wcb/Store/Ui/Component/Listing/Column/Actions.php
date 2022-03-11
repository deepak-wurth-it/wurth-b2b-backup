<?php
/**
 *
 * @category  Wcb
 * @package   Wcb_Store
 * @author    Deepak Kumar <deepak.kumar.rai@wuerth-it.com>
 * @copyright 2019 Wcb technologies (I) Pvt. Ltd
 */

namespace Wcb\Store\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

/**
 * Class PageActions
 */
class Actions extends Column
{
    /** Url path */
    const URL_PATH_EDIT = 'store/index/edit';
    const URL_PATH_DELETE = 'store/index/delete';

    /** @var UrlBuilder */
    protected $actionUrlBuilder;

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::URL_PATH_EDIT,
        $deleteUrl = self::URL_PATH_DELETE
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl = $editUrl;
        $this->deleteUrl = $deleteUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['entity_id' => $item['entity_id']]),
                        'label' => __('Edit')
                    ];

                      $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl( $this->deleteUrl, ['entity_id' => $item['entity_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'name' => __('Delete %1', $item['name']),
                            'message' => __('Are you sure you want to delete a %1 record?', $item['name'])
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
