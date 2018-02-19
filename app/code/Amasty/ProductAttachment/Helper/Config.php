<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Helper;

/**
 * @package Amasty\ProductAttachment\Helper
 *
 * @method int getDisplayBlock
 * @method int getShowOrderedDefault
 * @method string getBlockLabel
 * @method string getBlockLocation
 * @method string getBlockParentName
 * @method string getBlockSiblingTabCustom
 * @method string getBlockPosition
 *
 * @method string getPathToFtpFolder
 * @method string getDetectMime
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    const DISPLAY_BLOCK            = 'amfile/block/display_block';
    const CUSTOMER_GROUPS_DEFAULT  = 'amfile/block/customer_group';
    const SHOW_ORDERED_DEFAULT     = 'amfile/block/show_ordered';
    const BLOCK_LABEL              = 'amfile/block/block_label';
    const BLOCK_LOCATION           = 'amfile/block/block_location';
    const BLOCK_PARENT_NAME        = 'amfile/block/parent_name';
    const BLOCK_SIBLING_TAB        = 'amfile/block/sibling_tab';
    const BLOCK_SIBLING_TAB_CUSTOM = 'amfile/block/sibling_tab_custom';
    const BLOCK_SIBLING_NAME       = 'amfile/block/sibling_name';
    const BLOCK_POSITION           = 'amfile/block/position';

    const PATH_TO_FTP_FOLDER     = 'amfile/import/ftp_dir';
    const DETECT_MIME = 'amfile/additional/detect_mime';

    public function __call($getterName, $arguments)
    {
        switch (substr($getterName, 0, 3)) {
            case 'get':
                $key = $this->underscore(substr($getterName, 3));
                $key = function_exists('mb_strtoupper')
                    ? mb_strtoupper($key) : strtoupper($key);
                $configPath = constant("static::$key");
                return $this->getValue($configPath);
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __('Invalid method %1::%2(%3)', [get_class($this), $getterName])
        );
    }

    public function getCustomerGroupsDefault()
    {
        $customerGroupString = $this->scopeConfig->getValue(self::CUSTOMER_GROUPS_DEFAULT);
        return $customerGroupString ? explode(',', $customerGroupString) : [];
    }

    public function getBlockSiblingTab()
    {
        $siblingTab = $this->getValue(self::BLOCK_SIBLING_TAB);
        return $siblingTab != 'other'
            ? $siblingTab : $this->getValue(self::BLOCK_SIBLING_TAB_CUSTOM);
    }

    public function getBlockSiblingName()
    {
        return $this->getValue(self::BLOCK_SIBLING_NAME) ?: '-';
    }

    public function getBlockAttachmentName()
    {
        return 'advanced-attachment';
    }

    protected function underscore($name) {
        return strtolower(
            trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $name), '_')
        );
    }

    protected function getValue($key)
    {
        return $this->scopeConfig->getValue($key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isTab()
    {
        return $this->getBlockLocation() == 'product.info.details';
    }

    public function isAnyInsert()
    {
        return $this->getBlockLocation() == 'any';
    }

    public function getCustomerIdSessionKey()
    {
        return 'amfile_customer_id';
    }
}

