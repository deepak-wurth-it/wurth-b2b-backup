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

namespace Mirasvit\Search\Service;

use Magento\Framework\Data\Collection;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Repository\IndexRepository;

class IndexService
{
    private $indexRepository;

    public function __construct(
        IndexRepository $indexRepository
    ) {
        $this->indexRepository = $indexRepository;
    }

    public function getSearchCollection(IndexInterface $index): Collection
    {
        return $this->indexRepository->getInstance($index)->getSearchCollection();
    }

    public function getQueryResponse(IndexInterface $index)
    {
        return $this->indexRepository->getInstance($index)->getQueryResponse();
    }
}
