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
use Yosto\Slider\Model\Image\ContentPosition;
use Yosto\Slider\Model\Image\Grid\Status;

/**
 * Class Info
 * @package Yosto\Slider\Block\Adminhtml\Image\Edit\Tab
 */
class Info extends Generic implements TabInterface
{
    /**
     * @var Status
     */
    protected $imageStatus;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Status $imageStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Status $imageStatus,
        ContentPosition $contentPosition,
        array $data = []
    ) {
        $this->imageStatus = $imageStatus;
        $this->_contentPosition = $contentPosition;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model=$this->_coreRegistry->registry('slider_image');
        $form=$this->_formFactory->create();
        $form->setHtmlIdPrefix('image_');
        $form->setFieldNameSuffix('image');
        $fieldset=$form->addFieldset(
            'image_fieldset',
            ['legend'=>__('General')]
        );
        if($model->getImageId()){
            $fieldset->addField(
                'image_id',
                'hidden',
                ['name'=>'image_id']
            );
        }
        $fieldset->addField(
            'name',
            'text',
            [
                'name'=> 'name',
                'label'=>__('Name'),
                'required' => true,
                'note' => __('Image name shows on grid')
            ]
        );
        $fieldset->addField(
            'content_position',
            'select',
            [
                'name' => 'content_position',
                'label' => __('Content Position'),
                'options' => $this->_contentPosition->toOptionArray()
            ]
        );
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
          'title',
          'text',
          [
              'name'=>'title',
              'label'=>__('Title')
          ]
        );

        $fieldset->addField(
            'subtitle',
            'text',
            [
                'name'=>'subtitle',
                'label'=>__('Subtitle')
            ]
        );

        $fieldset->addField(
            'href',
            'text',
            [
                'name'=>'href',
                'label'=>__('Link to:')
            ]
        );

        $fieldset->addField(
            'button_title',
            'text',
            [
                'name' => 'button_title',
                'label' => __('Title for Button')
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name'=>'status',
                'label'=>__('Status'),
                'options'=>$this->imageStatus->toOptionArray()
            ]
        );
        $data = $model->getData();
        if(!$model->getImageId()) {
            $data['status'] = 1;
        }
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm(); // TODO: Change the autogenerated stub
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Image Info');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Image Info');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}