<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier\ModifierInterface as OptionBaseModifierInterface;

class All extends AbstractModifier implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    const SCOPE_PRODUCT = 'product';
    const SCOPE_GROUP = 'group';

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var string
     */
    protected $scope = self::SCOPE_PRODUCT;

    /**
     * @param ArrayManager $arrayManager
     * @param PoolInterface $pool
     */
    public function __construct(
        ArrayManager $arrayManager,
        PoolInterface $pool
    ) {
        $this->arrayManager = $arrayManager;
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Ui\DataProvider\Modifier\ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->detectScope();

        /** @var \Magento\Ui\DataProvider\Modifier\ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            if (!$modifier instanceof OptionBaseModifierInterface) {
                continue;
            }

            if ($modifier->isProductScopeOnly() && $this->getScope() != self::SCOPE_PRODUCT) {
                continue;
            }

            $this->meta = $modifier->modifyMeta($this->meta);
        }

        return $this->meta;
    }

    /**
     * Detect current scope
     * Based on the provided meta data
     */
    protected function detectScope()
    {
        if (!empty($this->meta['group']) && empty($this->meta[AbstractModifier::DEFAULT_GENERAL_PANEL])) {
            $this->scope = self::SCOPE_GROUP;
        } else {
            $this->scope = self::SCOPE_PRODUCT;
        }
    }

    /**
     * @return string
     */
    protected function getScope()
    {
        return $this->scope;
    }
}
