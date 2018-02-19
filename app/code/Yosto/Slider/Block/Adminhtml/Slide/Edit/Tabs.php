<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Block\Adminhtml\Slide\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

/**
 * Class Tabs
 * @package Yosto\Slider\Block\Adminhtml\Slide\Edit
 */
class Tabs extends WidgetTabs
{
    public function _construct(){
        $this->setId('slide_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('Slider Information');
        parent::_construct();
    }
    public function _beforeToHtml(){
        $this->addTab(
            'slide_info',
            [
                'label'=>__('General Info'),
                'title'=>__('General Info'),
                'content'=>$this->getLayout()->addBlock(
                    'Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab\Info'
                )->toHtml(),
                'active'=>true
            ]
        );

        $this->addTab(
            'custom_setting',
            [
                'label'=>__('Custom setting'),
                'title'=>__('Custom setting'),
                'content'=>$this->getLayout()->addBlock(
                    'Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab\CustomSetting'
                )->toHtml(),
                'active'=>false
            ]
        );
        $this->addTab(
            'default_setting',
            [
                'label'=>__('Default setting'),
                'title'=>__('Default setting'),
                'content'=>$this->getLayout()->addBlock(
                    'Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab\DefaultSetting'
                )->toHtml(),
                'active'=>false
            ]
        );

        $this->addTab(
            'select_image',
            [
                'label'=>__('Select Image'),
                'title'=>__('Select Image'),
                'content'=>$this->getLayout()->addBlock(
                    'Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab\Images'
                )->toHtml(),
                'active'=>false
            ]
        );
        parent::_beforeToHtml();
    }

}