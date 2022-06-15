<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Sales\Model;

use Psr\Log\LoggerInterface;
use WurthNav\Sales\Model\SalesShipmentLineFactory as SalesShipmentLineFactory;
use WurthNav\Sales\Model\SalesShipmentLineMiddlewareFactory as SalesShipmentLineMiddlewareFactory;

/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SalesShipmentLineSyncProcessor
{
    
    protected $salesShipment;
    public $log;
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SalesShipmentLineMiddlewareFactory $salesShipmentLineMiddlewareFactory,
        SalesShipmentLineFactory $salesShipmentLineFactory,
        LoggerInterface $logger
    ) {

        $this->storeManager = $storeManager;
        $this->salesShipmentLineMiddlewareFactory = $salesShipmentLineMiddlewareFactory;
        $this->salesShipmentLineFactory = $salesShipmentLineFactory;
        $this->logger = $logger;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {
        $this->salesShipment = '';
        $salesShipment = $this->salesShipmentLineMiddlewareFactory->create();

        $collection = $salesShipment->getCollection();

        $sizeTotal = $collection->getSize();
        $size = $sizeTotal / 100;
        $size = ceil($size);
        $limit = 100;
        if ($sizeTotal > 0) {

            for ($i = 1; $i <= $size; $i++) {

                $collection->setPageSize($limit);
                $collection->setCurPage($i);

                //echo $collection->getSelect();

                $this->log .= 'Started page ' . $i . PHP_EOL;

                if ($collection->getSize() > 0 && $collection->count()) {
                    foreach ($collection as $row) {
                        try {
                            $salesShipmentInner = $this->salesShipmentLineFactory->create();

                            $LineNo = $row->getData('LineNo_');

                            if ($LineNo) {
                                $salesShipmentInner->setData('LineNo', $LineNo);
                            }

                            $DocumentNo = $row->getData('Document No_');

                            if ($DocumentNo) {
                                $salesShipmentInner->setData('DocumentNo', $DocumentNo);
                            }

                            $SellToCustomerNo = $row->getData('Sell-to Customer No_');

                            if ($SellToCustomerNo) {
                                $salesShipmentInner->setData('SellToCustomerNo', $SellToCustomerNo);
                            }

                            $Type = $row->getData('Type');

                            if ($Type) {
                                $salesShipmentInner->setData('Type', $Type);
                            }

                            $No = $row->getData('No_');
                            if ($No) {
                                $salesShipmentInner->setData('No', $No);
                            }

                            $ShipmentDate = $row->getData('Shipment Date');

                            if ($ShipmentDate) {
                                $salesShipmentInner->setData('ShipmentDate', $ShipmentDate);
                            }

                            $Description = $row->getData('Description');
                            if ($Description) {
                                $salesShipmentInner->setData('Description', $Description);
                            }

                            $Quantity = $row->getData('Quantity');
                            if ($Quantity) {
                                $salesShipmentInner->setData('Quantity', $Quantity);
                            }

                            $ShortcutDimension1Code = $row->getData('Shortcut Dimension 1 Code');
                            if ($ShortcutDimension1Code) {
                                $salesShipmentInner->setData('ShortcutDimension1Code', $ShortcutDimension1Code);
                            }

                            $OrderNo = $row->getData('Order No_');
                            if ($OrderNo) {
                                $salesShipmentInner->setData('OrderNo', $OrderNo);
                            }

                            $BillToCustomerNo = $row->getData('Bill-to Customer No_');
                            if ($BillToCustomerNo) {
                                $salesShipmentInner->setData('BillToCustomerNo', $BillToCustomerNo);
                            }

                            $TypeDocument = $row->getData('TypeDocument');
                            if ($TypeDocument) {
                                $salesShipmentInner->setData('TypeDocument', $TypeDocument);
                            }

                            $ModifyDate = $row->getData('Modify Date');
                            if ($ModifyDate) {
                                $salesShipmentInner->setData('ModifyDate', $ModifyDate);
                            }

                            $CreateDate = $row->getData('Create Date');
                            if ($CreateDate) {
                                $salesShipmentInner->setData('CreateDate', $CreateDate);
                            }

                            $Id = $row->getData('Id');
                            if ($Id) {
                                $salesShipmentInner->setData('SalesShipmentLine_ai_id', $Id);
                            }

                            $checkExist = $this->salesShipmentLineFactory->create()->load($Id, 'SalesShipmentLine_ai_id');

                            if ($checkExist->getId()) {
                                $salesShipmentInner->setData('Id', $checkExist->getId());
                                $salesShipmentInner->save();
                                $this->log .= 'Updated Magento SalesShipmentLine Id ' . $checkExist->getId() . PHP_EOL;
                            } else {

                                $salesShipmentInner->save();
                                $this->log .=  'Inserted Middleware SalesShipmentLine Id ' . $Id . PHP_EOL;
                            }
                        } catch (\Exception $e) {
                            $this->logger->info($e->getMessage());
                            echo $e->getMessage() . PHP_EOL;
                            continue;
                        }
                    }
                }
                $collection->clear();

                if ($i == 500) {
                }
                $this->wurthNavLogger($this->log);
                $this->log = "";
                 // No Synchronized or need_update  Other validation field found for previous  done lines
            }
        }
    }


    public function wurthNavLogger($log = null)
    {
        echo $log;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wurthnav_sales_shipment_import.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($log);
    }
}
