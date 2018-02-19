<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Block\Adminhtml\Slide;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Yosto\Slider\Block\Adminhtml\Slide
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    )
    {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'slide_id';
        $this->_controller = 'adminhtml_slide';
        $this->_blockGroup = 'Yosto_Slider';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete'));
    }

    /**
     * Retrieve text for header element depending on loaded news
     *
     * @return string
     */
    public function getHeaderText()
    {
        $imageRegistry = $this->coreRegistry->registry('slider_slide');
        if ($imageRegistry->getImageId()) {
            $imageTitle = $this->escapeHtml($imageRegistry->getTitle());
            return __("Edit Slide '%1'", $imageTitle);
        } else {
            return __('Add Slide');
        }
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        /*        $this->_formScripts[] = "
        function toggleEditor() {
        if (tinyMCE.getInstanceById('post_content') == null) {
        tinyMCE.execCommand('mceAddControl', false, 'post_content');
        } else {
        tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
        }
        };
        ";*/

        return parent::_prepareLayout();
    }
}