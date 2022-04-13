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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Mirasvit\ReportApi\Api\Processor\ResponseColumnInterface;
use Mirasvit\ReportApi\Api\Processor\ResponseItemInterface;
use Mirasvit\ReportApi\Api\ResponseInterface;
use Mirasvit\ReportApi\Api\RequestInterface;

class ConvertToCsv
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $directory;

    /**
     * ConvertToCsv constructor.
     * @param Filesystem $filesystem
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getCsvFile(RequestInterface $request)
    {
        $name = hash('sha256', microtime());
        $file = 'export/' . $name . '.csv';

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $header = [];

        $request->setPageSize(1000);

        for ($i = 1; $i == $i; $i++) {
            $r = clone $request;
            $r->setCurrentPage($i);
            $resp = $r->process();

            if($i === 1) {
                foreach ($resp->getColumns() as $column) {
                    $header[] = $column->getLabel();
                }
                $stream->writeCsv($header);
            }

            if(!count($resp->getItems())) {
                $stream->writeCsv($resp->getTotals()->getFormattedData());
                break;
            }

            foreach ($resp->getItems() as $item) {
                $this->writeItem($stream, $item, $resp->getColumns());
            }

        }

        $stream->unlock();
        $stream->close();

        return [
            'type'  => 'filename',
            'value' => $file,
        ];
    }

    /**
     * @param Filesystem\File\WriteInterface $stream
     * @param ResponseItemInterface $item
     * @param array $columns
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function writeItem(Filesystem\File\WriteInterface $stream, ResponseItemInterface $item, array $columns)
    {
        $formattedData = $item->getFormattedData();

        $data = [];
        /** @var ResponseColumnInterface $column */
        foreach ($columns as $column) {
            $name = $column->getName();

            if (isset($formattedData[$name])) {
                $data[] = $formattedData[$name];
            } else {
                $data[] = '';
            }
        }

        $stream->writeCsv($data);

        foreach ($item->getItems() as $subItem) {
            $this->writeItem($stream, $subItem, $columns);
        }
    }
}
