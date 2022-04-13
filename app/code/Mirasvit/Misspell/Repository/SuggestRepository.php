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

namespace Mirasvit\Misspell\Repository;

use Mirasvit\Misspell\Api\Data\SuggestInterface;
use Mirasvit\Misspell\Model\ConfigProvider;
use Mirasvit\Misspell\Model\SuggestFactory;

class SuggestRepository implements SuggestInterface
{
    private $configProvider;

    private $suggestFactory;

    public function __construct(
        ConfigProvider $configProvider,
        SuggestFactory $suggestFactory
    ) {
        $this->configProvider = $configProvider;
        $this->suggestFactory = $suggestFactory;
    }

    public function suggest(string $query): ?string
    {
        $suggestModel = $this->suggestFactory->create()->load($query, SuggestInterface::QUERY);
        $suggest = $suggestModel->getSuggest();

        if ($suggest === null) {
            $suggest = $this->configProvider->getAdapter()->suggest($query);

            if ($suggest !== null) {
                $suggestModel->setQuery($query)->setSuggest($suggest)->save();
            }
        }

        return $suggest;
    }
}
