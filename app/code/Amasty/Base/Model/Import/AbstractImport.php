<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Model\Import;

use Amasty\Base\Model\Import\Behavior\BehaviorProviderInterface;
use Amasty\Base\Model\Import\Mapping\MappingInterface;
use Amasty\Base\Model\Import\Validation\EncodingValidator;
use Amasty\Base\Model\Import\Validation\ValidatorPoolInterface;
use Amasty\Base\Model\MagentoVersion;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;

/**
 * @since 1.4.6
 */
abstract class AbstractImport extends AbstractEntity
{
    const ALLOWED_ERROR_LIMIT = 'isErrorLimit';
    const MULTI_VALUE_SEPARATOR = ',';
    const RUNTIME_ERRORS = 'am_runtime_errors';

    /**
     * @var int
     */
    private $runTimeErrorCounter = 0;

    /**
     * @var array
     */
    private $pushedRunTimeErrors = [];

    /**
     * @var bool
     */
    private $isImport = false;

    /**
     * @var ValidatorPoolInterface
     */
    private $validatorPool;

    /**
     * @var BehaviorProviderInterface
     */
    private $behaviorProvider;

    /**
     * @var MappingInterface
     */
    private $mapping;

    private $entityTypeCode;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var ImportCounter
     */
    private $importCounter;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $entityTypeCode,
        ValidatorPoolInterface $validatorPool,
        BehaviorProviderInterface $behaviorProvider,
        MappingInterface $mapping,
        EncodingValidator $encodingValidator,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        ResourceConnection $resource,
        array $data = [],
        MagentoVersion $magentoVersion = null,
        ImportCounter $importCounter = null
    ) {
        if (empty($entityTypeCode)) {
            throw new \Amasty\Base\Exceptions\EntityTypeCodeNotSet();
        }
        $this->mapping = $mapping;
        $this->behaviorProvider = $behaviorProvider;
        $this->validatorPool = $validatorPool;
        $this->validatorPool->addValidator($encodingValidator);
        foreach ($this->validatorPool->getValidators() as $validator) {
            //array keys should be saved Leonid
            $this->errorMessageTemplates += $validator->getErrorMessages();
        }
        $this->errorMessageTemplates[self::ALLOWED_ERROR_LIMIT] = __('<b>Allowed errors limit is reached.</b>');
        $this->masterAttributeCode = $this->mapping->getMasterAttributeCode();
        $this->validColumnNames = $this->_permanentAttributes = $this->mapping->getValidColumnNames();

        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $data
        );
        $this->entityTypeCode = $entityTypeCode;

        if ($magentoVersion === null) {
            $this->magentoVersion = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(MagentoVersion::class);
        }
        if ($importCounter === null) {
            $this->importCounter = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(ImportCounter::class);
        }
    }

    /**
     * Validation failure message template definitions
     *
     * @var array $rowData
     * @var int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (version_compare($this->magentoVersion->get(), '2.3.0', '<')) {
            /**
             * Import logic fix.
             * hasToBeTerminated doesn't check while validation
             */
            if (!$this->isImport && $this->getErrorAggregator()->hasToBeTerminated()) {
                $this->addRowError(
                    self::ALLOWED_ERROR_LIMIT,
                    0,
                    null,
                    null,
                    ProcessingError::ERROR_LEVEL_CRITICAL
                );

                return true;
            }
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;
        $this->_processedEntitiesCount++;

        if ($validationErrors = $this->processValidation($rowData)) {
            foreach ($validationErrors as $errorCode => $errorLevel) {
                /**
                 * Error level import fix.
                 * Less then ProcessingError::ERROR_LEVEL_CRITICAL will pass validation
                 */
                if ($this->isImport && $errorLevel === ProcessingError::ERROR_LEVEL_NOT_CRITICAL) {
                    $errorLevel = ProcessingError::ERROR_LEVEL_CRITICAL;
                }
                $this->addRowError($errorCode, $rowNum, null, null, $errorLevel);
            }
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * @param array $rowData
     *
     * @return array|bool
     */
    protected function processValidation(array $rowData)
    {
        $validationErrors = [];
        foreach ($this->validatorPool->getValidators() as $validator) {
            try {
                $errors = $validator->validateRow($this->mapRow($rowData), $this->getBehavior());
                if (is_array($errors)) {
                    $this->evaluateRuntimeErrors($errors);
                    $validationErrors += $errors;
                }
            } catch (\Amasty\Base\Exceptions\StopValidation $exception) {
                if (is_array($exception->getValidateResult())) {
                    $validationErrors += $exception->getValidateResult();
                }
                break;
            }
        }

        if (!empty($validationErrors)) {
            return $validationErrors;
        }

        return false;
    }

    /**
     * @since 1.9.6
     *
     * @param array $errors
     */
    public function evaluateRuntimeErrors(&$errors)
    {
        if (!empty($errors[self::RUNTIME_ERRORS]) && is_array($errors[self::RUNTIME_ERRORS])) {
            foreach ($errors[self::RUNTIME_ERRORS] as $error => $level) {
                if (!isset($this->pushedRunTimeErrors[$error])) {
                    $code = self::RUNTIME_ERRORS . '_' . (++$this->runTimeErrorCounter);
                    $this->getErrorAggregator()->addErrorMessageTemplate($code, $error);
                    $this->pushedRunTimeErrors[$error] = $code;
                }

                $errors[$this->pushedRunTimeErrors[$error]] = $level;
            }
            unset($errors[self::RUNTIME_ERRORS]);
        }
    }

    /**
     * @throws \Amasty\Base\Exceptions\NonExistentImportBehavior
     * @return bool
     */
    protected function _importData()
    {
        $this->processImport();

        return true;
    }

    /**
     * @throws \Amasty\Base\Exceptions\NonExistentImportBehavior
     */
    protected function processImport()
    {
        /**
         * Import fix. Errors less then ProcessingError::ERROR_LEVEL_CRITICAL validateRow as true.
         * Skip them because Import button is active.
         */
        $this->isImport = true;
        $behavior = $this->behaviorProvider->getBehavior($this->getBehavior());

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $importData = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                $importData[] = $this->mapRow($rowData);
            }

            /** ImportCounter @since 1.9.6 */
            $result = $behavior->execute($importData, $this->importCounter);
            /** Backward compatibility */
            if (is_object($result)) {
                $this->importCounter->incrementCreated($result->getCountItemsCreated() ?: 0);
                $this->importCounter->incrementUpdated($result->getCountItemsUpdated() ?: 0);
                $this->importCounter->incrementDeleted($result->getCountItemsDeleted() ?: 0);
            }
        }
        $this->countItemsCreated = $this->importCounter->getCreatedCount();
        $this->countItemsUpdated = $this->importCounter->getUpdatedCount();
        $this->countItemsDeleted = $this->importCounter->getDeletedCount();
        /** Import logic fix. Clear error log after import */
        $this->getErrorAggregator()->clear();
    }

    /**
     * @param array $row
     *
     * @return array
     */
    public function mapRow($row)
    {
        $resultRow = [];
        foreach ($row as $field => $value) {
            $resultRow[$this->mapping->getMappedField($field)] = $value;
        }
        return $resultRow;
    }

    /**
     * @inheritdoc
     */
    public function getEntityTypeCode()
    {
        return $this->entityTypeCode;
    }
}
