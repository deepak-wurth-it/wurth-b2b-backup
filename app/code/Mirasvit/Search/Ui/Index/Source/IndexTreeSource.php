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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Repository\IndexRepository;

class IndexTreeSource implements OptionSourceInterface
{
    private $indexRepository;

    private $request;

    private $usedNames = [];

    public function __construct(
        IndexRepository $indexRepository,
        RequestInterface $request
    ) {
        $this->indexRepository = $indexRepository;
        $this->request         = $request;
    }

    public function toOptionArray(): array
    {
        $options = [];

        $identifiers = $this->indexRepository->getCollection()
            ->getColumnValues(IndexInterface::IDENTIFIER);

        $currentIdentifier = null;
        if ($this->request->getParam(IndexInterface::ID)) {
            $index = $this->indexRepository->get((int)$this->request->getParam(IndexInterface::ID));

            $currentIdentifier = $index ? $index->getIdentifier() : null;
        }

        foreach ($this->indexRepository->getList() as $instance) {
            $identifier = $instance->getIdentifier();

            if (in_array($identifier, $identifiers) && $identifier != $currentIdentifier || in_array($instance->getName(), $this->usedNames)) {
                continue;
            }

            $this->usedNames[] = $instance->getName();
            $group = trim(explode('/', $instance->getName())[0]);
            $name  = trim(explode('/', $instance->getName())[1]);

            if (!isset($options[$group])) {
                $options[$group] = [
                    'label'    => $group,
                    'value'    => $group,
                    'optgroup' => [],
                ];
            }

            $options[$group]['optgroup'][] = [
                'label'    => (string)$name,
                'value'    => $identifier,
                'disabled' => true,
            ];
        }

        return array_values($options);
    }
}
