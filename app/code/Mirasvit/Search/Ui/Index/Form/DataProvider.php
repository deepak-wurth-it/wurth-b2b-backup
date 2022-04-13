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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Search\Ui\Index\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Repository\IndexRepository;

class DataProvider extends AbstractDataProvider
{
    private $indexRepository;

    private $uiComponentFactory;

    private $context;

    public function __construct(
        IndexRepository $indexRepository,
        UiComponentFactory $uiComponentFactory,
        ContextInterface $context,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->context            = $context;
        $this->indexRepository    = $indexRepository;
        $this->collection         = $this->indexRepository->getCollection();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta(): array
    {
        $id = (int)$this->context->getRequestParam($this->getRequestFieldName());

        if ($id) {
            $index    = $this->indexRepository->get($id);
            $instance = $this->indexRepository->getInstance($index);

            $identifier = 'search_index_form_' . $instance->getType();

            if (in_array($identifier, [
                'search_index_form_catalogsearch_fulltext',
                'search_index_form_magento_catalog_attribute',
                'search_index_form_magento_cms_page',
                'search_index_form_external_wordpress_post',
                'search_index_form_blackbird_contentmanager_content',
            ])) {
                $component = $this->uiComponentFactory->create($identifier);

                return ['props' => $this->prepareComponent($component)];
            }
        }

        return parent::getMeta();
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $result = [];

        foreach ($this->indexRepository->getCollection() as $index) {
            $instance   = $this->indexRepository->getInstance($index);
            $attributes = $instance->getAttributeWeights();

            if (count($attributes) == 0) {
                $attributes['empty'] = 1; // for correct js type casting
            }

            $properties = $index->getProperties();
            if (count($properties) == 0) {
                $properties['empty'] = 1; // for correct js type casting
            }

            $data = [
                IndexInterface::ID         => $index->getId(),
                IndexInterface::TITLE      => $index->getTitle(),
                IndexInterface::IDENTIFIER => $index->getIdentifier(),
                IndexInterface::IS_ACTIVE  => $index->getIsActive() ? '1' : '0',
                IndexInterface::POSITION   => $index->getPosition(),
                'attributes'               => $attributes,
                'properties'               => $properties,
            ];

            $result[$index->getId()] = $data;
        }

        return $result;
    }

    protected function prepareComponent(UiComponentInterface $component): array
    {
        $data = [];
        foreach ($component->getChildComponents() as $child) {
            $data['children'][] = $this->prepareComponent($child);
        }

        $component->prepare();
        $data['arguments']['data'] = $component->getData();
        unset($data['arguments']['data']['options']);

        return $data;
    }
}
