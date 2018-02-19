<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoUrls\Plugin\ApplyFilters;

use Magento\Framework\View\Element\Template;
use \MageWorx\LayeredNavigation\Block\Navigation\UrlReplacer;

class AroundGetBaseFiltersUrl
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
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AroundGetClearUrl constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MageWorx\SeoUrls\Helper\Data $helperData
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageWorx\SeoUrls\Helper\Data $helperData,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->storeManager = $storeManager;
        $this->helperData   = $helperData;
        $this->request      = $request;
    }

    /**
     * @param Template $subject
     * @param $proceed
     * @param array $params
     * @return string
     */
    public function aroundGetBaseFiltersUrl(Template $subject, $proceed)
    {
        if ($this->out()) {
            return $proceed();
        }

        $rawCurrentUrl = $this->storeManager->getStore()->getCurrentUrl();
        return explode('?', $rawCurrentUrl)[0] . '?' . UrlReplacer::GET_PARAM_NAME_REPLACE_URL . '=1';
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
