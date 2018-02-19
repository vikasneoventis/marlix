<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ProductSlider\Block\Product\Widget;


class ProductList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    protected $_urlHelper;

    /**
     * ProductList constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder
     * @param \Magento\CatalogWidget\Model\Rule $rule
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\CatalogWidget\Model\Rule $rule,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        $this->_urlHelper = $urlHelper;
        parent::__construct($context, $productCollectionFactory, $catalogProductVisibility, $httpContext, $sqlBuilder, $rule, $conditionsHelper, $data);
    }

    /**
     * @param null $type
     * @return bool|\Magento\Framework\View\Element\AbstractBlock|null
     */
    public function getDetailsRenderer($type = null)
    {
        if ($type === null) {
            $type = 'default';
        }
        $rendererList = $this->getDetailsRendererList();
        if ($rendererList) {
            return $rendererList->getRenderer($type, 'default');
        }
        return null;
    }

    /**
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     */
    protected function getDetailsRendererList()
    {
        return $this->getDetailsRendererListName()
            ? $this->getLayout()->getBlock($this->getDetailsRendererListName())
            : $this->getLayout()->createBlock('Yosto\ProductSlider\Block\View\Element\CustomRendererList')->setWidgetName('yosto_product_list');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->_urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function isShowSwatches()
    {
        return $this->getData('show_swatches');
    }
}