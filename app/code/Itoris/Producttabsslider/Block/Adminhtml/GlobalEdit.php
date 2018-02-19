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
use Magento\Backend\Block\Widget\Button\SplitButton;
class GlobalEdit extends \Magento\Backend\Block\Widget\Form\Container
{

    protected function _construct()
    {
        $this->_blockGroup = 'Itoris_Producttabsslider';
        $this->_controller = 'adminhtml_producttabs';
        $this->_objectId = 'tab_id';
        $this->_mode='formEdit';
        parent::_construct();
        $this->buttonList->update('save','label', $this->escapeHtml(__('Save')));
        if($this->getRequest()->getParam('store')!=null) {
            $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/index').'store/'.$this->getRequest()->getParam('store') . '\')');
        }
        if($this->getRequest()->getParam('store')!=null) {
            $this->buttonList->update('save', 'url', '/edit/store/'.$this->getRequest()->getParam('store'));
        }
        $this->buttonList->update('save','class','save');
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => $this->escapeHtml(__('Save and Continue Edit')),
                'class' => 'primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            null
        );
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');

        //$this->_controller = false;
        //$this->_blockGroup = false;
        //$this->_mode = false;

    }
    protected function _getSaveAndContinueUrl()
    {

        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }
}