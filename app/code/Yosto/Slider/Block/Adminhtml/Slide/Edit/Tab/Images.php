<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Images
 * @package Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab
 */
class Images extends Generic implements TabInterface
{
    protected $imageArray;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Yosto\Slider\Model\Slide\Form\ImageArray $imageArray,
        array $data = []
    )
    {
        $this->imageArray=$imageArray;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    public function _prepareForm(){
        $selectedImages=$this->_coreRegistry->registry('slider_slide_selected_images');
        $form=$this->_formFactory->create();
        $form->setHtmlIdPrefix('slide_');
        $form->setFieldNameSuffix('slide');

        $fieldset=$form->addFieldset(
            'slide_fieldset_image',
            ['legend'=>__('Select Image')]
        );
        $fieldset->addField(
            'image',
            'multiselect',
            [
                'name'=>'image',
                'label'=>__('Image'),
                'values'=>$this->imageArray->toOptionArray(),
                'value'=>$selectedImages
            ]
        );
        $this->setForm($form);
        parent::_prepareForm();
    }
    public function getTabLabel()
    {
        return __('Select Images');
    }

    public function getTabTitle()
    {
        return __('Select Images');
    }

    public function canShowTab()
    {
       return true;
    }

    public function isHidden()
    {
        return false;
    }

}