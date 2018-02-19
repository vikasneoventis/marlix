<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Info
 * @package Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab
 */
class Info extends Generic implements TabInterface
{
    protected $_yesno;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    )
    {
        $this->_yesno = $yesno;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('slider_slide');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('slide_');
        $form->setFieldNameSuffix('slide');
        $fieldset = $form->addFieldset(
            'slide_fieldset',
            ['legend' => __('General')]
        );
        if ($model->getSlideId()) {
            $fieldset->addField(
                'slide_id',
                'hidden',
                ['name' => 'slide_id']
            );
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Enable'),
                'options' => $this->_yesno->toArray()
            ]
        );
        $data = $model->getData();
        if ($model->getSliderId() == null) {
            $data['status'] = 1;
        }
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm(); // TODO: Change the autogenerated stub
    }


    public function getTabLabel()
    {
        return __('Slide Info');
    }

    public function getTabTitle()
    {
        return __('Slide Info');
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