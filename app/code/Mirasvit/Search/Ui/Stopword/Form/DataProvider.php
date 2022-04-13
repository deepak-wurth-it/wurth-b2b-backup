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



namespace Mirasvit\Search\Ui\Stopword\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Search\Api\Data\StopwordInterface;
use Mirasvit\Search\Repository\StopwordRepository;

class DataProvider extends AbstractDataProvider
{
    private $stopwordRepository;

    public function __construct(
        StopwordRepository $repository,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->stopwordRepository = $repository;
        $this->collection         = $this->stopwordRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $result = [];

        foreach ($this->stopwordRepository->getCollection() as $stopword) {
            $data                       = [
                StopwordInterface::ID       => $stopword->getId(),
                StopwordInterface::TERM     => $stopword->getTerm(),
                StopwordInterface::STORE_ID => $stopword->getStoreId(),
            ];
            $result[$stopword->getId()] = $data;
        }

        return $result;
    }
}
