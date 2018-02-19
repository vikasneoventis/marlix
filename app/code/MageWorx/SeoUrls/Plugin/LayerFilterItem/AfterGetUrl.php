<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoUrls\Plugin\LayerFilterItem;

use \MageWorx\SeoUrls\Model\Source\PagerMask;
use Magento\Framework\View\Element\Template;
use MageWorx\SeoAll\Helper\Layer as SeoAllHelperLayer;

class AfterGetUrl
{
    /**
     * @var \MageWorx\SeoUrls\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \MageWorx\SeoUrls\Helper\UrlBuildWrapper
     */
    protected $urlBuildWrapper;

    /**
     * AfterGetUrl constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \MageWorx\SeoUrls\Helper\Data $helperData
     * @param \MageWorx\SeoUrls\Helper\UrlBuildWrapper $urlBuildWrapper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \MageWorx\SeoUrls\Helper\Data $helperData,
        \MageWorx\SeoUrls\Helper\UrlBuildWrapper $urlBuildWrapper
    ) {
        $this->request            = $request;
        $this->helperData         = $helperData;
        $this->urlBuildWrapper    = $urlBuildWrapper;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\Item $filterItem
     * @param $url
     * @return string
     */
    public function afterGetUrl(\Magento\Catalog\Model\Layer\Filter\Item $filterItem, $url)
    {
        if ($this->out()) {
            return $url;
        }

        return $this->urlBuildWrapper->getFilterUrl($filterItem);
    }

    /**
     * @return bool
     */
    protected function out()
    {
        if (!$this->helperData->getIsSeoFiltersEnable()) {
            return true;
        }

        if ($this->request->getFullActionName() !== 'catalog_category_view') {
            return true;
        }

        return false;
    }
}
