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

namespace Mirasvit\Misspell\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Mirasvit\Misspell\Api\Data\SuggestInterface;

class Suggest extends AbstractModel
{
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->_init(ResourceModel\Suggest::class);

        parent::__construct($context, $registry);
    }

    public function getSuggestId(): ?int
    {
        return (int)parent::getData(SuggestInterface::ID);
    }

    public function getQuery(): string
    {
        return (string)parent::getData(SuggestInterface::QUERY);
    }

    public function setQuery(string $input): Suggest
    {
        return parent::setData(SuggestInterface::QUERY, $input);
    }

    public function getSuggest(): ?string
    {
        return parent::getData(SuggestInterface::SUGGEST);
    }

    public function setSuggest(string $input): Suggest
    {
        return parent::setData(SuggestInterface::SUGGEST, $input);
    }
}
