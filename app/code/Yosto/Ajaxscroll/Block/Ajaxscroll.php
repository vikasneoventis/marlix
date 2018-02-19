<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Ajaxscroll\Block;

use Yosto\Ajaxscroll\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;

/**
 * Is used for all block templates in this extension.
 *
 * Class Ajaxscroll
 * @package Yosto\Ajaxscroll\Block
 */
class Ajaxscroll extends Template
{
    /**
     * @var Data
     */
    protected $_helper;


    /**
     * Ajaxscroll constructor.
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(Template\Context $context, Data $helper, array $data = [])
    {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }
    //For ajax scroll

    /**
     * @return mixed
     */
    public function isEnabledAjaxscroll()
    {
        return $this->_helper->isEnabledAjaxscroll();
    }

    /**
     * Url of loading bar
     *
     * @return string
     */
    public function getLoadingIcon()
    {
        return $this->getBaseUrl() . 'pub/media/yosto/lazyloading/' . $this->_helper->getLoadingIcon();
    }

    /**
     * @return mixed
     */
    public function getLoadingText()
    {
        return $this->_helper->getLoadingText();
    }

    /**
     * Get container css class
     *
     * @return string
     */
    public function getContainerClass()
    {
        return $this->_helper->getContainerClass();
    }

    /**
     * Get Item class
     *
     * @return string
     */
    public function getItemClass()
    {
        return $this->_helper->getItemClass();
    }

    /**
     * Get pagination class
     *
     * @return string
     */

    public function getPaginationClass()
    {
        return $this->_helper->getPaginationClass();
    }

    /**
     * Get next button class
     *
     * @return string
     */
    public function getNextClass()
    {
        return $this->_helper->getNextClass();
    }


    //Load more button

    /**
     * @return mixed
     */
    public function isEnabledLoadMore()
    {
        return $this->_helper->isEnabledLoadMore();
    }

    /**
     * @return mixed
     */
    public function getLoadMoreText()
    {
        return $this->_helper->getLoadMoreText();
    }

    /**
     * @return mixed
     */
    public function getThreshold()
    {
        return $this->_helper->getThreshold();
    }

    /**
     * @return mixed
     */
    public function getLoadMoreBackground()
    {
        return $this->_helper->getLoadMoreBackground();
    }

    /**
     * @return mixed
     */
    public function getLoadMoreTextColor()
    {
        return $this->_helper->getLoadMoreTextColor();
    }

    /**
     * @return mixed
     */
    public function getLoadPreviousText()
    {
        return $this->_helper->getLoadPreviousText();
    }

    /**
     * @return mixed
     */
    public function getNoMoreItemsText()
    {
        return $this->_helper->getNoMoreItemsText();
    }

    //Lazy load
    /**
     * @return mixed
     */
    public function isEnabledLazyLoad()
    {
        return $this->_helper->isEnabledLazyLoad();
    }

    /**
     * @return string
     */
    public function getLoadingImage()
    {
        return $this->getBaseUrl() . 'pub/media/yosto/lazyloading/' . $this->_helper->getLoadingImage();
    }


    /**
     * @return mixed
     */
    public function getLoadingClass()
    {
        return $this->_helper->getLoadingClass();
    }

    //Back to top
    /**
     * @return mixed
     */
    public function isEnabledTop()
    {
        return $this->_helper->isEnabledTop();
    }

    /**
     * @return string
     */
    public function getTopIcon()
    {
        return $this->getBaseUrl() . 'pub/media/yosto/lazyloading/' . $this->_helper->getTopIcon();
    }

    /**
     * @return mixed
     */
    public function getIconArrowColor()
    {
        return $this->_helper->getIconArrowColor();
    }

    /**
     * @return mixed
     */
    public function isImageOrIcon()
    {
        return $this->_helper->isImageOrIcon();
    }

    /**
     * @return mixed
     */
    public function getInitBackground()
    {
        return $this->_helper->getInitBackground();
    }

    /**
     * @return mixed
     */
    public function getHoverBackground()
    {
        return $this->_helper->getHoverBackground();
    }

    //Get grid and list mode info
    /**
     * @return mixed
     */
    public function getListMode()
    {
        return $this->_helper->getListMode();
    }

    /**
     * @return mixed
     */
    public function getGridPerPage()
    {
        return $this->_helper->getGridPerpage();
    }

    /**
     * @return mixed
     */
    public function getListPerPage()
    {
        return $this->_helper->getListPerPage();
    }

}