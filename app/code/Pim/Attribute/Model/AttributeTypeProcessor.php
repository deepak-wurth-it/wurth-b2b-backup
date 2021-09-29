<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Attribute\Model;
use Psr\Log\LoggerInterface;

class AttributeTypeProcessor
{


    /**
     *
     */
    public function __construct(

        \Pim\Attribute\Model\AttributeValuesFactory $AttributeValuesFactory,
        \Pim\Attribute\Model\AttributeFactory $AttributeFactory,
        LoggerInterface $logger


    ) {

        $this->attributeValuesFactory = $AttributeValuesFactory;
        $this->attributeFactory = $AttributeFactory;
        $this->logger = $logger;
    }


    public function initExecution()
    {

        try {
            $objAttributeVales = $this->attributeValuesFactory->create();
            $collection = $objAttributeVales->getCollection();
            $countAttributeId = $collection->getColumnValues('AttributeId');
            $countAttributeId = array_count_values($countAttributeId);

            if (count($countAttributeId) > 1) {
                foreach ($countAttributeId as $key => $value) {

                    try {
                        $objAttribute = $this->attributeFactory->create()->load($key);

                        if (empty($objAttribute->getData('magento_attribute_type'))) {
                            $objAttribute->setData('magento_attribute_type', 'select');
                            $objAttribute->save();
                            echo 'Attribute Type Updated For Id ' . $key . PHP_EOL;
                        }
                    } catch (\Exception $e) {
                        $this->logger->info('Failed to update attribute type Id ' . $key . PHP_EOL);
                        echo 'Failed to update attribute type Id ' . $key . PHP_EOL;
                        echo $e->getMessage() . "\n" . PHP_EOL;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Something went wrong in pim collection  ' . PHP_EOL);

            echo 'Something went wrong in pim collection  ' . PHP_EOL;
            echo $e->getMessage() . "\n" . PHP_EOL;
        }
    }
}
