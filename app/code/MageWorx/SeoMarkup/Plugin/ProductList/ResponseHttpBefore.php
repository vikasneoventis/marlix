<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Plugin\ProductList;

use MageWorx\SeoMarkup\Helper\Category as HelperData;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout;
use MageWorx\SeoMarkup\Helper\Json\Category as HelperJsonCategory;

class ResponseHttpBefore
{
    /**
     * @var  HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var HelperJsonCategory
     */
    protected $heperJsonCategory;

    public function __construct(
        HelperData $helperData,
        Registry $registry,
        RequestInterface $request,
        UrlInterface $url,
        Layout $layout,
        HelperJsonCategory $helperJsonCategory
    ) {
        $this->helperData = $helperData;
        $this->registry = $registry;
        $this->request = $request;
        $this->url = $url;
        $this->layout = $layout;
        $this->helperJsonCategory = $helperJsonCategory;
    }

    /**
     * Add json category data to head block - we use plugin for avoid double loading product collection
     *
     * @param \Magento\Framework\App\Response\Http $subject
     * @param string $value
     * @return array
     */
    public function beforeAppendBody($subject, $value)
    {
        if (!$this->helperData->isRsEnabled()) {
            return [$value];
        }
        if (is_callable([$subject, 'isAjax']) && $subject->isAjax()) {
            return [$value];
        }
        $fullActionName = $this->request->getFullActionName();

        if ($fullActionName !== 'catalog_category_view') {
            return [$value];
        }

        $productListJson = $this->helperJsonCategory->getMarkupHtml();
        if ($productListJson) {
            $value = str_ireplace('</head>', "\n" . $productListJson . '</head>', $value);
        }
        return [$value];
    }
}
