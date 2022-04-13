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



namespace Mirasvit\Report\Ui\Email\Form\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Mirasvit\Report\Api\Repository\EmailRepositoryInterface;
use Mirasvit\Report\Service\DateService;

class Blocks extends AbstractComponent
{
    /**
     * @var DateService
     */
    private $dateService;
    /**
     * @var EmailRepositoryInterface
     */
    private $emailRepository;

    /**
     * Blocks constructor.
     * @param DateService $dateService
     * @param EmailRepositoryInterface $emailRepository
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        DateService $dateService,
        EmailRepositoryInterface $emailRepository,
        ContextInterface $context,
        $components = [],
        array $data = []
    ) {
        $this->dateService = $dateService;
        $this->emailRepository = $emailRepository;
        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return 'blocks';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $config['reports'] = $this->emailRepository->getReports();

        foreach ($this->dateService->getIntervals(true) as $interval => $label) {
            $config['ranges'][] = [
                'label' => $label,
                'value' => $interval,
            ];
        }



        //        foreach ($this->indexRepository->getList() as $instance) {
        //            $config['instances'][$instance->getIdentifier()] = $instance->getAttributes();
        //        }
        //        echo '<pre>';
        //        print_R($config);
        //        die();
        $this->setData('config', $config);

        parent::prepare();
    }
}
