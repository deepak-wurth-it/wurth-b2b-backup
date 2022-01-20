<?php

namespace Amasty\SalesRuleWizard\Model\OptionsProvider;

class CustomerGroup implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array|null
     */
    protected $options;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    private $objectConverter;

    /**
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     */
    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            $this->options = $this->objectConverter->toOptionArray($customerGroups, 'id', 'code');
        }

        return $this->options;
    }

    public function getValues()
    {
        $values = [];
        foreach ($this->toOptionArray() as $option) {
            $values[] = $option['value'];
        }

        return $values;
    }
}
