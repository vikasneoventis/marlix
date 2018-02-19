<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_TABS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\Producttabsslider\Block\Adminhtml\Grid\Render;


class GlobalTextGroup extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $tabGroup=$row->getData('group');
        $tabGroup=explode(',',$tabGroup);
        $store=$row->getData('group_store_name');
        if($this->getRequest()->getParam('store')){
            if(!empty($store) && $store!=NULL) {
                $store = explode(',', $store);
                if (count($store) > 1) {
                    if (in_array("''", $store) || in_array('""', $store))
                        array_pop($store);
                }

                $store = implode(',', $store);
                $store = trim($store, ",");
                $row->setData('group_name', $store);
            }
        }
        if(in_array(-1,$tabGroup)){
            $row->setData('group_name',$this->escapeHtml(__('All Groups')));
        }
        $format = $this->getColumn()->getFormat() ? $this->getColumn()->getFormat() : null;
        $defaultValue = $this->getColumn()->getDefault();
        if ($format === null) {
            // If no format and it column not filtered specified return data as is.
            $data = parent::_getValue($row);
            $string = $data === null ? $defaultValue : $data;
            return $this->escapeHtml($string);
        } elseif (preg_match_all($this->_variablePattern, $format, $matches)) {
            // Parsing of format string
            $formattedString = $format;
            foreach ($matches[0] as $matchIndex => $match) {
                $value = $row->getData($matches[1][$matchIndex]);
                $formattedString = str_replace($match, $value, $formattedString);
            }
            return $formattedString;
        } else {
            return $this->escapeHtml($format);
        }
    }
}