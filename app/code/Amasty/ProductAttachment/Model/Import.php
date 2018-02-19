<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\File\Read;

class Import
{

    protected $csvFields
        = [
            'file name'                               => 'file_name',
            'product sku'                             => 'product_sku',
            'file title'                              => 'label',
            'sort order'                              => 'position',
            'customer group ids'                      => 'customer_group',
            'show only if a product has been ordered' => 'show_for_ordered',
            'visible'                                 => 'is_visible',
            'url'                                     => 'file_url',
        ];

    /**
     * & - is link to other field
     *
     * @return array
     */
    protected function getCsvFieldsDefaultValues()
    {
        return [
            'label'            => '&file_name',
            'show_for_ordered' => $this->getConfigHelper()
                                       ->getShowOrderedDefault(),
            'customer_group'   => $this->getConfigHelper()
                                       ->getCustomerGroupsDefault(),
            'position'         => 10,
            'is_visible'       => 1,
        ];
    }

    protected $requiredCsvFields = [
        'file_name',
        'product_sku',
    ];

    protected $fileData = [
        'file_name',
    ];

    protected $fileStoreData = [
        'label',
        'position',
        'show_for_ordered',
        'is_visible',
    ];

    protected $fileCustomerGroupData = [
        'customer_group'
    ];

    /**
     * @var array
     */
    protected $result = [];

    /**
     * @var int
     */
    protected $lineNumber = 0;

    /**
     * @var int
     */
    protected $countImportedLines = 0;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $csvAbsolutePath
     * @param string $csvFileName
     *
     * @return array
     */
    public function importFromCsv($csvAbsolutePath, $csvFileName)
    {
        try {
            $csvFile = $this->getCsvFile($csvAbsolutePath, $csvFileName);

            $fieldNames = $this->readCsvHeader($csvFile);
            $fieldNames = array_map('strtolower', $fieldNames);

            if (!$this->validateFileHeader($fieldNames)) {
                $errorMessage = __("Can't import the file. Please Check required fields (%1)",
                    implode(',', $this->getRequiredFieldLabels()));
                throw new LocalizedException($errorMessage);
            }

            while(($csvLineRow = $csvFile->readCsv()) !== false) {
                $this->prepareAndImportCsvLine($fieldNames, $csvLineRow);
            }
            if (array_key_exists('errors', $this->result)) {
                $this->result['errorcode'] = 1;
            }
            $this->result['success'] = __('Imported %1 attachment(s)', $this->countImportedLines);
        } catch (LocalizedException $e) {
            $this->result['errorcode'] = 1;
            $this->result['errors'][] = $e->getMessage();
            $this->result['errors'][] = __('Error On line %1', $this->lineNumber);
        }

        return $this->result;
    }

    public function prepareAndImportCsvLine($fieldNames, $csvLineRow)
    {
        try {
            $this->lineNumber++;
            if (count($csvLineRow) == 1 && !trim($csvLineRow[0])) {
                return;
            }
            $csvLine = $this->mapCsvData($fieldNames, $csvLineRow);
            $this->importFromCsvLine($csvLine);
            $this->countImportedLines++;

        } catch (\Exception $e) {
            $this->result['errors'][] = $e->getMessage();
            $this->result['errors'][] = __('Error On line %1', $this->lineNumber);
        }
    }

    public function validateFileHeader($fileHeader)
    {
        return empty(array_diff($this->getRequiredFieldLabels(), $fileHeader));
    }

    /**
     * @param string $absolutePathToCsv
     * @param string $csvFileName
     *
     * @return Read
     */
    protected function getCsvFile($absolutePathToCsv, $csvFileName)
    {
        /**
         * @var \Magento\Framework\Filesystem\Directory\ReadFactory $directoryReadFactory
         */
        $directoryReadFactory = $this->objectManager->create('Magento\Framework\Filesystem\Directory\ReadFactory');

        $directoryRead = $directoryReadFactory->create($absolutePathToCsv);

        return $directoryRead->openFile($csvFileName);
    }

    /**
     * @param Read $csvFile
     *
     * @return array
     */
    protected function readCsvHeader(Read $csvFile)
    {
        $csvFile->seek($this->lineNumber++);
        $fileHeader = $csvFile->readCsv();
        foreach ($fileHeader as &$item) {
            $item = trim($item);
        }
        return $fileHeader;
    }

    protected function mapCsvData($fieldNames, $csvLine)
    {
        $resultCsvLine = [];
        foreach ($fieldNames as &$fieldName) {
            $fieldName = trim($fieldName);
        }
        foreach ($this->csvFields as $csvFieldLabel => $csvFieldCode) {
            $index = $this->getFieldLabel($csvFieldLabel, $fieldNames);

            if ($index !== false) {
                if ($csvFieldCode == 'customer_group') {
                    $resultCsvLine[$csvFieldCode] = $this->readCustomerGroupData($this->getDataFromCsvLine($index, $csvLine));
                } else {
                    $resultCsvLine[$csvFieldCode] = $this->getDataFromCsvLine($index, $csvLine);
                }
            }
        }

        return $resultCsvLine;

    }

    protected function readCustomerGroupData($customerGroupRowData)
    {
        if ($customerGroupRowData === '') {
            return $customerGroupRowData;
        }
        $customerGroupData = explode('-', $customerGroupRowData);
        foreach ($customerGroupData as &$customerGroupId) {
            $customerGroupId = trim($customerGroupId);
        }
        return $customerGroupData;
    }

    public function importFromCsvLine($csvLine)
    {

        $fileModel = $this->createFileModel();
        $productId = $this->getProductIdBySku($this->getDataFromCsvLine('product_sku', $csvLine));

        $fileData = $this->getFileData($csvLine);
        $fileData['product_id'] = $productId;
        $fileData['file_type'] = 'file';
        if (isset($csvLine['file_url']) && $csvLine['file_url']) {
            $fileData['file_type'] = 'url';
            $fileData['file_url'] = $csvLine['file_url'];
        }
        $storeId = array_key_exists('store_id', $csvLine)
            ? $this->getDataFromCsvLine('store_id', $csvLine) : 0;

        $fileStoreData = $this->getFileStoreData($csvLine);
        $fileModel->setStore($fileStoreData);

        $fileCustomerGroupData = $this->getFileCustomerGroupData($csvLine);
        $fileModel->setCustomerGroup($fileCustomerGroupData);

        $fileModel->saveProductAttachmentFromCsv($fileData, $storeId);
    }

    protected function getFileData($csvLine)
    {
        $fileData = $this->doGetFileData($csvLine, $this->fileData);
        return $fileData;
    }

    protected function getFileStoreData($csvLine)
    {
        $fileStoreData = $this->doGetFileData($csvLine, $this->fileStoreData);
        return $fileStoreData;
    }

    protected function getFileCustomerGroupData($csvLine)
    {
        $fileCustomerGroupData = $this->doGetFileData($csvLine, $this->fileCustomerGroupData);
        return (array_key_exists('customer_group',$fileCustomerGroupData)
            && !empty($fileCustomerGroupData['customer_group']))
            ? $fileCustomerGroupData['customer_group'] : [];
    }

    protected function doGetFileData($csvLine, $fieldsArray)
    {
        $fileData = [];
        $defaultValues = $this->getCsvFieldsDefaultValues();
        foreach ($fieldsArray as $field) {
            $defaultValue = array_key_exists($field, $defaultValues)
                ? $defaultValues[$field] : null;
            if ($defaultValue !== null && is_string($defaultValue) && strpos($defaultValue, '&') === 0) {
                $defaultValue = $csvLine[trim($defaultValue, '&')];
            } else {
                $defaultValue;
            }

            $fileData[$field] = (array_key_exists($field, $csvLine) && $csvLine[$field] !== '')
                ? $csvLine[$field] : $defaultValue;
        }
        return $fileData;
    }

    public function getRequiredFieldLabels()
    {
        return $this->getFieldLabels($this->requiredCsvFields);
    }

    public function getFieldLabels($fieldCodes)
    {
        $result = [];
        foreach ($fieldCodes as $fieldCode) {
            $result[] = $this->getFieldLabel($fieldCode, $this->csvFields);
        }
        return $result;
    }

    public function getFieldLabel($fieldCode, $fieldsScope)
    {
        return array_search($fieldCode, $fieldsScope);
    }

    /**
     * @param string $productSku
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return int
     */
    public function getProductIdBySku($productSku)
    {
        /**
         * @var \Magento\Catalog\Model\Product $product
         */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $productId = $product->getIdBySku($productSku);

        if (!$productId) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(sprintf('Not Found Product with sku %s', $productSku))
            );
        }

        return $productId;

    }

    /**
     * @return \Amasty\ProductAttachment\Model\File
     */
    protected function createFileModel()
    {
        return $this->objectManager->create('Amasty\ProductAttachment\Model\File');
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $result = [];
        foreach ($this->csvFields as $csvFieldLabel => $code) {
            $result[$code]['name'] = $csvFieldLabel;
            $result[$code]['required'] = in_array($code, $this->requiredCsvFields)
                ? 'required' : 'optional';
        }
        return $result;
    }

    protected function getDataFromCsvLine($dataCode, $csvLine)
    {
        if (!array_key_exists($dataCode, $csvLine)) {
            throw new LocalizedException(__('Column count doesn\'t match value count.'));
        }
        $returnValue = $csvLine[$dataCode];
        $returnValue = trim($returnValue);
        $returnValue = $this->iconvString($returnValue);

        return $returnValue;
    }

    protected function iconvString($string) {
        if (extension_loaded('mbstring')) {
            $charsetList = array_merge(mb_detect_order(), ['CP-1251']);
            $inCharset = mb_detect_encoding($string, $charsetList);
        } else {
            throw new \Exception('Can not detect file encoding. The module required mbstring extension.');
        }
        return iconv($inCharset, 'UTF-8//IGNORE', $string);

    }

    /**
     * @return \Amasty\ProductAttachment\Helper\Config
     */
    public function getConfigHelper()
    {
        return $this->objectManager->get('Amasty\ProductAttachment\Helper\Config');
    }
}
