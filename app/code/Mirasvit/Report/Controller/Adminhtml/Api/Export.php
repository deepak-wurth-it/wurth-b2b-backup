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



namespace Mirasvit\Report\Controller\Adminhtml\Api;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Mirasvit\Report\Model\Export\ConvertToCsv;
use Mirasvit\Report\Model\Export\ConvertToXml;
use Mirasvit\ReportApi\Api\RequestBuilderInterface;

class Export extends AbstractApi
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ConvertToXml
     */
    private $convertToXml;

    /**
     * @var ConvertToCsv
     */
    private $convertToCsv;

    /**
     * @var RequestBuilderInterface
     */
    private $requestBuilder;

    /**
     * Export constructor.
     * @param FileFactory $fileFactory
     * @param ConvertToXml $convertToXml
     * @param ConvertToCsv $convertToCsv
     * @param RequestBuilderInterface $requestBuilder
     * @param Context $context
     */
    public function __construct(
        FileFactory $fileFactory,
        ConvertToXml $convertToXml,
        ConvertToCsv $convertToCsv,
        RequestBuilderInterface $requestBuilder,
        Context $context
    ) {
        $this->fileFactory  = $fileFactory;
        $this->convertToXml = $convertToXml;
        $this->convertToCsv = $convertToCsv;

        $this->requestBuilder = $requestBuilder;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');

        $response = $this->processRequest();

        if ($type === 'xml') {
            $content = $this->convertToXml->getXmlFile($response);
        } else {
            $content = $this->convertToCsv->getCsvFile($response);
        }

        return $this->fileFactory->create('export.' . $type, $content, 'var');
    }

    /**
     * @return \Mirasvit\ReportApi\Api\RequestInterface
     */
    private function processRequest()
    {
        $r = $this->getRequest();

        $request = $this->requestBuilder->create();
        $request->setTable($r->getParam('table'))
            ->setDimensions($r->getParam('dimensions'));

        foreach ($r->getParam('dimensions', []) as $c) {
            $request->addColumn($c);
        }

        foreach ($r->getParam('columns', []) as $c) {
            $request->addColumn($c);
        }

        foreach ($r->getParam('filters', []) as $filter) {
            if ($filter['conditionType'] == 'like') {
                $filter['value'] = '%' . $filter['value'] . '%';
            }

            $request->addFilter($filter['column'], $filter['value'], $filter['conditionType']);
        }

        foreach ($r->getParam('sortOrders', []) as $sortOrder) {
            $request->addSortOrder($sortOrder['column'], $sortOrder['direction']);
        }

        return $request;
    }
}
