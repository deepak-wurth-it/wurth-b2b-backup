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



namespace Mirasvit\Report\Model\Export;

use Magento\Framework\Filesystem;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;
use Mirasvit\Report\Service\XmlWriter;
use Mirasvit\Report\Service\XmlWriterFactory;
use Mirasvit\ReportApi\Api\ResponseInterface;
use Mirasvit\ReportApi\Api\RequestInterface;

class ConvertToXml extends ConvertToCsv
{
    /**
     * @var XmlWriterFactory
     */
    protected $xmlWriterFactory;

    /**
     * @var SearchResultIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * ConvertToXml constructor.
     *
     * @param SearchResultIteratorFactory $iteratorFactory
     * @param Filesystem                  $filesystem
     * @param XmlWriterFactory            $xmlWriterFactory
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        SearchResultIteratorFactory $iteratorFactory,
        Filesystem $filesystem,
        XmlWriterFactory $xmlWriterFactory
    ) {
        $this->iteratorFactory  = $iteratorFactory;
        $this->xmlWriterFactory = $xmlWriterFactory;

        parent::__construct($filesystem);
    }

    /**
     * @param \Mirasvit\ReportApi\Processor\ResponseItem $item
     *
     * @return array
     */
    public function getItemData($item)
    {
        return $item->getFormattedData();
    }

    /**
     * @param RequestInterface $request
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getXmlFile(RequestInterface $request)
    {
        $name = hash('sha256', microtime());
        $file = 'export/' . $name . '.xml';

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $header = [];

        for ($i = 1; $i == $i; $i++) {
            $r = clone $request;
            $r->setCurrentPage($i);
            $resp = $r->process();

            if(!$resp->getItems()) {
                break;
            }

            /** @var XmlWriter $xmlWriter */
            $xmlWriter = $this->xmlWriterFactory->create([
                'iterator' => $this->iteratorFactory->create(['items' => $resp->getItems()]),
                'rowCallback' => [$this, 'getItemData'],
            ]);

            if($i === 1) {
                foreach ($resp->getColumns() as $column) {
                    $header[] = $column->getLabel();
                }

                $xmlWriter->setDataHeader($header);
            }

            $xmlWriter->write($stream, $name . '.xml');
        }

        $stream->unlock();
        $stream->close();

        return [
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true  // can delete file after use
        ];
    }
}
