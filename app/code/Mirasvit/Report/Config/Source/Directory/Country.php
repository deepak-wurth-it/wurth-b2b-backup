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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Config\Source\Directory;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Locale\ListsInterface;

class Country implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ListsInterface
     */
    private $localeLists;

    /**
     * Country constructor.
     * @param CollectionFactory $countryCollectionFactory
     * @param ListsInterface $localeLists
     */
    public function __construct(
        CollectionFactory $countryCollectionFactory,
        ListsInterface $localeLists
    ) {
        $this->collectionFactory = $countryCollectionFactory;
        $this->localeLists = $localeLists;
    }


    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->collectionFactory->create() as $item) {
            $label = $this->localeLists->getCountryTranslation($item->getCountryId());
            if (!$label) {
                continue;
            }

            $result[] = [
                'label' => $label,
                'value' => $item->getCountryId(),
            ];
        }

        usort($result, function ($a, $b) {
            return $a['label'] > $b['label'];
        });

        return $result;
    }
}
