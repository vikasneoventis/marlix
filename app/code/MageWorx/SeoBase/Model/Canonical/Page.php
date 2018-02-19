<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Canonical;

class Page extends \MageWorx\SeoBase\Model\Canonical
{
    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $layout;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     *
     * @param \MageWorx\SeoBase\Helper\Data $helperData
     * @param \MageWorx\SeoBase\Helper\Url $helperUrl
     * @param \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\View\Layout $layout
     * @param string $fullActionName
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Data $helperData,
        \MageWorx\SeoBase\Helper\Url  $helperUrl,
        \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\View\Layout $layout,
        $fullActionName
    ) {
        $this->layout = $layout;
        $this->url = $url;
        parent::__construct($helperData, $helperUrl, $helperStoreUrl, $fullActionName);
    }

    /**
     * Retrieve CMS pages canonical URL
     *
     * @return string|null
     */
    public function getCanonicalUrl()
    {
        if ($this->isCancelCanonical()) {
            return null;
        }

        $currentUrl = $this->url->getCurrentUrl();
        $url        = $this->helperUrl->deleteAllParametrsFromUrl($currentUrl);
        $page       = $this->getPage();
        if ($page) {
            $homePageId = null;
            $homeIdentifier = $this->helperData->getHomeIdentifier();

            if (strpos($homeIdentifier, '|') !== false) {
                list($homeIdentifier, $homePageId) = explode('|', $homeIdentifier);
            }

            if ($homeIdentifier == $page->getIdentifier()) {
                $urlRaw = rtrim(str_replace($page->getIdentifier(), '', $url), '/');
                $url = $this->trailingSlash($urlRaw, true);
                return $this->helperUrl->escapeUrl($url);
            }
        }

        return $this->renderUrl($url);
    }

    /**
     * Retrieve current CMS page model from layout
     *
     * @return \Magento\Cms\Model\Page|null
     */
    protected function getPage()
    {
        $block = $this->layout->getBlock('cms_page');
        if (is_object($block)) {
            $page = $block->getPage();
            if (is_object($page)) {
                return $page;
            }
        }

        return null;
    }
}
