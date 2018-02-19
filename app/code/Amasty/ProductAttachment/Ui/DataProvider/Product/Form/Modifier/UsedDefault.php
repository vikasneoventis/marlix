<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form;

class UsedDefault extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var scopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param ScopeConfigInterface $scopeConfig
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ScopeConfigInterface $scopeConfig,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->scopeConfig = $scopeConfig;
        $this->arrayManager = $arrayManager;
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
        if ($this->locator->getProduct()->getStoreId()) {
            $this
                ->usedDefaultInGrid('label')
                ->usedDefaultInGrid('customer_group')
                ->usedDefaultInGrid('show_for_ordered')
                ->usedDefaultInGrid('is_visible')
            ;
        }

        return $this->meta;
    }


    /**
     * @param $index
     *
     * @return $this
     */
    protected function usedDefaultInGrid($index)
    {
        $linkGroupPath = $this->arrayManager->findPath(
            'container_' . $index,
            $this->meta,
            null,
            'children'
        );
        $checkboxPath = $linkGroupPath . '/children/use_default_'.$index.'/arguments/data/config';
        $useDefaultConfig = [
            'componentType' => Form\Element\Checkbox::NAME,
            'formElement' => Form\Field::NAME,
            'description' => __('Use Default Value'),
            'dataScope' => 'use_default_' . $index,
            'valueMap' => [
                'false' => '0',
                'true' => '1',
            ],
            'exports' => [
                'checked' => '${$.parentName}.' . $index . ':disabled',
            ],
        ];
        $this->meta = $this->arrayManager->set($checkboxPath, $this->meta, $useDefaultConfig);

        return $this;
    }
}
