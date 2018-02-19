<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Fieldset;
use MageWorx\OptionTemplates\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;

/**
 * Data provider for "Customizable Options" panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Templates extends AbstractModifier implements \MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier\ModifierInterface
{
    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param GroupCollectionFactory $groupCollectionFactory
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        GroupCollectionFactory $groupCollectionFactory
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->addTemplates();

        return $this->meta;
    }

    protected function addTemplates()
    {
        $options[] = [
            'value' => 'none',
            'label' => 'None'
        ];

        $groupCustomOptionsName =
            \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;

        $product = $this->locator->getProduct();
        $productId = $product->getId();
        /** @var \MageWorx\OptionTemplates\Model\ResourceModel\Group\Collection $collection */
        $collection = $this->groupCollectionFactory->create();
        $options = array_merge($options, $collection->toOptionArray());
        $values = $productId ? $collection->addProductFilter($productId)->getAllIds() : [];

        $this->meta[$groupCustomOptionsName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children'],
            [
                'option_groups' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => \Magento\Ui\Component\Form\Element\DataType\Text::NAME,
                                'formElement' => \Magento\Ui\Component\Form\Element\MultiSelect::NAME,
                                'componentType' => \Magento\Ui\Component\Form\Field::NAME,
                                'dataScope' => 'option_groups',
                                'id' => 'mageworx_product_group',
                                'label' => __('MageWorx Option Templates'),
                                'options' => $options,
                                'value' => $values,
                                'visible' => true,
                                'disabled' => false,
                                'sortOrder' => 5,
                            ],
                        ],
                    ],
                    'children' => [],
                ],
            ]
        );
    }

    /**
     * Check is current modifier for the product only
     *
     * @return bool
     */
    public function isProductScopeOnly()
    {
        return true;
    }
}
