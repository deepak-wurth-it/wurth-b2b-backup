<?php

namespace Amasty\SalesRuleWizard\Model;

use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Framework\App\Request\DataPersistorInterface;

class RuleDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var OptionsProvider\CustomerGroup
     */
    private $customerGroupOptions;

    /**
     * @var \Magento\CatalogRule\Model\Rule\WebsitesOptionsProvider
     */
    private $websitesOptions;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Amasty\SalesRuleWizard\Model\OptionsProvider\CustomerGroup $customerGroupOptions,
        \Magento\CatalogRule\Model\Rule\WebsitesOptionsProvider $websitesOptions,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->customerGroupOptions = $customerGroupOptions;
        $this->websitesOptions = $websitesOptions;
    }


    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $websites = [];
        foreach ($this->websitesOptions->toOptionArray() as $option) {
            $websites[] = $option['value'];
        }
        $this->loadedData[null] = [
            'customer_group_ids' => $this->customerGroupOptions->getValues(),
            'website_ids' => $websites,
            'free_gifts' => ['products' => []]
        ];

        $items = $this->collection->getItems();
        /** @var Rule $rule */
        foreach ($items as $rule) {
//            $rule->load($rule->getId());
            $rule->setDiscountAmount($rule->getDiscountAmount() * 1);
            $rule->setDiscountQty($rule->getDiscountQty() * 1);

            $this->loadedData[$rule->getId()] = $rule->getData();
        }

        if ($savedData = $this->dataPersistor->get('wizard_rule')) {
            /** @var Rule $model */
            $model = $this->collection->getNewEmptyItem();
            $model->setData($savedData);
            $this->loadedData[$model->getId()] = $model->getData();
        }

        return $this->loadedData;
    }
}
