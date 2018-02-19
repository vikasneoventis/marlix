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

namespace Itoris\Producttabsslider\Block\Adminhtml;

class GridButton extends \Magento\Backend\Block\Widget\Form\Container
{



    protected function _construct()
    {
        $this->_blockGroup = 'Itoris_Producttabsslider';
        $this->_controller = 'adminhtml_producttabs';
        $this->_objectId = 'page_id';
        parent::_construct();

        $this->buttonList->update('save', 'label', $this->escapeHtml(__('Add New Tab')));
        $this->buttonList->update('save', 'path', 'edit');
        if($this->getRequest()->getParam('store')!=null){
            $this->buttonList->update('save', 'disabled',  "disabled");
        }
        $this->buttonList->update('save', 'url',  "setLocation('*/*/edit')");
        $this->buttonList->remove('delete');
        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');

        //$this->_controller = false;
        //$this->_blockGroup = false;
        //$this->_mode = false;

    }


}