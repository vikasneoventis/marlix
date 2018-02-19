<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Block\Adminhtml\Image\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use  \Magento\Cms\Model\Wysiwyg\Config;

/**
 * Class Upload
 * @package Yosto\Slider\Block\Adminhtml\Image\Edit\Tab
 */
class Upload extends Generic implements TabInterface
{
    /**
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model=$this->_coreRegistry->registry('slider_image');
        $form=$this->_formFactory->create();
        $form->setHtmlIdPrefix('image_');
        $form->setFieldNameSuffix('image');
        $fieldset =$form->addFieldSet(
            'upload_image',
            [
                'legend'=>'Upload'
            ]
        );

        $wysiwygConfig = $this->_wysiwygConfig->getConfig();
        $fieldset->addField(
            'image_html',
            'editor',
            [
                'name'        => 'image_html',
                'label'    => __('Image'),
                'required'     => true,
                'state' => 'html',
                'config'    => $wysiwygConfig
            ]
        );
        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);
        parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Upload Image');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Upload Image');
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