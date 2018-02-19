<?php

namespace Amasty\Checkout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Layout\BuilderFactory as LayoutBuilderFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Quote\Model\Quote;

class Item extends AbstractHelper
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout = null;
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var LayoutBuilderFactory
     */
    protected $layoutBuilderFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        LayoutBuilderFactory $layoutBuilderFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->layoutFactory = $layoutFactory;
        $this->layoutBuilderFactory = $layoutBuilderFactory;
        $this->registry = $registry;
    }

    public function getItemOptionsConfig(Quote $quote, $item)
    {
        /** @var \Magento\Catalog\Block\Product\View\Options $optionsBlock */
        $optionsBlock = $this->getLayout()->getBlock('amcheckout.options.prototype');

        $quoteItem = is_object($item) ? $item : $quote->getItemById($item);

        $additionalConfig = [];

        $product = $quoteItem->getProduct();

        $product->setPreconfiguredValues(
            $product->processBuyRequest($quoteItem->getBuyRequest())
        );

        // Fix issue in vendor/magento/module-tax/Observer/GetPriceConfigurationObserver.php
        $oldRegistryProduct = $this->registry->registry('current_product');
        if ($oldRegistryProduct) {
            $this->registry->unregister('current_product');
        }
        $this->registry->register('current_product', $product);

        if ($quoteItem->getData('product_type') == 'configurable') {
            $buyRequest = $quoteItem->getBuyRequest();

            /** @var \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $configurableAttributesBlock */
            $configurableAttributesBlock = $this->getLayout()->getBlock('amcheckout.super.prototype');

            $configurableAttributesBlock->unsetData('allow_products');
            $configurableAttributesBlock->addData([
                'product' => $product,
                'quote_item' => $quoteItem
            ]);

            $configurableAttributesConfig = [
                'selectedAttributes' => $buyRequest['super_attribute'],
                'template' => $configurableAttributesBlock->toHtml(),
                'spConfig' => $configurableAttributesBlock->getJsonConfig(),
            ];

            $additionalConfig['configurableAttributes'] = $configurableAttributesConfig;
        }

        if ($quoteItem->getProduct()->getOptions()) {
            $optionsBlock->setProduct($product);

            $customOptionsConfig = [
                'template' => $optionsBlock->toHtml(),
                'optionConfig' => $optionsBlock->getJsonConfig()
            ];

            $additionalConfig['customOptions'] = $customOptionsConfig;
        }

        $this->registry->unregister('current_product');
        if ($oldRegistryProduct) {
            $this->registry->register('current_product', $oldRegistryProduct);
        }

        return $additionalConfig;
    }

    protected function getLayout()
    {
        if ($this->layout === null) {
            $layout = $this->layoutFactory->create();

            $this->layoutBuilderFactory->create(
                LayoutBuilderFactory::TYPE_LAYOUT, ['layout' => $layout]
            );
            $layout->getUpdate()->addHandle(['default', 'amasty_checkout_prototypes']);

            /** @var \Magento\Framework\View\Element\AbstractBlock $block */
            foreach ($layout->getAllBlocks() as $block) {
                $block->setData('area', 'frontend');
            }

            $this->layout = $layout;
        }

        return $this->layout;
    }
}
