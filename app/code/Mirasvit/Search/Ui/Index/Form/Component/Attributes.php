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



namespace Mirasvit\Search\Ui\Index\Form\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Mirasvit\Search\Repository\IndexRepository;

class Attributes extends AbstractComponent
{
    private $indexRepository;

    public function __construct(
        IndexRepository $indexRepository,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->indexRepository = $indexRepository;

        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return 'attributes';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config              = $this->getData('config');
        $config['instances'] = [];

        foreach ($this->indexRepository->getList() as $instance) {
            $config['instances'][$instance->getIdentifier()] = $instance->getAttributes();
        }

        $this->setData('config', $config);

        parent::prepare();
    }
}
