<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Block\Adminhtml\Image\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

/**
 * Class Tabs
 * @package Yosto\Slider\Block\Adminhtml\Image\Edit
 */
class Tabs extends WidgetTabs
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('image_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Image Information'));
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'image_info',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    'Yosto\Slider\Block\Adminhtml\Image\Edit\Tab\Info'
                )->toHtml(),
                'active' => true
            ]
        );
        $this->addTab(
            'default_setting_image',
            [
                'label'=>__('Default Setting'),
                'title'=>__('Default Setting'),
                'content'=>$this->getLayout()->createBlock(
                    'Yosto\Slider\Block\Adminhtml\Image\Edit\Tab\DefaultSetting'
                )->toHtml(),
                'active'=>false
            ]
        );

        $this->addTab(
            'effect_image',
            [
                'label'=>__('Effect'),
                'title'=>__('Effect'),
                'content'=>$this->getLayout()->createBlock(
                    'Yosto\Slider\Block\Adminhtml\Image\Edit\Tab\Effect'
                )->toHtml(),
                'active'=>false
            ]
        );
        $this->addTab(
            'upload_image',
            [
                'label'=>__('Upload'),
                'title'=>__('Upload'),
                'content'=>$this->getLayout()->createBlock(
                    'Yosto\Slider\Block\Adminhtml\Image\Edit\Tab\Upload'
                )->toHtml(),
                'active'=>false
            ]
        );

        return parent::_beforeToHtml();
    }
}