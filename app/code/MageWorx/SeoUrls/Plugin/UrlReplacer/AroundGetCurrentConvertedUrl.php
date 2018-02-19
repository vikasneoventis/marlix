<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoUrls\Plugin\UrlReplacer;

use \MageWorx\SeoUrls\Model\Source\PagerMask;
use Magento\Framework\View\Element\Template;
use MageWorx\SeoAll\Helper\Layer as SeoAllHelperLayer;

class AroundGetCurrentConvertedUrl
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
     * AroundGetCurrentConvertedUrl constructor.
     * @param \MageWorx\SeoUrls\Helper\Data $helperData
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \MageWorx\SeoUrls\Helper\UrlBuildWrapper $urlBuildWrapper
     */
    public function __construct(
        \MageWorx\SeoUrls\Helper\Data $helperData,
        \Magento\Framework\App\RequestInterface $request,
        \MageWorx\SeoUrls\Helper\UrlBuildWrapper $urlBuildWrapper
    ) {
        $this->request            = $request;
        $this->helperData         = $helperData;
        $this->urlBuildWrapper    = $urlBuildWrapper;
    }

    /**
     * @param Template $subject
     * @param $proceed
     * @param array $params
     * @return mixed
     */
    public function aroundGetCurrentConvertedUrl(Template $subject, \Closure $proceed)
    {
        if ($this->out()) {
            return $proceed();
        }
        return $this->urlBuildWrapper->getCurrentFiltersUrl();
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
