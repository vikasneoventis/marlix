<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class CustomSetting
 * @package Yosto\Slider\Block\Adminhtml\Slide\Edit\Tab
 */
class CustomSetting extends Generic implements TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesno;

    /**
     * @var \Yosto\Slider\Model\Image\Effect
     */
    protected $_effect;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Yosto\Slider\Model\Image\Effect $effect
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        \Yosto\Slider\Model\Image\Effect $effect,
        array $data = []
    ) {
        $this->_yesno = $yesno;
        $this->_effect = $effect;
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
            ['legend' => __('Custom Setting')]
        );

        $fieldset->addField(
            'items_number',
            'text',
            [
                'name' => 'items_number',
                'label' => __('Items Number'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'note' => __('Number of items want to see on the screen')
            ]
        );

        $fieldset->addField(
            'nav',
            'select',
            [
                'name' => 'nav',
                'label' => __('Enable Nav'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Show next/prev buttons')
            ]
        );
        $fieldset->addField(
            'dots',
            'select',
            [
                'name' => 'dots',
                'label' => __('Dots'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Show dots navigation.')
            ]
        );

        $fieldset->addField(
            'dots_each',
            'text',
            [
                'name' => 'dots_each',
                'label' => __('Dots Each'),
                'class' => 'required-entry validate-number',
                'required' => true,
                'note' => __('Show dots each x item.')
            ]
        );

        $fieldset->addField(
            'animate_out',
            'select',
            [
                'name' => 'animate_out',
                'label' => __('Animate out'),
                'note' => __('Class for CSS3 animation out.'),
                'values' => $this->_effect->toOptionArray()

            ]
        );

        $fieldset->addField(
            'animate_in',
            'select',
            [
                'name' => 'animate_in',
                'label' => __('Animate In'),
                'note' => __('Class for CSS3 animation in.'),
                'values' => $this->_effect->toOptionArray()
            ]
        );

        $fieldset->addField(
            'autoplay',
            'select',
            [
                'name' => 'autoplay',
                'label' => __('Autoplay'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Autoplay images')
            ]
        );
        $fieldset->addField(
            'autoplay_speed',
            'text',
            [
                'name' => 'autoplay_speed',
                'label' => __('Autoplay speed'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'note' => __('Autoplay speed')
            ]
        );



        $fieldset->addField(
            'autoplay_timeout',
            'text',
            [
                'name' => 'autoplay_timeout',
                'label' => __('Autoplay timeout'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'note' => __('Autoplay interval timeout')
            ]
        );
        $fieldset->addField(
            'autoplay_hover_pause',
            'select',
            [
                'name' => 'autoplay_hover_pause',
                'label' => __('Autoplay hover pause'),
                'options' => $this->_yesno->toArray(),
                'note' => __('Pause on mouse hover.')
            ]
        );

        $data = $model->getData();
        if (!$model->getData('slide_id')) {
            $data['items_number'] = 1;
            $data['dots'] = 1;
            $data['nav'] = 1;
            $data['dots_each'] = 1;
            $data['autoplay'] = 1;
            $data['autoplay_speed'] = 1500;
            $data['autoplay_timeout'] =5000;
            $data['autoplay_hover_pause'] = 1;
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
        return __('Custom Setting');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Custom Setting');
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