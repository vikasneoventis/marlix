<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model;

use Magento\ConfigurableProduct\Model\Product\ReadHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface as OptionRepository;
use Magento\Framework\Webapi\Exception;
use \MageWorx\OptionTemplates\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use \Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterRegistry;

class OptionSaver
{
    const KEY_NEW_PRODUCT = 'new';

    const KEY_UPD_PRODUCT = 'upd';

    const KEY_DEL_PRODUCT = 'del';

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $productOptionConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \MageWorx\OptionTemplates\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * Array contain all group option ids, that were added to personal product
     *
     * @var array
     */
    protected $productGroupNewOptionIds = [];

    /**
     * @var \MageWorx\OptionTemplates\Model\Group
     */
    protected $group;

    /**
     *
     * @var array
     */
    protected $deletedGroupOptions;

    /**
     *
     * @var array
     */
    protected $addedGroupOptions;

    /**
     *
     * @var array
     */
    protected $intersectedOptions;

    /**
     *
     * @var array
     */
    protected $products = [];

    /**
     * Array of modified options and modified/added option values
     *
     * @var array
     */
    protected $modifiedUpGroupOptions;

    /**
     * Array of deleted option values
     *
     * @var array
     */
    protected $modifiedDownGroupOptions;

    /**
     * Added product option values to template options
     * NEED to be deleted after template re-applying
     * @var array
     */
    protected $addedProductValues;

    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory
     */
    protected $customOptionFactory;

    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array|null
     */
    protected $groupOptions;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \MageWorx\OptionTemplates\Model\Group\Source\SystemAttributes
     */
    protected $systemAttributes;

    /**
     * @var array|null
     */
    protected $oldGroupCustomOptions;

    /**
     * @var array
     */
    protected $oldGroupCustomOptionValues;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var CollectionUpdaterRegistry
     */
    protected $collectionUpdaterRegistry;

    /**
     *
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     * @param \MageWorx\OptionTemplates\Model\GroupFactory $groupFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory
     * @param OptionRepository $optionRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param ManagerInterface $eventManager
     * @param ResourceConnection $resource
     * @param CollectionUpdaterRegistry $collectionUpdaterRegistry
     */
    public function __construct(
        ReadHandler $readHandler,
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
        \MageWorx\OptionTemplates\Model\GroupFactory $groupFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory,
        OptionRepository $optionRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Psr\Log\LoggerInterface $logger,
        Helper $helper,
        BaseHelper $baseHelper,
        \MageWorx\OptionTemplates\Model\Group\Source\SystemAttributes $systemAttributes,
        \MageWorx\OptionBase\Model\Entity\Group $groupEntity,
        \MageWorx\OptionBase\Model\Entity\Product $productEntity,
        ManagerInterface $eventManager,
        CollectionUpdaterRegistry $collectionUpdaterRegistry,
        ResourceConnection $resource
    ) {
        $this->readHandler = $readHandler;
        $this->productOptionConfig = $productOptionConfig;
        $this->groupFactory = $groupFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customOptionFactory = $customOptionFactory;
        $this->optionRepository = $optionRepository;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->baseHelper = $baseHelper;
        $this->logger = $logger;
        $this->systemAttributes = $systemAttributes;
        $this->groupEntity = $groupEntity;
        $this->productEntity = $productEntity;
        $this->eventManager = $eventManager;
        $this->collectionUpdaterRegistry = $collectionUpdaterRegistry;
        $this->resource = $resource;
    }

    public function saveProductOptions(Group $group, $oldGroupCustomOptions)
    {
        $this->products[self::KEY_NEW_PRODUCT] = $group->getNewProductIds();
        $this->products[self::KEY_UPD_PRODUCT] = $group->getUpdProductIds();
        $this->products[self::KEY_DEL_PRODUCT] = $group->getDelProductIds();
        $this->oldGroupCustomOptions = $oldGroupCustomOptions;
        $this->oldGroupCustomOptionValues = $this->getOptionValues($this->oldGroupCustomOptions);

        $allProductIds = $group->getAffectedProductIds();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $this->collectionUpdaterRegistry->setCurrentEntityType('product');
        $this->collectionUpdaterRegistry->setCurrentEntityId(0);
        $collection->addStoreFilter(0)
            ->setStoreId(0)
            ->addFieldToFilter('entity_id', ['in' => $allProductIds])
            ->addOptionsToResult(); // by default the product (collections item) has no options data

        /** Reload model for using new option ids **/
        /** @var Group group */
        $this->group = $this->groupFactory->create()->load($group->getId());
        $this->groupOptions = $this->groupEntity->getOptionsAsArray($this->group);

        $this->deletedGroupOptions = $this->getGroupDeletedOptions();
        $this->addedGroupOptions = $this->getGroupAddedOptions();
        $this->intersectedOptions = $this->getGroupIntersectedOptions();
        $groupNewModifiedOptions = $this->getGroupNewModifiedOptions();
        $groupLastModifiedOptions = $this->getGroupLastModifiedOptions();
        $this->modifiedUpGroupOptions = $this->arrayDiffRecursive($groupNewModifiedOptions, $groupLastModifiedOptions);
        $this->modifiedDownGroupOptions = $this->arrayDiffRecursive(
            $groupLastModifiedOptions,
            $groupNewModifiedOptions
        );

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $customOptions = [];
            $this->clearProductGroupNewOptionIds();
            $product->setStoreId(0);
            $preparedProductOptionArray = $this->getPreparedProductOptions($product);

            try {
                foreach ($preparedProductOptionArray as $preparedOption) {
                    /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
                    if (is_object($preparedOption)) {
                        $customOption = $this->customOptionFactory->create(['data' => $preparedOption->getData()]);
                        $id = $preparedOption->getData('id');
                        $values = $preparedOption->getValues();
                    } elseif (is_array($preparedOption)) {
                        $customOption = $this->customOptionFactory->create(['data' => $preparedOption]);
                        $id = $preparedOption['id'];
                        $values = !empty($preparedOption['values']) ? $preparedOption['values'] : [];
                    } else {
                        throw new Exception(__('The prepared option is not an instance of DataObject or array. %1 is received', gettype($preparedOption)));
                    }

                    $customOption->setProductSku($product->getSku())
                        ->setOptionId($id)
                        ->setValues($values);
                    $customOptions[] = $customOption;
                }
                if (!empty($customOptions)) {
                    $product->setOptions($customOptions);
                    $product->setCanSaveCustomOptions(true);
                    $this->saveOptionsInProduct($product);
                }

                $this->updateProductData($product);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }

            $this->doProductRelationAction($product->getId());
        }
    }

    /**
     * Get values from options
     *
     * @param array|null $options
     * @return array $values
     */
    protected function getOptionValues($options)
    {
        $values = [];
        if (empty($options)) {
            return $values;
        }

        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $valueKey => $value) {
                $values[$valueKey] = $value;
            }
        }
        return $values;
    }


    /**
     * Transfer product based custom options attributes from group to the corresponding product
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function updateProductData($product)
    {
        $excludeAttributes = $this->systemAttributes->toArray();
        $groupData = $this->group->getData();
        foreach ($excludeAttributes as $attribute) {
            unset($groupData[$attribute]);
        }

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $this->readHandler->execute($product);
        }

        $product->addData($groupData);
        if ($product->getOptions()) {
            $this->updateHasOptionsStatus($product);
            $product->setHasOptions(1);
        }

        $product->setIsAfterTemplateSave(true);

        $this->eventManager->dispatch('mageworx_attributes_save_trigger', ['product' => $product]);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    protected function updateHasOptionsStatus($product)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('catalog_product_entity');
        $data = [
            'has_options' => 1,
        ];
        $linkField = $product->getResource()->getLinkField();
        $sql = $linkField." = '".$product->getData($linkField)."'";
        $connection->update($tableName, $data, $sql);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function saveOptionsInProduct($product)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        foreach ($this->optionRepository->getProductOptions($product) as $option) {
            $this->optionRepository->delete($option);
        }

        if ($product->getOptions()) {
            foreach ($product->getOptions() as $option) {
                if ($option->getData('is_delete') == true) {
                    continue;
                }
                //compatibility for 2.2.x
                if ($this->baseHelper->checkModuleVersion('101.0.10')) {
                    $option->setOptionId(null);
                    if (!empty($option['values'])) {
                        $values = [];
                        foreach ($option['values'] as $valueKey => $value) {
                            $value['option_type_id'] = null;
                            $values[$valueKey] = $value;
                        }
                        $option->setValues($values);
                    }
                    if (!empty($option->getData('values'))) {
                        $values = [];
                        foreach ($option->getData('values') as $valueKey => $value) {
                            $value['option_type_id'] = null;
                            $values[$valueKey] = $value;
                        }
                        $option->setData('values', $values);
                    }
                }
                $this->optionRepository->save($option);
            }
        }

        return $product;
    }

    /**
     * @return void
     */
    protected function clearProductGroupNewOptionIds()
    {
        $this->productGroupNewOptionIds = [];
    }

    /**
     *
     * @return array
     */
    protected function getGroupDeletedOptions()
    {
        return array_diff_key($this->oldGroupCustomOptions, $this->groupOptions);
    }

    /**
     *
     * @return array
     */
    protected function getGroupAddedOptions()
    {
        return array_diff_key($this->groupOptions, $this->oldGroupCustomOptions);
    }

    /**
     *
     * @return array
     */
    protected function getGroupIntersectedOptions()
    {
        return array_intersect_key($this->groupOptions, $this->oldGroupCustomOptions);
    }

    /**
     *
     * @return array
     */
    protected function getGroupNewModifiedOptions()
    {
        $intersectedGroupOptionIds = array_keys($this->getGroupIntersectedOptions($this->oldGroupCustomOptions));
        $prepareNewGroupOptions = [];

        foreach ($intersectedGroupOptionIds as $optionId) {
            if (!empty($this->groupOptions[$optionId])) {
                $prepareNewGroupOptions[$optionId] = $this->groupOptions[$optionId];
            }
        }

        return $prepareNewGroupOptions;
    }

    /**
     *
     * @return array
     */
    protected function getGroupLastModifiedOptions()
    {
        $intersectedGroupOptionIds = array_keys($this->getGroupIntersectedOptions($this->oldGroupCustomOptions));
        $prepareLastGroupOptions = [];

        foreach ($intersectedGroupOptionIds as $optionId) {
            if (!empty($this->oldGroupCustomOptions[$optionId])) {
                $prepareLastGroupOptions[$optionId] = $this->oldGroupCustomOptions[$optionId];
            }
        }

        return $prepareLastGroupOptions;
    }

    /**
     * Retrieve new product options as array, that were built by group modification
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getPreparedProductOptions($product)
    {
        $productOptions = $this->productEntity->getOptionsAsArray($product);

        $ids = [];
        foreach ($this->groupOptions as $groupKey => $groupValue) {
            $ids[$groupKey] = $groupValue;
        }
        foreach ($productOptions as $productOption) {
            if (empty($ids[$productOption['group_option_id']]) || empty($productOption['values'])) {
                continue;
            }
            foreach ($productOption['values'] as $valueKey => $valueData) {
                if (empty($valueData['group_option_value_id'])) {
                    $this->addedProductValues[$productOption['group_option_id']]['values'][$valueKey] = $valueData;
                }
            }
        }

        if ($this->isNewProduct($product->getId())) {
            $productOptions = $this->addNewOptionProcess($productOptions);
        } elseif ($this->isUpdProduct($product->getId())) {
            $productOptions = $this->deleteOptionProcess($productOptions);
            $productOptions = $this->addNewOptionProcess($productOptions);
            $productOptions = $this->modifyOptionProcess($productOptions);
        } elseif ($this->isDelProduct($product->getId())) {
            $productOptions = $this->deleteOptionProcess($productOptions);
        }

        return $productOptions;
    }

    /**
     * Delete options that were deleted in group
     *
     * @todo Delete All product option with group_option_id that missed in group.
     * @param array $productOptions
     * @param null $group
     * @return array
     */
    public function deleteOptionProcess(array $productOptions, $group = null)
    {
        if ($group === null) {
            $deletedGroupOptionIds = array_keys($this->deletedGroupOptions);
        } else {
            $groupOptions = $this->groupEntity->getOptionsAsArray($group);
            $deletedGroupOptionIds = array_keys($groupOptions);
        }

        foreach ($productOptions as $key => $productOption) {
            if (!empty($productOption['group_option_id']) &&
                in_array($productOption['group_option_id'], $deletedGroupOptionIds)
            ) {
                $productOption['is_delete'] = '1';
                $productOptions[$key] = $productOption;
            }
        }

        return $productOptions;
    }

    /**
     * Delete all group options
     *
     * @param array $productOptions
     * @return array
     */
    protected function clearOptionProcess(array $productOptions)
    {
        foreach ($productOptions as $key => $productOption) {
            if (empty($productOption['group_option_id'])) {
                continue;
            }
            foreach ($this->group->getOptions() as $option) {
                if ($productOption['group_option_id'] == $option->getData('option_id')) {
                    $productOptions[$key]['is_delete'] = '1';
                }
            }
        }

        return $productOptions;
    }

    /**
     * Modify options that were modified in group
     *
     * @param array $productOptions
     * @return array
     */
    protected function modifyOptionProcess(array $productOptions)
    {
        foreach ($productOptions as $productOptionId => $productOption) {
            $groupOptionId = !empty($productOption['group_option_id']) ? $productOption['group_option_id'] : null;
            if (!$groupOptionId) {
                continue;
            }
            if ($this->isOptionWereRecreated($groupOptionId)) {
                continue;
            }
            if (!empty($this->modifiedDownGroupOptions[$groupOptionId])) {
                foreach ($this->modifiedDownGroupOptions[$groupOptionId] as $modPropertyKey => $modProperty) {
                    if ($modPropertyKey == 'values' && is_array($modProperty)) {
                        /**
                         * @todo is corresponding product option another type? we must recreate it early maybe.
                         */
                        if (empty($productOptions[$productOptionId][$modPropertyKey])) {
                            $productOptions[$productOptionId][$modPropertyKey] = [];
                        }

                        foreach ($modProperty as $valueId => $valueData) {
                            //Option value were deleted in group - delete it in corresponding product option
                            if (!empty($valueData['option_type_id'])) {
                                $productOptions[$productOptionId][$modPropertyKey] =
                                    $this->markProductOptionValueAsDelete(
                                        $productOptions[$productOptionId][$modPropertyKey],
                                        $valueData['option_type_id'],
                                        'group_option_value_id'
                                    );
                            } else {
                                $productOptions[$productOptionId][$modPropertyKey] =
                                    $this->getModifyProductOptionValue(
                                        $productOptions[$productOptionId][$modPropertyKey],
                                        $valueId,
                                        $valueData
                                    );
                            }
                        }
                    } elseif (!is_array($modProperty)) {
                        unset($productOptions[$productOptionId][$modPropertyKey]);
                    }
                }
            }

            if (!empty($this->modifiedUpGroupOptions[$groupOptionId])) {
                foreach ($this->modifiedUpGroupOptions[$groupOptionId] as $modPropertyKey => $modProperty) {
                    if ($modPropertyKey == 'values' && is_array($modProperty)) {
                        /**
                         * @todo is corresponding product option another type? we must recreate it early maybe.
                         */
                        if (empty($productOptions[$productOptionId][$modPropertyKey])) {
                            $productOptions[$productOptionId][$modPropertyKey] = [];
                        }

                        foreach ($modProperty as $valueId => $valueData) {
                            if (!empty($valueData['option_type_id'])) {
                                $productOptions[$productOptionId][$modPropertyKey][] =
                                    $this->convertGroupOptionValueToProductOptionValue(
                                        $valueData,
                                        $productOptionId,
                                        $productOptions[$productOptionId][$modPropertyKey]
                                    );
                            } else {
                                $productOptions[$productOptionId][$modPropertyKey] =
                                    $this->getModifyProductOptionValue(
                                        $productOptions[$productOptionId][$modPropertyKey],
                                        $valueId,
                                        $valueData
                                    );
                            }
                        }
                    } elseif (!is_array($modProperty)) {
                        $productOptions[$productOptionId][$modPropertyKey] = $modProperty;
                    }
                }
            }

            if (!empty($this->addedProductValues[$groupOptionId])) {
                foreach ($this->addedProductValues[$groupOptionId] as $modPropertyKey => $modProperty) {
                    if ($modPropertyKey == 'values' && is_array($modProperty)) {
                        if (empty($productOptions[$productOptionId][$modPropertyKey])) {
                            continue;
                        }

                        foreach ($modProperty as $valueId => $valueData) {
                            //delete product option value that was added to template option
                            if (empty($valueData['option_type_id'])) {
                                continue;
                            }
                            $productOptions[$productOptionId][$modPropertyKey] =
                                $this->markProductOptionValueAsDelete(
                                    $productOptions[$productOptionId][$modPropertyKey],
                                    $valueData['option_type_id'],
                                    'option_type_id'
                                );
                        }
                    }
                }
            }
        }

        return $productOptions;
    }

    /**
     * Add new options that were added in group
     *
     * @param array $productOptions
     * @param Group|null
     * @return array
     */
    public function addNewOptionProcess(array $productOptions, $group = null)
    {
        if ($group === null) {
            $groupOptions = $this->groupOptions;
        } else {
            $groupOptions = $this->groupEntity->getOptionsAsArray($group);
        }

        $newProductOptions = [];

        $i = $productOptions ? max(array_keys($productOptions)) + 1 : 1;

        foreach ($groupOptions as $groupOption) {
            $issetGroupOptionInProduct = false;

            foreach ($productOptions as $productOption) {
                if (!empty($productOption['group_option_id'])
                    && $productOption['group_option_id'] == $groupOption['option_id']
                ) {
                    $issetGroupOptionInProduct = true;
                }
            }

            if (!$issetGroupOptionInProduct) {
                $groupOption['group_option_id'] = $groupOption['id'];
                $groupOption['id'] = (string)$i;
                $groupOption['option_id'] = '0';

                $groupOption = $this->convertGroupToProductOptionValues($groupOption);
                $newProductOptions[$i] = $groupOption;
                $this->productGroupNewOptionIds[] = $groupOption['group_option_id'];
            }
            $i++;
        }

        return $productOptions + $newProductOptions;
    }

    /**
     *
     * @param array $option
     * @return array
     */
    protected function convertGroupToProductOptionValues($option)
    {
        if (!empty($option['values'])) {
            foreach ($option['values'] as $valueKey => $value) {
                $value['group_option_value_id'] = $value['option_type_id'];
                $value['option_type_id'] = '-1';
                $option['values'][$valueKey] = $value;
            }
        }

        return $option;
    }

    /**
     *
     * @param int $productId
     */
    protected function doProductRelationAction($productId)
    {
        if ($this->isNewProduct($productId)) {
            $this->group->addProductRelation($productId);
        } elseif ($this->isDelProduct($productId)) {
            $this->group->deleteProductRelation($productId);
        }
    }

    /**
     *
     * @param int $productId
     * @return boolean
     */
    protected function isNewProduct($productId)
    {
        return in_array($productId, $this->products[self::KEY_NEW_PRODUCT]);
    }

    /**
     *
     * @param int $productId
     * @return boolean
     */
    protected function isUpdProduct($productId)
    {
        return in_array($productId, $this->products[self::KEY_UPD_PRODUCT]);
    }

    /**
     *
     * @param int $productId
     * @return boolean
     */
    protected function isDelProduct($productId)
    {
        return in_array($productId, $this->products[self::KEY_DEL_PRODUCT]);
    }

    /**
     * Check if different options types
     *
     * @param string $typeOld
     * @param string $typeNew
     * @return bool
     */
    protected function isSameOptionGroupType($typeOld, $typeNew)
    {
        return ($this->getOptionGroupType($typeOld) == $this->getOptionGroupType($typeNew));
    }

    /**
     *
     * @param string $name
     * @return string
     */
    protected function getOptionGroupType($name)
    {
        foreach ($this->productOptionConfig->getAll() as $typeName => $data) {
            if (!empty($data['types'][$name])) {
                return $typeName;
            }
        }

        return null;
    }

    /**
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    protected function arrayDiffRecursive($arr1, $arr2)
    {
        $outputDiff = [];

        foreach ($arr1 as $key => $value) {
            if (is_array($arr2) && array_key_exists($key, $arr2)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->arrayDiffRecursive($value, $arr2[$key]);
                    if (count($recursiveDiff)) {
                        $outputDiff[$key] = $recursiveDiff;
                    }
                } elseif ($arr2[$key] != $value) {
                    $outputDiff[$key] = $value;
                }
            } else {
                $outputDiff[$key] = $value;
            }
        }

        return $outputDiff;
    }

    /**
     * Check if option was recreated
     *
     * @param string $groupOptionId
     * @return bool
     */
    protected function isOptionWereRecreated($groupOptionId)
    {
        return in_array($groupOptionId, $this->productGroupNewOptionIds);
    }

    /**
     * Convert group option value to product option value, keep changed attributes from config (qty, for example)
     *
     * @param array $groupOptionValueData
     * @param int $productOptionId
     * @param array $productOptionValues
     * @return string
     */
    protected function convertGroupOptionValueToProductOptionValue(array $groupOptionValueData, $productOptionId, $productOptionValues)
    {
        $groupOptionValueData['option_id'] = (string)$productOptionId;
        $groupOptionValueData['group_option_value_id'] = $groupOptionValueData['option_type_id'];
        $groupOptionValueData['option_type_id'] = '-1';

        foreach ($productOptionValues as $optionValue) {
            if (empty($optionValue['group_option_value_id'])) {
                continue;
            }
            if (empty($this->oldGroupCustomOptionValues[$optionValue['group_option_value_id']])) {
                continue;
            }
            if (empty($this->oldGroupCustomOptionValues[$optionValue['group_option_value_id']]['mageworx_group_option_type_id'])) {
                continue;
            }
            $linkedMageworxOptionId = $this->oldGroupCustomOptionValues[$optionValue['group_option_value_id']]['mageworx_group_option_type_id'];
            if ($linkedMageworxOptionId != $groupOptionValueData['mageworx_group_option_type_id']) {
                continue;
            }
            foreach ($this->helper->getReapplyExceptionAttributeKeys() as $attribute) {
                if (!isset($optionValue[$attribute])) {
                    continue;
                }
                $oldOptionValueData = $this->oldGroupCustomOptionValues[$optionValue['group_option_value_id']][$attribute];
                if ($oldOptionValueData == $optionValue[$attribute]) {
                    continue;
                }
                $groupOptionValueData[$attribute] = $optionValue[$attribute];
            }
        }

        return $groupOptionValueData;
    }

    /**
     * Mark 'delete' a product option value by deleted group option value
     *
     * @param array $productOptionValueArray
     * @param int $valueId
     * @param string $linkKey
     * @return array
     */
    protected function markProductOptionValueAsDelete(array $productOptionValueArray, $valueId, $linkKey)
    {
        foreach ($productOptionValueArray as $optionValueKey => $optionData) {
            if (!empty($optionData[$linkKey]) &&
                $valueId == $optionData[$linkKey]
            ) {
                $productOptionValueArray[$optionValueKey]['is_delete'] = '1';
                break;
            }
        }

        return $productOptionValueArray;
    }

    /**
     * Modify/add product option value properties by modified group option value properties
     *
     *
     * @param array $productOptionValueArray
     * @param int $groupOptionValueId
     * @param array $valueData
     * @return array
     */
    protected function getModifyProductOptionValue(array $productOptionValueArray, $groupOptionValueId, $valueData)
    {
        foreach ($productOptionValueArray as $optionValueKey => $optionValue) {
            if (!empty($optionValue['group_option_value_id']) &&
                $groupOptionValueId == $optionValue['group_option_value_id']
            ) {
                foreach ($valueData as $key => $value) {
                    $productOptionValueArray[$optionValueKey][$key] = $value;
                }
                break;
            }
        }

        return $productOptionValueArray;
    }
}
