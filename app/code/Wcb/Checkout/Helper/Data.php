<?php

namespace Wcb\Checkout\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class Data extends AbstractHelper
{
    protected $productLoader;

    protected $connection;

    protected $productRepository;

    protected $type = ['2' => '100'];

    public function __construct(
        ProductRepositoryInterface $productrepositoryInterface,
        ProductFactory $productFactory,
        ResourceConnection $resourceConnection,
        Context $context
    ) {
        $this->productLoader = $productFactory;
        $this->productRepository = $productrepositoryInterface;
        $this->connection = $resourceConnection->getConnection();
        parent::__construct($context);
    }

    public function getLoadProduct($id)
    {
        return $this->productRepository->getById($id);
    }

    public function getType($base_unit_of_measure_id)
    {
        $id = (int)$base_unit_of_measure_id;
        $selectExist = $this->connection->select()
            ->from(
                ['uom' => 'unitsofmeasure'],
                ['Code']
            )
            ->where('unitsofmeasure_id = ?', $id);

        $dataExist = $this->connection->fetchOne($selectExist);
        return $dataExist;
    }

    public function getQuantityUnitByQuantity($qty, $product)
    {
        $qty = (float)$qty;
        $unitOfMeasureId = (float)$product->getBaseUnitOfMeasureId();
        $minimumQty = (float)$product->getMinimumSalesUnitQuantity();
        $unitQty = 1;
        if ($unitOfMeasureId && $minimumQty && $qty) {
            $unitOfMeasure = isset($this->type[$unitOfMeasureId]) ? $this->type[$unitOfMeasureId] : 1;
            $unitQty = $qty / ($unitOfMeasure * $minimumQty);
        }
        return $unitQty;
    }

    public function getMinimumAndMeasureQty($product)
    {
        $minimumQty = (float)$product->getMinimumSalesUnitQuantity();
        $unitOfMeasureId = (float)$product->getBaseUnitOfMeasureId();
        $result = 0;
        if ($unitOfMeasureId && $minimumQty) {
            $unitOfMeasure = isset($this->type[$unitOfMeasureId]) ? $this->type[$unitOfMeasureId] : 1;
            $result = $minimumQty * $unitOfMeasure;
        }
        return $result;
    }
    public function getTotalQty($product, $qty)
    {
        return $this->getMinimumAndMeasureQty($product) * $qty;
    }
}
