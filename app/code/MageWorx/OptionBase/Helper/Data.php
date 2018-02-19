<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Framework\App\ResourceConnection;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var string|int
     */
    protected $moduleVersion;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * List of MageWorx Option attributes can be linked by SKU.
     *
     * @var array
     */
    protected $linkedAttributes = [];

    /**
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        ResourceConnection $resource,
        $linkedAttributes = []
    ) {
        $this->productMetadata = $productMetadata;
        $this->objectManager = $objectManager;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->messageManager = $messageManager;
        $this->response = $response;
        $this->jsonHelper = $jsonHelper;
        $this->resource = $resource;
        $this->linkedAttributes = $linkedAttributes;
        parent::__construct($context);
    }

    /**
     * Convert option object/array data to specific array format
     * format: option data to $option[$optionId], value data to $option[$optionId]['values'][$valueId]
     *
     * @param array $options
     * @return array
     */
    public function beatifyOptions($options)
    {
        $array = [];
        if (empty($options)) {
            return $array;
        }

        foreach ($options as $optionKey => $option) {
            $array[$optionKey] = is_object($option) ? $option->getData() : $option;

            $values = [];
            if (isset($option['values'])) {
                $values = $option['values'];
            } elseif (is_object($option)) {
                $values = $option->getValues();
            }
            if (!$values) {
                continue;
            }
            foreach ($values as $valueKey => $value) {
                $array[$optionKey]['values'][$valueKey] = is_object($value) ? $value->getData() : $value;
            }
        }

        return $array;
    }

    /**
     * Search element of array by key and value
     *
     * @param string $key
     * @param string $value
     * @param array $array
     * @return string|null
     */
    public function searchArray($key, $value, $array)
    {
        foreach ($array as $k => $v) {
            if ($v[$key] === $value) {
                return $k;
            }
        }
        return null;
    }

    /**
     * Get options value qty based on the customers selection
     * Returns 1 by default
     *
     * @param $valueId
     * @param $valueData
     * @param QuoteItem $item
     * @param array $cart
     * @return float|int|mixed
     * @throws \Exception
     */
    public function getOptionValueQty($valueId, $valueData, QuoteItem $item, $cart = [])
    {
        if (empty($valueData['option_id'])) {
            throw new \Exception('Unable to locate the option id');
        }

        /** <!-- Change qty based on the customers input (qty input) --> */
        $itemQty = $item->getQty() ? $item->getQty() : 1;
        $itemQty = isset($cart[$item->getId()]) ? $cart[$item->getId()]['qty'] : $itemQty;
        $optionId = $valueData['option_id'];
        $productOption = $item->getProduct()->getOptionById($optionId);
        $isOneTime = (boolean)$productOption->getData('one_time');

        // Find base value's qty
        $valueQty = 1;
        if (!empty($itemInfo['options_qty'][$optionId][$valueId])) {
            $valueQty = $itemInfo['options_qty'][$optionId][$valueId];
        } elseif (!empty($itemInfo['options_qty'][$optionId]) && !is_array($itemInfo['options_qty'][$optionId])) {
            $valueQty = $itemInfo['options_qty'][$optionId];
        }

        // Multiply quantity by quantity of a product if there is no one-time option
        if (!$isOneTime) {
            $valueQty *= $itemQty;
        }

        return $valueQty;
    }


    /**
     * @param string $class
     * @return string
     */
    public function getLinkField($class)
    {
        $this->metadataPool = $this->objectManager->get('\Magento\Framework\EntityManager\MetadataPool');
        return $this->metadataPool->getMetadata($class)->getLinkField();
    }

    /**
     * Check Magento edition.
     *
     * @return boolean
     */
    public function isEnterprise()
    {
        return $this->productMetadata->getEdition() == 'Enterprise';
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleVersion($moduleName)
    {
        $path = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $directoryRead = $this->readFactory->create($path);
        $composerJsonData = $directoryRead->readFile('composer.json');
        $data = json_decode($composerJsonData);

        return !empty($data->version) ? $data->version : 0;
    }

    /**
     * Check module version according to conditions
     *
     * @param string $fromVersion
     * @param string $toVersion
     * @param string $fromOperator
     * @param string $toOperator
     * @param string $moduleName
     * @return string
     */
    public function checkModuleVersion(
        $fromVersion,
        $toVersion = '',
        $fromOperator = '>=',
        $toOperator = '<',
        $moduleName = 'Magento_Catalog'
    ) {
        if ($this->moduleVersion[$moduleName] === null) {
            $this->moduleVersion[$moduleName] = $this->getModuleVersion($moduleName);
        }

        $fromCondition = version_compare($this->moduleVersion[$moduleName], $fromVersion, $fromOperator);
        if ($toVersion === '') {
            return $fromCondition;
        }
        return $fromCondition && version_compare($this->moduleVersion[$moduleName], $toVersion, $toOperator);
    }

    /**
     * Return message about max_input_vars if form_key is not defined in request
     */
    public function checkMaxInputVars()
    {
        $data = $this->_getRequest()->getPostValue();
        if (!$data || !empty($data['form_key']) || ini_get('max_input_vars') >= 10000) {
            return;
        }

        if ($this->_getRequest()->getQuery('isAjax', false) || $this->_getRequest()->getQuery('ajax', false)) {
            $this->response->representJson(
                $this->jsonHelper->jsonEncode(
                    [
                        'error' => true,
                        'message' => __('Invalid Form Key. Please try to set "max_input_vars" directive to "10000"')
                    ]
                )
            );
        } else {
            $this->messageManager->addWarningMessage('Please try to set "max_input_vars" directive to "10000"');
        }
    }

    /**
     * Clear mageworx_id from all options and values.
     *
     * @param array $options
     * @return array
     */
    public function clearMageworxId($options)
    {
        foreach ($options as $oIndex => $option) {
            $options[$oIndex]['mageworx_option_id'] = '';

            $values = isset($option['values']) ? $option['values'] : [];
            if (!$values) {
                continue;
            }

            foreach ($values as $vIndex => $value) {
                $options[$oIndex]['values'][$vIndex]['mageworx_option_type_id'] = '';
            }
        }

        return $options;
    }

    /**
     * Convert mageworx_id to the record id in every dependent value.
     * Usually used with the clearMageworxId($options) method.
     *
     * @param array $options
     * @return array
     */
    public function convertDependentMageworxIdToRecordId($options)
    {
        foreach ($options as $oIndex => $option) {
            $values = isset($option['values']) ? $option['values'] : [];

            if (!$values) {
                continue;
            }

            foreach ($values as $vIndex => $value) {
                $dependencies = $value['field_hidden_dependency'] ? json_decode($value['field_hidden_dependency']) : null;

                if (!$dependencies) {
                    continue;
                }

                foreach ($dependencies as $dIndex => $dependency) {
                    $dependencies[$dIndex] = $this->replaceMWIdWithRecordId($dependency, $options);
                }

                $values[$vIndex]['field_hidden_dependency'] = json_encode($dependencies);
            }

            $options[$oIndex]['values'] = $values;
        }

        return $options;
    }

    /**
     * Replace mageworx_id with record_id in the dependencies.
     *
     * @param array $dependency
     * @param array $options
     * @return array
     */
    private function replaceMWIdWithRecordId($dependency, $options)
    {
        $dependencyOptionMageworxId = $dependency[0];
        $dependencyValueMageworxId = $dependency[1];

        foreach ($options as $oIndex => $option) {
            $mageworxOptionId = $option['mageworx_option_id'];

            if ($mageworxOptionId != $dependencyOptionMageworxId) {
                continue;
            }

            $dependency[0] = (isset($option['record_id']) && $option['record_id'] !== null) ?
                $option['record_id'] :
                $option['option_id']; // option_id is the record_id in the dynamic-row

            $values = isset($option['values']) ? $option['values'] : [];
            foreach ($values as $vIndex => $value) {
                $mageworxValueId = $value['mageworx_option_type_id'];

                if ($mageworxValueId != $dependencyValueMageworxId) {
                    continue;
                }

                $dependency[1] = $vIndex; // value record_id is the index number in the array
            }
        }

        return $dependency;
    }

    /**
     * Retrieve list of linked product attributes for OptionLink module.
     *
     * @param int|null $storeId
     * @return array
     */
    public function prepareLinkedAttributes($attributes)
    {
        $attributeName = \Magento\Catalog\Api\Data\ProductAttributeInterface::CODE_NAME;
        $attributePrice = \Magento\Catalog\Api\Data\ProductAttributeInterface::CODE_PRICE;

        $this->linkedAttributes += [
            $attributeName => $attributeName,
            $attributePrice => $attributePrice
        ];

        return array_intersect($this->linkedAttributes, $attributes);
    }

    /**
     * Get MageWorx option type IDs from conditions for collection updaters
     *
     * @param array $conditions
     * @return array
     */
    public function findMageWorxOptionTypeIdByConditions($conditions)
    {
        if (!empty($conditions['option_id'])) {
            $whereCondition = "option_id IN (" . implode(',', $conditions['option_id']) . ")";
        }

        $connection = $this->resource->getConnection();
        $sql = $connection->select()
            ->reset()
            ->distinct()
            ->from($this->getOptionValueTableName($conditions['entity_type']))
            ->where($whereCondition)
            ->columns('mageworx_option_type_id');
        $subselectResult = $connection->fetchAll($sql);

        $mageworxOptionTypeIds = [];
        foreach ($subselectResult as $subselectResultItem) {
            $mageworxOptionTypeIds[] = "'". $subselectResultItem['mageworx_option_type_id'] . "'";
        }
        return $mageworxOptionTypeIds;
    }

    /**
     * Get option value's table name by entity type
     *
     * @param string $entityType 'product' or 'group'
     * @return string
     */
    public function getOptionValueTableName($entityType)
    {
        if ($entityType == 'group') {
            return $this->resource->getTableName('mageworx_optiontemplates_group_option_type_value');
        }
        return $this->resource->getTableName('catalog_product_option_type_value');
    }
}
