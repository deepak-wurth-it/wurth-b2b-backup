<?php

namespace Wcb\QuickOrder\Helper;

use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class Data extends AbstractHelper
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    /**
     * @var Attribute
     */
    protected $_eavAttribute;

    /**
     * Data constructor.
     * @param Context $context
     * @param ResourceConnection $resourceConnection
     * @param Attribute $eavAttribute
     */
    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        Attribute $eavAttribute
    ) {
        $this->_eavAttribute = $eavAttribute;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    /**
     * @param $productsCode
     * @return array
     */
    public function getProductCodeWithProductId($productsCode)
    {
        // remove space in user enter code and use
        if (!$productsCode) {
            return;
        }
        $productsCode = str_replace(' ', '', $productsCode);
        $productsCode = "'" . $productsCode . "%'";
        $productCodeId = $this->getProductCodeAttributeId();
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('catalog_product_entity_text');
        $query = "SELECT row_id FROM " . $table . " WHERE attribute_id = $productCodeId && REPLACE(value,' ','') LIKE $productsCode";
        $data = $connection->fetchAll($query);
        $productData = [];
        foreach ($data as $row) {
            if (isset($row['row_id'])) {
                $productData[] = $row['row_id'];
            }
        }
        return $productData;
    }

    /**
     * @return int
     */
    public function getProductCodeAttributeId()
    {
        return $this->_eavAttribute->getIdByCode('catalog_product', 'product_code');
    }
}
