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



namespace Mirasvit\Report\Ui;

use Magento\Backend\Block\Template;
use Mirasvit\Report\Api\Service\DateServiceInterface;

class ConfigDataProvider extends Template
{
    /**
     * @var DateServiceInterface
     */
    private $dateService;

    /**
     * ConfigDataProvider constructor.
     * @param DateServiceInterface $dateService
     * @param Template\Context $context
     */
    public function __construct(
        DateServiceInterface $dateService,
        Template\Context $context
    ) {
        $this->dateService = $dateService;

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getConfigData()
    {
        $result = [
            'dateRange' => [],
        ];

        foreach ($this->dateService->getIntervals() as $identifier => $label) {
            $range = $this->dateService->getInterval($identifier);

            $result['dateRange'][$identifier] = [
                'label' => $label,
                'from'  => $range->getFrom()->toString('Y-MM-ddTHH:mm:ss'),
                'to'    => $range->getTo()->toString('Y-MM-ddTHH:mm:ss'),
            ];
        }

        return $result;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $json = \Zend_Json::encode($this->getConfigData());

        return "<script>var configDataProvider = $json</script>";
    }
}
