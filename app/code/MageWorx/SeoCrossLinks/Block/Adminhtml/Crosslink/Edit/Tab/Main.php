<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinks\Block\Adminhtml\Crosslink\Edit\Tab;

use MageWorx\SeoCrossLinks\Model\Crosslink;
use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use Magento\Config\Model\Config\Source\Yesno as BooleanOptions;
use MageWorx\SeoCrossLinks\Model\Crosslink\Source\IsActive as LinkIsActive;
use MageWorx\SeoCrossLinks\Model\Crosslink\Source\LinkTo as LinkToOptions;
use MageWorx\SeoCrossLinks\Model\Crosslink\Source\Target as LinkTargetOptions;

class Main extends GenericForm implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var BooleanOptions
     */
    protected $booleanOptions;

    /**
     * @var LinkIsActiveOptions
     */
    protected $linkIsActiveOptions;

    /**
     * @var LinkToOptions
     */
    protected $linkToOptions;

    /**
     * @var LinkTargetOptions
     */
    protected $linkTargetOptions;

    /**
     * @param Store $systemStore
     * @param BooleanOptions $booleanOptions
     * @param LinkToOptions $linkToOptions
     * @param LinkTargetOptions $linkTargetOptions
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Store $systemStore,
        BooleanOptions $booleanOptions,
        LinkIsActive $linkIsActive,
        LinkToOptions $linkToOptions,
        LinkTargetOptions $linkTargetOptions,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->systemStore           = $systemStore;
        $this->booleanOptions        = $booleanOptions;
        $this->linkIsActiveOptions   = $linkIsActive;
        $this->linkToOptions         = $linkToOptions;
        $this->linkTargetOption      = $linkTargetOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \MageWorx\SeoCrossLinks\Model\Crosslinks $crosslink */
        $crosslink = $this->_coreRegistry->registry('mageworx_seocrosslinks_crosslink');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('crosslink_');
        $form->setFieldNameSuffix('crosslink');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Crosslink Info'),
                'class'  => 'fieldset-wide'
            ]
        );

        if ($crosslink->getId()) {
            $fieldset->addField(
                'crosslink_id',
                'hidden',
                ['name' => 'crosslink_id']
            );
        }

        $fieldset->addField(
            'keyword',
            'textarea',
            [
                'name'      => 'keyword',
                'label'     => __('Keyword'),
                'title'     => __('Keyword'),
                'required'  => true,
                'note'      => $this->getKeywordFieldNote(),
            ]
        );

        $fieldset->addField(
            'link_title',
            'text',
            [
                'name'      => 'link_title',
                'label'     => __('Link Alt/Title'),
                'title'     => __('Link Alt/Title'),
            ]
        );

        $fieldset->addField(
            'link_target',
            'select',
            [
                'label'     => __('Link Target'),
                'title'     => __('Link Target'),
                'name'      => 'link_target',
                'required'  => true,
                'options'   => $this->linkTargetOption->toArray()
            ]
        );

        if ($this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name'      => 'stores[]',
                    'value'     => $this->_storeManager->getStore(true)->getId()
                ]
            );
            $crosslink->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name'     => 'stores[]',
                    'label'    => __('Store View'),
                    'title'    => __('Store View'),
                    'required' => true,
                    'values'   => $this->systemStore->getStoreValuesForForm(false, true),
                    'note'     =>__('NOTE: Cross Link will be build in the chosen store views.'),
                ]
            );
        }

        $reference = $fieldset->addField(
            'reference',
            'select',
            [
                'label'     => __('Reference'),
                'name'      => 'reference',
                'values'    => $this->linkToOptions->toOptionArray()
            ]
        );

        $url = $fieldset->addField(
            'ref_static_url',
            'text',
            [
                'label'    => __('Custom URL'),
                'name'     => 'ref_static_url',
                'index'    => 'ref_static_url',
                'class'    => 'required-entry',
                'required' => true,
                'note'     => $this->getStaticUrlFieldNote()
            ]
        );

        $product = $fieldset->addField(
            'ref_product_sku',
            'text',
            [
                'label'    => __('Product SKU'),
                'name'     => 'ref_product_sku',
                'index'    => 'ref_product_sku',
                'required'  => true,
            ]
        );

        $category = $fieldset->addField(
            'ref_category_id',
            'text',
            [
                'label'    => __('Category ID'),
                'name'     => 'ref_category_id',
                'index'    => 'ref_category_id',
                'class'    => 'required-entry not-negative-amount integer',
                'required'  => true,
            ]
        );

        $fieldset->addField(
            'replacement_count',
            'text',
            [
                'label'    => __('Max Replacement Count per Page'),
                'name'     => 'replacement_count',
                'index'    => 'replacement_count',
                'class'    => 'required-entry not-negative-amount integer validate-number-range number-range-0-100',
                'note'     => __('Max # of this keyword per page. 100 is the max value.'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'priority',
            'text',
            [
                'label'    => __('Priority'),
                'name'     => 'priority',
                'index'    => 'priority',
                'class'    => 'required-entry not-negative-amount integer validate-number-range number-range-0-100',
                'note'     => __('100 is the highest priority.'),
                'required' => true,
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )
            ->addFieldMap($reference->getHtmlId(), $reference->getName())
            ->addFieldMap($url->getHtmlId(), $url->getName())
            ->addFieldMap($product->getHtmlId(), $product->getName())
            ->addFieldMap($category->getHtmlId(), $category->getName())
            ->addFieldDependence(
                $url->getName(),
                $reference->getName(),
                Crosslink::REFERENCE_TO_STATIC_URL
            )
            ->addFieldDependence(
                $product->getName(),
                $reference->getName(),
                Crosslink::REFERENCE_TO_PRODUCT_BY_SKU
            )
            ->addFieldDependence(
                $category->getName(),
                $reference->getName(),
                Crosslink::REFERENCE_TO_CATEGORY_BY_ID
            )
        );


        $fieldset->addField(
            'nofollow_rel',
            'select',
            [
                'label'     => __('Nofollow'),
                'title'     => __('Nofollow'),
                'name'      => 'nofollow_rel',
                'required'  => false,
                'options'   => $this->booleanOptions->toArray()
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label'     => __('Is Active'),
                'title'     => __('Is Active'),
                'name'      => 'is_active',
                'required'  => true,
                'options'   => $this->linkIsActiveOptions->toArray()
            ]
        );

        $crosslinkData = $this->_session->getData('mageworx_seocrosslinks_crosslink_data', true);
        if ($crosslinkData) {
            $crosslink->addData($crosslinkData);
        } else {
            if (!$crosslink->getId()) {
                $crosslink->addData($crosslink->getDefaultValues());
            }
        }

        $form->addValues($crosslink->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Cross Link');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    protected function getKeywordFieldNote()
    {
        $hrefBefore = '<a href="http://support.mageworx.com/extensions/seo_suite_pro_and_ultimate/how_to_add_keywords_for_creating_internal_links.html" target="_blank">';
        $hrefAfter  = '</a>';

        $note = '<p>' . __("NOTE: Enter one keyword (keyword phrase) per line. "
            . "A new cross link rule will be created for each entered keyword.");

        $note .= '</p><p>' . __("For multiple keywords use the Reduced Multisave Priority feature."
            . " It reduces the keyword priority for every next keyword on the list "
            . "(thus, the most important keywords appear in the first place).");

        $note .= '</p><p>' . __("Adding '+' before or after a keyword will apply the Cross Link rule to all its variations. "
            . "E.g. Entering 'iphone 5+' will apply the rule to 'iphone 5s', 'iphone 5c', etc. (but not to 'iphone 5').") . '</p>';

        $note .= '<p>' . __('For more info, follow the %1 link %2.', $hrefBefore, $hrefAfter) . '</p>';

        return $note;
    }

    protected function getStaticUrlFieldNote()
    {
        $note = '<p>';
        $note .= __("Link without 'http[s]://' as customer/account/<br>will be converted to<br>http[s]://(store_URL_here)/customer/account/");
        $note .= '</p><p>';
        $note .= __("Link with 'http[s]://' will be added as it is.");
        $note .= '</p>';
        return $note;
    }
}
