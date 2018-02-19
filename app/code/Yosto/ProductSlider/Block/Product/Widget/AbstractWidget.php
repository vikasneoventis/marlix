<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\ProductSlider\Block\Product\Widget;


use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\Widget\NewWidget;
use Yosto\ProductSlider\Model\ResourceModel\Bestsellers\CollectionFactory as BestsellersCollectionFactory;
use Yosto\ProductSlider\Model\ResourceModel\Mostviewed\CollectionFactory as MostViewedCollectionFactory;

/**
 * Class AbstractWidget
 * @package Yosto\ProductSlider\Block\Product\Widget
 */
abstract class AbstractWidget extends NewWidget
{

    protected $_widgetName;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $_urlHelper;

    /**
     * @var BestsellersCollectionFactory
     */
    protected $_bestsellersCollectionFactory;

    /**
     * @var MostViewedCollectionFactory
     */
    protected $_mostViewedCollectionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $_catalogProductTypeConfigurable;

    /**
     * AbstractWidget constructor.
     * @param Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param BestsellersCollectionFactory $bestsellersCollectionFactory
     * @param MostViewedCollectionFactory $mostViewedCollectionFactory
     * @param array $data
     */
    public function __construct(
         Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Framework\Url\Helper\Data $urlHelper,
         BestsellersCollectionFactory $bestsellersCollectionFactory,
         MostViewedCollectionFactory $mostViewedCollectionFactory,
        array $data = []
    ) {
        $this->_urlHelper = $urlHelper;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_bestsellersCollectionFactory = $bestsellersCollectionFactory;
        $this->_mostViewedCollectionFactory = $mostViewedCollectionFactory;
        parent::__construct($context, $productCollectionFactory, $catalogProductVisibility, $httpContext, $data);
    }


    /**
     * @param null $type
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
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
            : $this->getLayout()->createBlock('Yosto\ProductSlider\Block\View\Element\CustomRendererList')->setWidgetName($this->getWidgetName());
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
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * @return mixed
     */
    public function isShowSwatches()
    {
        return $this->getData('show_swatches');
    }


    public function getWidgetName()
    {
        return $this->_widgetName;
    }

    public function setWidgetName($widgetName)
    {
        $this->_widgetName = $widgetName;
        return $this;
    }
}