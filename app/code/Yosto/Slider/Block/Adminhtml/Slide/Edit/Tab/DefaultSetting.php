<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class DefaultSetting
 * @package Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab
 */
class DefaultSetting extends Generic implements TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesno;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    ) {
        $this->_yesno = $yesno;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {

        $model = $this->_coreRegistry->registry('slider_slide');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('slide_');
        $form->setFieldNameSuffix('slide');
        $fieldset = $form->addFieldset(
            'slide_fieldset',
            ['legend' => __('Default Setting')]
        );
        $fieldset->addField(
            'height',
            'text',
            [
                'name' => 'height',
                'label' => __('Height'),
                'class' => 'validate-number',
                'note' => __('Height of slide using pixels, Auto height is default value')
            ]
        );

        $fieldset->addField(
            'margin',
            'text',
            [
                'name' => 'margin',
                'label' => __('Margin'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'note' => __('Margin-right on theme')
            ]
        );

        $fieldset->addField(
            'loop',
            'select',
            [
                'name' => 'loop',
                'label' => __('Is Loop'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Infinity loop. Duplicate last and first item to get loop illusion.')
            ]
        );

        $fieldset->addField(
            'center',
            'select',
            [
                'name' => 'center',
                'label' => __('Is Center'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Center item. Works well with even an odd number of items.')
            ]
        );
        $fieldset->addField(
            'mouse_drag',
            'select',
            [
                'name' => 'mouse_drag',
                'label' => __('Enable mouse drag'),
                'options' => $this->_yesno->toArray(),
            ]
        );

        $fieldset->addField(
            'touch_drag',
            'select',
            [
                'name' => 'touch_drag',
                'label' => __('Enable touch drag'),
                'options' => $this->_yesno->toArray(),
            ]
        );

        $fieldset->addField(
            'pull_drag',
            'select',
            [
                'name' => 'pull_drag',
                'label' => __('Enable pull drag'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Stage pull to edge.')
            ]
        );

        $fieldset->addField(
            'free_drag',
            'select',
            [
                'name' => 'free_drag',
                'label' => __('Enable free drag'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Item pull to edge')
            ]
        );

        $fieldset->addField(
            'stage_padding',
            'text',
            [
                'name' => 'stage_padding',
                'label' => __('Enable stage padding'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'note' => __('Padding left and right on stage (can see neighbours).')
            ]
        );

        $fieldset->addField(
            'merge',
            'select',
            [
                'name' => 'merge',
                'label' => __('Enable merge'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Merge items. Looking for data-merge inside item.')
            ]
        );

        $fieldset->addField(
            'merge_fit',
            'select',
            [
                'name' => 'merge_fit',
                'label' => __('Enable merge fit'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Fit merged items if screen is smaller than items value.')
            ]
        );


        $fieldset->addField(
            'auto_width',
            'select',
            [
                'name' => 'auto_width',
                'label' => __('Enable auto width'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Set non grid content. Try using width style on divs.')
            ]
        );

        $fieldset->addField(
            'start_position',
            'text',
            [
                'name' => 'start_position',
                'label' => __('Enable start position'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'note' => __('Start position')
            ]
        );


        $fieldset->addField(
            'rewind',
            'select',
            [
                'name' => 'rewind',
                'label' => __('Rewind'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Go backwards when the boundary has reached')
            ]
        );


        $fieldset->addField(
            'slide_by',
            'text',
            [
                'name' => 'slide_by',
                'label' => __('Slide by'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'note' => __('Navigation slide by x')
            ]
        );


        $fieldset->addField(
            'dot_data',
            'select',
            [
                'name' => 'dot_data',
                'label' => __('Dot data'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Used by data-dot content')
            ]
        );
        $fieldset->addField(
            'lazy_load',
            'select',
            [
                'name' => 'lazy_load',
                'label' => __('Lazy Load'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Lazy load images. data-src and data-src-retina for highres. Also load images into background inline style if element is not img')
            ]
        );

        $fieldset->addField(
            'smart_speed',
            'text',
            [
                'name' => 'smart_speed',
                'label' => __('Smart speed'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'note' => __('Speed Calculate')
            ]
        );

        $fieldset->addField(
            'fluid_speed',
            'select',
            [
                'name' => 'fluid_speed',
                'label' => __('Fluid speed'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Speed Calculate')
            ]
        );
        $fieldset->addField(
            'nav_speed',
            'select',
            [
                'name' => 'nav_speed',
                'label' => __('Nav speed'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Navigation speed.')
            ]
        );
        $fieldset->addField(
            'dots_speed',
            'select',
            [
                'name' => 'dots_speed',
                'label' => __('Dots speed'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Pagination speed')
            ]
        );

        $fieldset->addField(
            'drag_end_speed',
            'select',
            [
                'name' => 'drag_end_speed',
                'label' => __('Drag end speed'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Drag end speed')
            ]
        );
        $fieldset->addField(
            'callbacks',
            'select',
            [
                'name' => 'callbacks',
                'label' => __('Callbacks'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Enable callback events.')
            ]
        );
        $fieldset->addField(
            'responsive_refresh_rate',
            'text',
            [
                'name' => 'responsive_refresh_rate',
                'label' => __('Responsive refresh rate'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'note' => __('Object containing responsive options. Can be set to false to remove responsive capabilities.')
            ]
        );

        $fieldset->addField(
            'responsive_base_element',
            'text',
            [
                'name' => 'responsive_base_element',
                'label' => __('Responsive base element'),
                'required' => true,
                'note' => __('Set on any DOM element. If you care about non responsive browser (like ie8) then use it on main wrapper. This will prevent from crazy resizing.')
            ]
        );

        $fieldset->addField(
            'video',
            'select',
            [
                'name' => 'video',
                'label' => __('Video'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Enable fetching YouTube/Vimeo/Vzaar videos.')
            ]
        );
        $fieldset->addField(
            'video_height',
            'text',
            [
                'name' => 'video_height',
                'label' => __('Video height'),
                'class' => 'validate-number',
                'note' => __('Set height for videos.')
            ]
        );
        $fieldset->addField(
            'video_width',
            'text',
            [
                'name' => 'video_width',
                'label' => __('Video width'),
                'class' => 'validate-number',
                'note' => __('Set width for videos.')
            ]
        );


        $fieldset->addField(
            'fallback_easing',
            'text',
            [
                'name' => 'fallback_easing',
                'label' => __('Fallback easing'),
                'required' => true,
                'note' => __('Easing for CSS2')
            ]
        );

        $data = $model->getData();
        if (!$model->getData('slide_id')) {
            $data['margin'] = 0;
            $data['loop'] = 0;
            $data['center'] = 0;
            $data['mouse_drag'] = 1;
            $data['touch_drag'] = 1;
            $data['pull_drag'] = 1;
            $data['stage_padding'] = 0;
            $data['merge'] = 0;
            $data['merge_fit'] = 1;
            $data['auto_width'] = 0;
            $data['start_position'] = 0;
            $data['rewind'] = 1;
            $data['slide_by'] = '1';
            $data['dot_data'] = 0;
            $data['smart_speed'] =250;
            $data['fluid_speed'] = 0;
            $data['nav_speed'] = 0;
            $data['dots_speed'] = 1;
            $data['drag_end_speed'] = 0;
            $data['callbacks'] = 1;
            $data['responsive_refresh_rate'] = 200;
            $data['responsive_base_element'] = 'window';
            $data['video'] = 0;
            $data['fallback_easing'] = 'swing';
        }
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Default Setting');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Default Setting');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

}