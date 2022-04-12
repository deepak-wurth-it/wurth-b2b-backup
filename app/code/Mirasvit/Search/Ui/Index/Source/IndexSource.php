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



namespace Mirasvit\Search\Ui\Index\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\Search\Repository\IndexRepository;

class IndexSource implements OptionSourceInterface
{
    private $indexRepository;

    public function __construct(
        IndexRepository $indexRepository
    ) {
        $this->indexRepository = $indexRepository;
    }

    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->indexRepository->getList() as $instance) {
            $identifier = $instance->getIdentifier();
            //            if (!$onlyUnused
            //                || !$this->indexRepository->getCollection()
            //                    ->getItemByColumnValue(IndexInterface::IDENTIFIER, $identifier)
            //            ) {
            $options[] = [
                'label' => (string)$instance->getName(),
                'value' => $identifier,
            ];
            //            }
        }

        return $options;
    }
}
