<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionDependency\Ui\DataProvider\Product\Form\Modifier;

use \MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier\ModifierInterface;
use \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Stdlib\ArrayManager;
use \Magento\Catalog\Model\Locator\LocatorInterface;
use \Magento\Ui\Component\Container;
use \Magento\Ui\Component\Form\Fieldset;
use \Magento\Ui\Component\Modal;
use \Magento\Ui\Component\Form\Element\DataType\Text;
use \Magento\Ui\Component\Form\Element\Hidden;
use \Magento\Ui\Component\Form\Element\Select;
use \Magento\Ui\Component\Form\Element\Input;
use \Magento\Ui\Component\Form\Field;
use \Magento\Ui\Component\Form;
use \MageWorx\OptionDependency\Helper\Data as Helper;
use \MageWorx\OptionBase\Helper\Data as HelperBase;
use \Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Class DisableFields. Update custom options grid in product edit page.
 * Add 'sku_is_valid' hidden field.
 * Update 'disabled' attribute on some option values fields.
 */
class Dependency extends AbstractModifier implements ModifierInterface
{
    const CUSTOM_MODAL_LINK = 'custom_modal_link';
    const DEPENDENCY_MODAL_INDEX = 'dependency_modal';
    const DEPNDENCY_MODAL_CONTENT = 'content';
    const DEPENDENCY_MODAL_FIELDSET = 'fieldset';

    const DEPENDENCIES_DYNAMIC_ROW = 'dependencies_dynamic_row';

    const BUTTON_DEPENDENCY_NAME = 'button_dependency';
    const FIELD_HIDDEN_DEPENDENCY_NAME = 'field_hidden_dependency';
    const FIELD_HIDDEN_MAGEWORX_OPTION_ID = 'mageworx_option_id';
    const FIELD_HIDDEN_MAGEWORX_OPTION_TYPE_ID = 'mageworx_option_type_id';
    const FIELD_OPTION_TYPE_TITLE_ID = 'option_type_title_id';
    const FIELD_OPTION_TITLE_ID = 'option_title_id';

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Helper
     */
    protected $helperBase;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var array
     */
    protected $meta = [];

    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        Helper $helper,
        HelperBase $helperBase,
        HttpRequest $request
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->helper = $helper;
        $this->helperBase = $helperBase;
        $this->request = $request;
    }

    public function modifyData(array $data)
    {
        if (!$this->isSchedule()) {
            return $data;
        }

        $productId = $this->locator->getProduct()->getId();
        $productOptions = isset($data[$productId]['product']['options']) ? $data[$productId]['product']['options'] : [];

        // convert mageworx_option_id to record_id in the dependencies
        $productOptions = $this->helperBase->convertDependentMageworxIdToRecordId($productOptions);
        $data[$productId]['product']['options'] = $productOptions;

        return $data;
    }

    private function isSchedule()
    {
        if ($this->request->getParam('handle') != 'catalogstaging_update') {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        if ($this->helper->isTitleIdEnabled()) {
            $this->addOptionTitleId();
            $this->addOptionTypeTitleId();
        }
        $this->addDependencyModal();
        $this->addDependencyButton();
        $this->addHiddenDependencyField();
        $this->addMageworxOptionId();
        $this->addMageworxOptionTypeId();

        return $this->meta;
    }

    /**
     * Add modal windows for configure dependencies.
     */
    protected function addDependencyModal()
    {
        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::DEPENDENCY_MODAL_INDEX => $this->getModalConfig(),
            ]
        );
    }

    /**
     * Retrieve array of settings of modal window.
     * Add Dynamic Row component to modal window.
     *
     * @return array
     */
    protected function getModalConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'MageWorx_OptionDependency/component/modal-component',
                        'componentType' => Modal::NAME,
                        'dataScope' => static::DEPENDENCIES_DYNAMIC_ROW,
                        'provider' => static::FORM_NAME . '.product_form_data_source',
                        'ns' => static::FORM_NAME,
                        'indexies' => [
                            'dependencies_dynamic_row' => static::DEPENDENCIES_DYNAMIC_ROW,
                        ],
                        'options' => [
                            'title' => __('Dependency'),
                            'buttons' => [
                                [
                                    'text' => __('Save & Close'),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        'saveDependencyData',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                static::DEPNDENCY_MODAL_CONTENT => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => false,
                                'componentType' => 'container',
                                'dataScope' => 'data.product',
                                'externalProvider' => 'data.product_data_source',
                                'ns' => static::FORM_NAME,
                                'behaviourType' => 'edit',
                                'externalFilterMode' => true,
                                'currentProductId' => $this->locator->getProduct()->getId(),
                            ],
                        ],
                    ],
                    'children' => [
                        static::DEPENDENCY_MODAL_FIELDSET => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'label' => __('Dependency'),
                                        'componentType' => Form::NAME,
                                        'dataScope' => 'custom_data',
                                        'collapsible' => true,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                static::DEPENDENCIES_DYNAMIC_ROW => $this->getCustomOptionsStructure(10),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Retrieve config of Dynamic Row component.
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getCustomOptionsStructure($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'productProvider' => 'product_form.product_form',
                        'optionsProvider' => 'source.data.product.options',
                        'dependencyContainer' => static::FIELD_HIDDEN_DEPENDENCY_NAME,
                        'component' => 'MageWorx_OptionDependency/component/dynamic-rows',
                        'componentType' => 'dynamicRows',
                        'defaultLabel' => __('Add Parent Option(s)'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'option_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'productProvider' => 'product_form.product_form',
                                        'optionsProvider' => 'source.data.product.options',
                                        'dependencyModalIndex' => static::DEPENDENCIES_DYNAMIC_ROW,
                                        'component' => 'MageWorx_OptionDependency/component/option-select',
                                        'isSchedule' => $this->isSchedule(),
                                        'dataType' => Text::NAME,
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataScope' => 'option_id',
                                        'label' => __('Option'),
                                        'additionalClasses' => 'admin__field-large',
                                        'isTitleIdEnabled' => (int)$this->helper->isTitleIdEnabled()
                                    ],
                                ],
                            ],
                        ],
                        'value_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'dataScope' => 'value_id',
                                        'label' => __('Value'),
                                        'additionalClasses' => 'admin__field-large',
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Add 'Dependency' button to options.
     */
    protected function addDependencyButton()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;

        // add 'Dependency' button to option values
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        ['container_option']['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            ['container_option']['children']['values']['children']['record']['children'],
            $this->getDependencyButton(210)
        );

        // add 'Dependency' button to options
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        ['container_option']['children'][CustomOptions::CONTAINER_TYPE_STATIC_NAME]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            ['container_option']['children'][CustomOptions::CONTAINER_TYPE_STATIC_NAME]['children'],
            $this->getDependencyButton(120, true)
        );
    }

    /**
     * Retrieve array of settings of 'Dependency' button.
     *
     * @param int $sortOrder
     * @param bool $additionalForGroup
     * @return array
     */
    protected function getDependencyButton($sortOrder, $additionalForGroup = false)
    {
        $field[static::BUTTON_DEPENDENCY_NAME] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'labelVisible' => true,
                        'label' => ' ',
                        'title' => __('Dependency'),
                        'dataScope' => static::BUTTON_DEPENDENCY_NAME,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'component' => 'MageWorx_OptionDependency/component/button',
                        'additionalForGroup' => $additionalForGroup,
                        'displayArea' => 'insideGroup',
                        'template' => 'ui/form/components/button/container',

                        'actions' => [
                            [
                                'targetName' => 'dataScope = ' . static::DEPENDENCIES_DYNAMIC_ROW
                                    . '.data.product.custom_data',
                                'actionName' => 'setOptionData',
                                'params' => [
                                    [
                                        'provider' => '${ $.provider }',
                                        'parentScope' => '${ $.parentScope }'
                                    ],
                                ],
                            ],
                            [
                                'targetName' => 'ns=' . static::FORM_NAME . ', index='
                                    . static::DEPENDENCY_MODAL_INDEX,
                                'actionName' => 'openModal',
                            ],
                        ],
                        'displayAsLink' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];

        return $field;
    }

    /**
     * Add hidden 'Dependency' field to options.
     */
    protected function addHiddenDependencyField()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;

        // add 'Dependency' button to option values
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children'],
            $this->getHiddenDependencyField(220)
        );

        // add 'Dependency' button to options
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        ['container_option']['children'][CustomOptions::CONTAINER_TYPE_STATIC_NAME]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            ['container_option']['children'][CustomOptions::CONTAINER_TYPE_STATIC_NAME]['children'],
            $this->getHiddenDependencyField(130, true)
        );
    }

    /**
     * Retrieve array of settings of hidden 'Dependency' field.
     *
     * @param int $sortOrder
     * @param bool $additionalForGroup
     * @return array
     */
    protected function getHiddenDependencyField($sortOrder, $additionalForGroup = false)
    {
        $field[static::FIELD_HIDDEN_DEPENDENCY_NAME] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Hidden::NAME,
                        'dataScope' => static::FIELD_HIDDEN_DEPENDENCY_NAME,
                        'dataType' => Text::NAME,
                        'additionalForGroup' => $additionalForGroup,
                        'displayArea' => 'insideGroup',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];

        return $field;
    }

    /**
     * Add 'mageworx_option_id' field to options.
     */
    protected function addMageworxOptionId()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;

        // add mageworx_option_id to options
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        ['container_option']['children'][CustomOptions::CONTAINER_TYPE_STATIC_NAME]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            ['container_option']['children'][CustomOptions::CONTAINER_TYPE_STATIC_NAME]['children'],
            $this->getMageworxOptionId(140)
        );
    }

    /**
     * Get mageworx_option_id field config .
     * @param $sortOrder
     */
    protected function getMageworxOptionId($sortOrder)
    {
        $field[static::FIELD_HIDDEN_MAGEWORX_OPTION_ID] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Hidden::NAME,
                        'dataScope' => static::FIELD_HIDDEN_MAGEWORX_OPTION_ID,
                        'dataType' => Text::NAME,
                        'additionalForGroup' => true,
                        'displayArea' => 'insideGroup',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];

        return $field;
    }

    /**
     * Add 'mageworx_option_type_id' field to options.
     */
    protected function addMageworxOptionTypeId()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;

        // add mageworx_option_type_id to option values
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children'],
            $this->getMageworxOptionTypeId(230)
        );
    }

    /**
     * Get mageworx_option_type_id field config .
     * @param $sortOrder
     */
    protected function getMageworxOptionTypeId($sortOrder)
    {
        $field[static::FIELD_HIDDEN_MAGEWORX_OPTION_TYPE_ID] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Hidden::NAME,
                        'dataScope' => static::FIELD_HIDDEN_MAGEWORX_OPTION_TYPE_ID,
                        'dataType' => Text::NAME,
                        'displayArea' => 'insideGroup',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];

        return $field;
    }

    /**
     * Add 'option_title_id' field to options.
     */
    protected function addOptionTitleId()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;

        //add option_title_id to options
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $this->getOptionTitleId(21)
        );
    }

    /**
     * Get 'option_title_id' field config .
     * @param $sortOrder
     */
    protected function getOptionTitleId($sortOrder)
    {
        $field[static::FIELD_OPTION_TITLE_ID] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => 'Title ID',
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_OPTION_TITLE_ID,
                        'dataType' => Text::NAME,
                        'fit' => true,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];

        return $field;
    }

    /**
     * Add 'option_type_title_id' field to options.
     */
    protected function addOptionTypeTitleId()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;

        //add option_type_title_id to option values
        $titleConfig = $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children']['title'];
        unset($this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children']['title']);
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            ['title' => $titleConfig],
            $this->getOptionTypeTitleId(11),
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children']
        );
    }

    /**
     * Get 'option_type_title_id' field config .
     * @param $sortOrder
     */
    protected function getOptionTypeTitleId($sortOrder)
    {
        $field[static::FIELD_OPTION_TYPE_TITLE_ID] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => 'Title ID',
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_OPTION_TYPE_TITLE_ID,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];

        return $field;
    }

    /**
     * Check is current modifier for the product only
     *
     * @return bool
     */
    public function isProductScopeOnly()
    {
        return false;
    }
}
