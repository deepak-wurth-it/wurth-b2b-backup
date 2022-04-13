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
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Config\Loader;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\ValidationStateInterface;

/**
 * Loads reports configuration from XML file by merging them together
 */
class Reader extends Filesystem
{
    /**
     * Mapping XML name nodes
     * @var array
     */
    protected $_idAttributes
        = [
            '/config/(table|eavTable|relation)' => 'name',
            '/config/(table|eavTable)/column'   => 'name',
        ];

    /**
     * Reader constructor.
     * @param FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        Converter $converter,
        SchemaLocator $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = 'mst_report.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }

    /**
     * Load configuration scope
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $scope    = $scope ? : $this->_defaultScope;
        $fileList = $this->getFiles($scope);

        if (!count($fileList)) {
            return [];
        }
        $output = $this->_readFiles($fileList);

        return $output;
    }

    /**
     * @param string|null $scope
     * @return string[]
     */
    public function getFiles($scope)
    {
        $result = $this->_fileResolver->get($this->_fileName, $scope);
        if (!is_array($result) && is_object($result)) {
            $result = method_exists($result, 'toArray') ? $result->toArray() : [];
        }
        return $result;
    }
}
