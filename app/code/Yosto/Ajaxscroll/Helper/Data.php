<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Ajaxscroll\Helper;

use Magento\Store\Model\ScopeInterface;
/**
 * Class Data
 * @package Yosto\Ajaxscroll\Helper
 */
class Data extends \Yosto\Core\Helper\Data
{
    /**
     * @param $configPath
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($configPath, $storeId = null) {
        return $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE, $storeId);
    }


    //For ajaxscroll

    /**
     * @return mixed
     */
    public function isEnabledAjaxscroll()
    {
        return $this->getConfig('ajaxscroll/general/enabled');

    }


    /**
     * @return mixed
     */
    public function getLoadingIcon()
    {
        return $this->getConfig('ajaxscroll/general/loading_icon');
    }

    /**
     * @return mixed
     */
    public function getLoadingText()
    {
        return $this->getConfig('ajaxscroll/general/loading_text');
    }

    /**
     * @return mixed
     */
    public function getContainerClass()
    {
        return $this->getConfig('ajaxscroll/general/container_class');
    }

    /**
     * @return mixed
     */
    public function getItemClass()
    {
        return $this->getConfig('ajaxscroll/general/item_class');
    }

    /**
     * @return mixed
     */
    public function getPaginationClass()
    {
        return $this->getConfig('ajaxscroll/general/pagination_class');
    }

    /**
     * @return mixed
     */
    public function getNextClass()
    {
        return $this->getConfig('ajaxscroll/general/next_button_class');
    }

    // Load more button

    /**
     * @return mixed
     */
    public function isEnabledLoadMore()
    {
        return $this->getConfig('ajaxscroll/load_more/enabled_load_more');
    }


    /**
     * @return mixed
     */
    public function getThreshold()
    {
        return $this->getConfig('ajaxscroll/load_more/threshold');
    }

    /**
     * @return mixed
     */
    public function getLoadMoreText()
    {
        return $this->getConfig('ajaxscroll/load_more/load_more_text');
    }

    /**
     * @return mixed
     */
    public function getLoadMoreBackground()
    {

        return $this->getConfig('ajaxscroll/load_more/load_more_background');

    }

    /**
     * @return mixed
     */
    public function getLoadMoreTextColor()
    {
        return $this->getConfig('ajaxscroll/load_more/load_more_text_color');

    }

    /**
     * @return mixed
     */
    public function getLoadPreviousText()
    {
        return $this->getConfig('ajaxscroll/load_more/load_previous_text');
    }

    /**
     * @return mixed
     */
    public function getNoMoreItemsText()
    {
        return $this->getConfig('ajaxscroll/load_more/no_more_items');
    }


    //Lazy loading

    /**
     * @return mixed
     */
    public function isEnabledLazyLoad()
    {
        return $this->getConfig('ajaxscroll/lazy_loading/enabled_lazy_load');
    }

    /**
     * @return mixed
     */
    public function getLoadingImage()
    {
        return $this->getConfig('ajaxscroll/lazy_loading/loading_img');
    }

    /**
     * @return mixed
     */
    public function getLoadingClass()
    {
        return $this->getConfig('ajaxscroll/lazy_loading/loading_class');
    }

    //Back to top

    /**
     * Whether backtotop button enabled
     *
     * @return mixed
     */
    public function isEnabledTop()
    {
        return $this->getConfig('ajaxscroll/back_to_top/enabled_top');
    }

    /**
     * Whether image or icon is used
     *
     * @return mixed
     */
    public function isImageOrIcon()
    {
        return $this->getConfig('ajaxscroll/back_to_top/use_image_or_icon');
    }

    /**
     * @return mixed
     */
    public function getIconArrowColor()
    {
        return $this->getConfig('ajaxscroll/back_to_top/arrow_color');
    }

    /**
     * @return mixed
     */
    public function getInitBackground()
    {
        return $this->getConfig('ajaxscroll/back_to_top/init_background');
    }

    /**
     * @return mixed
     */
    public function getHoverBackground()
    {
        return $this->getConfig('ajaxscroll/back_to_top/hover_background');
    }

    /**
     * @return mixed
     */
    public function getTopIcon()
    {
        return $this->getConfig('ajaxscroll/back_to_top/to_top_icon');
    }


    //Get list mode
    /**
     * @return mixed
     */
    public function getListMode()
    {
        return $this->getConfig('catalog/frontend/list_mode');
    }

    /**
     * @return mixed
     */
    public function getGridPerPage()
    {
        return $this->getConfig('catalog/frontend/grid_per_page');
    }

    /**
     * @return mixed
     */
    public function getListPerPage()
    {
        return $this->getConfig('catalog/frontend/list_per_page');
    }

}
