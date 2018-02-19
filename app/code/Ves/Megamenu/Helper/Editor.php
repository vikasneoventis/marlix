<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Helper;

class Editor extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var array
     */
    protected $_fields;
    protected $_htmlId;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;
    protected $_menuType;
    protected $_yesno;
    protected $_status;
    protected $_linkType;
    protected $_alignType;
    protected $_iconPosition;
    protected $_repeatType;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $frontendUrlBuilder;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var array
     */
    protected $_chilCol;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Ves\Megamenu\Model\Config\Source\AnimationsIn
     */
    protected $_animationsIn;

    /**
     * @var \Ves\Megamenu\Model\Config\Source\AnimationsOut
     */
    protected $_animationsOut;

    /**
     * @param \Magento\Framework\App\Helper\Context             $context         
     * @param \Magento\Framework\Registry                       $registry        
     * @param \Magento\Backend\Helper\Data                      $backendData     
     * @param \Magento\Framework\Escaper                        $escaper         
     * @param \Magento\Framework\View\LayoutInterface           $layout          
     * @param \Magento\Store\Model\System\Store                 $systemStore     
     * @param \Ves\Megamenu\Model\Config\Source\MenuType        $menuType        
     * @param \Ves\Megamenu\Model\Config\Source\Yesno           $yesno           
     * @param \Ves\Megamenu\Model\Config\Source\Status          $status          
     * @param \Ves\Megamenu\Model\Config\Source\LinkTarget      $linkTarget      
     * @param \Ves\Megamenu\Model\Config\Source\LinkType        $linkType        
     * @param \Ves\Megamenu\Model\Config\Source\AlignType       $alignType       
     * @param \Ves\Megamenu\Model\Config\Source\AnimationsIn    $animationsIn    
     * @param \Ves\Megamenu\Model\Config\Source\AnimationsOut   $animationsOut   
     * @param \Ves\Megamenu\Model\Config\Source\RepeatType      $repeatType      
     * @param \Ves\Megamenu\Model\Config\Source\IconPosition    $iconPosition    
     * @param \Ves\Megamenu\Model\Config\Source\ChilCol         $childCol        
     * @param \Ves\Megamenu\Model\Config\Source\TabPosition     $tabPosition     
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager    
     * @param \Magento\Cms\Model\Wysiwyg\Config                 $wysiwygConfig   
     * @param \Ves\Megamenu\Model\Config\Source\StoreCategories $storeCategories 
     * @param \Magento\Framework\Url                            $url             
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Store\Model\System\Store $systemStore,
        \Ves\Megamenu\Model\Config\Source\MenuType $menuType,
        \Ves\Megamenu\Model\Config\Source\Yesno $yesno,
        \Ves\Megamenu\Model\Config\Source\Status $status,
        \Ves\Megamenu\Model\Config\Source\LinkTarget $linkTarget,
        \Ves\Megamenu\Model\Config\Source\LinkType $linkType,
        \Ves\Megamenu\Model\Config\Source\AlignType $alignType,
        \Ves\Megamenu\Model\Config\Source\AnimationsIn $animationsIn,
        \Ves\Megamenu\Model\Config\Source\AnimationsOut $animationsOut,
        \Ves\Megamenu\Model\Config\Source\RepeatType $repeatType,
        \Ves\Megamenu\Model\Config\Source\IconPosition $iconPosition,
        \Ves\Megamenu\Model\Config\Source\ChilCol $childCol,
        \Ves\Megamenu\Model\Config\Source\TabPosition $tabPosition,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Ves\Megamenu\Model\Config\Source\StoreCategories $storeCategories,
        \Magento\Framework\Url $url
    ) {
        parent::__construct($context);
        $this->_coreRegistry      = $registry;
        $this->_backendData       = $backendData;
        $this->_layout            = $layout;
        $this->_menuType          = $menuType->toOptionArray();
        $this->_yesno             = $yesno->toOptionArray();
        $this->_status            = $status->toOptionArray();
        $this->_linkTarget        = $linkTarget->toOptionArray();
        $this->_linkType          = $linkType->toOptionArray();
        $this->_alignType         = $alignType->toOptionArray();
        $this->_repeatType        = $repeatType->toOptionArray();
        $this->_iconPosition      = $iconPosition->toOptionArray();
        $this->_storeManager      = $storeManager;
        $this->_backendUrlBuilder = $url;
        $this->escaper            = $escaper;
        $this->_chilCol           = $childCol->toOptionArray();
        $this->_tabPosition       = $tabPosition->toOptionArray();
        $this->_wysiwygConfig     = $wysiwygConfig;
        $this->_animationsOut     = $animationsOut->toOptionArray();
        $this->_animationsIn      = $animationsIn->toOptionArray();
        $this->_systemStore       = $systemStore;
        $this->storeCategories    = $storeCategories;
        $this->prepareFields();
    }

    public function getCategoriesHtml() {
        $categories = $this->storeCategories->getCategoryList();
        $html = '<select data-bind="value: loadcategory">';
        foreach ($categories as $category) {
            $html .= $this->_optionToHtml($category);
        }
        $html .= '</select>';
        return $html;
    }

    public function prepareFields() {
        $categoryList = $this->storeCategories->getCategoryList();

        $this->addField("label1", [
                'label' => __('General Information'),
                'type'  => 'fieldset'
            ]);

        $this->addField("status", [
                'label'  => __('Status'),
                'type'   => 'switcher',
                'value'  => 1,
                'values' => $this->_yesno
            ]);

        $this->addField("name", [
                'label' => __('Name'),
                'type'  => 'text'
            ]);

        $this->addField("classes", [
                'label' => __('CSS Classes'),
                'type'  => 'text'
            ]);

        $this->addField("link_type", [
                'label'  => __('Link Type'),
                'type'   => 'select',
                'value'  => 'custom_link',
                'values' => $this->_linkType
            ]);

        $this->addField("link", [
                'label'  => __('Custom Link'),
                'type'   => 'text',
                'note'   => __('<ul class="menu-note"><li>Enter hash(#) to make this item not clickable.</li><li>Support Magento 2 Store Directive - It helps get URLs of your store. Example: <b>{{store url=""}}</b>, <b>{{store url="contact"}}</b></li></ul>' ),
                'depend' => [
                    'field' => 'link_type',
                    'value' => 'custom_link'
                ]
            ]);

        $this->addField("category", [
                'label'  => __('Category'),
                'type'   => 'select',
                'values' => $categoryList,
                'depend' => [
                    'field' => 'link_type',
                    'value' => 'category_link'
                ]
            ]);

        $this->addField("target", [
                'label'  => __('Link Target'),
                'type'   => 'select',
                'value'  => '_self',
                'values' => $this->_linkTarget,
                'depend' => [
                    'field' => 'link_type'
                ]
            ]);

        $this->addField("show_icon", [
                'label'  => __('Show Icon'),
                'type'   => 'switcher',
                'value'  => 0,
                'values' => $this->_yesno
            ]);

        $this->addField("icon", [
                'label'  => __('Icon'),
                'type'   => 'image',
                'depend' => [
                    'field' => 'show_icon',
                    'value' => 1
                ]
            ]);

        $this->addField("hover_icon", [
                'label'  => __('Hover Icon'),
                'type'   => 'image',
                'depend' => [
                    'field' => 'show_icon',
                    'value' => 1
                ]
            ]);

        $this->addField("icon_position", [
                'label'  => __('Icon Position'),
                'type'   => 'select',
                'values' => $this->_iconPosition,
                'depend' => [
                    'field' => 'show_icon',
                    'value' => 1
                ]
            ]);

        $this->addField("icon_classes", [
                'label'  => __('Icon CCS Classes'),
                'type'   => 'icon',
                'depend' => [
                    'field' => 'show_icon',
                    'value' => 1
                ]
            ]);

        $this->addField("disable_bellow", [
                'label' => __('Disable Dimesion'),
                'type'  => 'text',
                'note'  => __('Enter the width(pixel) want to disable this item. Empty to disable this feature.<br/><strong>Bootstrap 3 Media Query Breakpoints: </strong><br/><ul class="menu-note" style="margin-left: 40px;"><li><span>Large Devices, Wide Screens: 1200px</span></li><li><span>Medium Devices, Desktops: 992px</span></li><li><span>Small Devices, Tablets: 768px</span></li><li><span>Extra Small Devices, Phones: 480px</span></li><li><span>iPhone Retina: 320px</span></li></ul>')
            ]);

        $this->addField("caret", [
                'label' => __('Caret'),
                'type'  => 'icon'
            ]);

        $this->addField("hover_caret", [
                'label' => __('Hove Caret'),
                'type'  => 'icon'
            ]);

        $this->addField("before_html", [
                'label'  => __('Before HTML'),
                'type'   => 'editor'
            ]);

        $this->addField("after_html", [
                'label'  => __('After HTML'),
                'type'   => 'editor'
            ]);

        $this->addField("label8", [
                'label' => __('Dropdown'),
                'type'  => 'fieldset'
            ]);

        $this->addField("is_group", [
                'label'  => __('Is Group'),
                'type'   => 'switcher',
                'value'  => 0,
                'values' => $this->_yesno,
                'note'   => __('Set to Yes and then both menu content and sub-menu items will be displayed in the same level.')
            ]);

        $this->addField("sub_width", [
                'label' => __('Width'),
                'type'  => 'text'
            ]);

        $this->addField("animation_in", [
                'label'  => __('Show Effect'),
                'type'   => 'select',
                'note'   => __('Check animations at <a href="https://daneden.github.io/animate.css" target="_blank">here</a>'),
                'values' => $this->_animationsIn,
            ]);

        $this->addField("animation_time", [
                'label'  => __('Show Duration(s)'),
                'type'   => 'text',
                'value'  => '0.5',
            ]);

        $this->addField("align", [
                'label'  => __('Alignment'),
                'type'   => 'select',
                'value'  => '3',
                'values' => $this->_alignType,
            ]);

        $this->addField("dropdown_bgcolor", [
                'label' => __('Background Color'),
                'type'  => 'color',
            ]);

        $this->addField("dropdown_bgimage", [
                'label' => __('Background Image'),
                'type'  => 'image'
            ]);

        $this->addField("dropdown_bgimagerepeat", [
                'label'  => __('Background Repeat'),
                'type'   => 'select',
                'value'  => '1',
                'values' => $this->_repeatType
            ]);

        $this->addField("dropdown_bgpositionx", [
                'label' => __('Background Position X'),
                'type'  => 'text'
            ]);

        $this->addField("dropdown_bgpositiony", [
                'label' => __('Background Position Y'),
                'type'  => 'text'
            ]);

        $this->addField("dropdown_inlinecss", [
                'label' => __('Inline CSS'),
                'type'  => 'textarea',
                'note'  => __('Semi-colon separated.')
            ]);

        $this->addField("label2", [
                'label' => __('Header'),
                'type'  => 'fieldset'
            ]);

        $this->addField("show_header", [
                'label'  => __('Enabled'),
                'type'   => 'switcher',
                'value'  => 0,
                'values' => $this->_yesno
            ]);

        $this->addField("header_html", [
                'label' => __('Top HTML'),
                'type'  => 'editor',
                'depend' => [
                'field' => 'show_header',
                'value' => 1
            ]
            ]);

        $this->addField("label3", [
                'label' => __('Left Block'),
                'type'  => 'fieldset'
            ]);

        $this->addField("show_left_sidebar", [
                'label'  => __('Enabled'),
                'type'   => 'switcher',
                'value'  => 0,
                'values' => $this->_yesno
            ]);

        $this->addField("left_sidebar_width", [
                'label'  => __('Width'),
                'type'   => 'text',
                'depend' => [
                'field'  => 'show_left_sidebar',
                'value'  => 1
            ]
            ]);

        $this->addField("left_sidebar_html", [
                'label'  => __('HTML'),
                'type'   => 'editor',
                'depend' => [
                'field'  => 'show_left_sidebar',
                'value'  => 1
            ]
            ]);

        $this->addField("label4", [
                'label' => __('Main Content'),
                'type'  => 'fieldset'
            ]);

        $this->addField("show_content", [
                'label'  => __('Enabled'),
                'type'   => 'switcher',
                'value'  => 1,
                'values' => $this->_yesno
            ]);

        $this->addField("content_width", [
                'label' => __('Width'),
                'type'  => 'text',
                'value' => '100%'
            ]);

        $this->addField("content_type", [
                'label'  => __('Main Content Type'),
                'type'   => 'select',
                'value'  => 'childmenu',
                'values' => $this->_menuType
            ]);

        $this->addField("tab_position", [
                'label'  => __('Tab Position'),
                'type'   => 'select',
                'values' => $this->_tabPosition,
                'value'  => 'left',
                'depend' => [
                    'field'  => 'content_type',
                    'value'  => 'dynamic'
                ]   
            ]);

        $this->addField("parentcat", [
                'label'  => __('Parent Category'),
                'type'   => 'select',
                'values' => $categoryList,
                'note'   => __('Get sub-categories'),
                'depend' => [
                'field'  => 'content_type',
                'value'  => 'parentcat'
            ]
            ]);

        $this->addField("child_col", [
                'label'  => __('Child Menu Column'),
                'type'   => 'select',
                'values' => $this->_chilCol,
                'value'  => 1
            ]);

        $this->addField("content_html", [
                'label'  => __('Content HTML'),
                'type'   => 'editor',
                'depend' => [
                'field'  => 'content_type',
                'value'  => 'content'
            ]
            ]);

        $this->addField("label5", [
                'label' => __('Right Block'),
                'type'  => 'fieldset'
            ]);

        $this->addField("show_right_sidebar", [
                'label'  => __('Enabled'),
                'value'  => 0,
                'type'   => 'switcher',
                'values' => $this->_yesno
            ]);

        $this->addField("right_sidebar_width", [
                'label' => __('Width'),
                'type'  => 'text',
                'depend' => [
                    'field' => 'show_right_sidebar',
                    'value' => 1
                    ]
            ]);

        $this->addField("right_sidebar_html", [
                'label' => __('HTML'),
                'type'  => 'editor',
                'depend' => [
                    'field' => 'show_right_sidebar',
                    'value' => 1
                    ]
            ]);

        $this->addField("label6", [
                'label' => __('Bottom Block'),
                'type'  => 'fieldset'
            ]);

        $this->addField("show_footer", [
                'label'  => __('Enabled'),
                'type'   => 'switcher',
                'value'  => 0,
                'values' => $this->_yesno
            ]);

        $this->addField("footer_html", [
                'label' => __('HTML'),
                'type'  => 'editor',
                'depend' => [
                    'field' => 'show_footer',
                    'value' => 1
                ]
            ]);

        $this->addField("menu_id", [
                'label' => __('Menu ID'),
                'class' => 'ves-hidden',
                'type'  => 'text'
            ]);

        $this->addField("item_id", [
                'label' => __('Item ID'),
                'class' => 'ves-hidden',
                'type'  => 'text'
            ]);

        $this->addField("label7", [
                'label' => __('Design'),
                'type'  => 'fieldset'
            ]);

        $this->addField("color", [
                'label' => __('Text Color'),
                'type'  => 'color'
            ]);

        $this->addField("hover_color", [
                'label' => __('Hover Text Color'),
                'type'  => 'color'
            ]);

        $this->addField("bg_color", [
                'label' => __('Background Color'),
                'type'  => 'color'
            ]);

        $this->addField("bg_hover_color", [
                'label' => __('Background Hover Color'),
                'type'  => 'color'
            ]);

        $this->addField("inline_css", [
                'label' => __('Inline CSS'),
                'type'  => 'textarea',
                'note'  => __('Semi-colon separated.')
            ]);
    }

public function getFields(){
    return $this->_fields;
}

public function addField($name, $params)
{
    if(isset($params['type']) && $params['type'] == 'separator'){
        $params['class'] = 'ves-separator';
    }
    $params['name'] = $name;
    $this->_fields[$name] = $params;
    if (!empty($params['renderer']) && $params['renderer'] instanceof \Magento\Framework\View\Element\AbstractBlock) {
        $this->_fields[$name]['renderer'] = $params['renderer'];
    }
}

protected function _getCellInputElementName($fieldName)
{
    return 'items[<%- _id %>][' . $fieldName . ']';
}

public function _optionToHtml($option)
{
    $class = $html = '';
    if(isset($option['class'])){
        $class = 'class="'.$option['class'].'"';
    }
    if (is_array($option['value'])) {
        $html = '<optgroup '.$class.' label="' . $option['label'] . '">';
        foreach ($option['value'] as $groupItem) {
            $html .= $this->_optionToHtml($groupItem);
        }
        $html .= '</optgroup>';
    } else {
        $html = '<option '.$class.'  value="' . $option['value'] . '"';
        $html .= '>' . $option['label'] . '</option>';
    }
    return $html;
}

    /**
     * @param string|null $route
     * @param array|null $params
     * @return string
     */
    public function getUrl($route, $params)
    {
        return $this->_backendUrlBuilder->getUrl($route, $params);
    }

    public function getStoreHtml()
    {
        $stores = $this->_systemStore->getStoreValuesForForm(false, true);
        $html = '<select class="select admin__control-select" data-bind="value: previewStore">';
        foreach ($stores as $option) {
            $html .= $this->_optionToHtml1($option);
        }
        $html .= '</select>';
        return $html;
    }

    public function _optionToHtml1($option)
    {
        $class = $html = '';
        if(isset($option['class'])){
            $class = 'class="'.$option['class'].'"';
        }
        if (is_array($option['value'])) {
            $html = '<optgroup '.$class.' label="' . $option['label'] . '">';
            foreach ($option['value'] as $groupItem) {
                $html .= $this->_optionToHtml1($groupItem);
            }
            $html .= '</optgroup>';
        } else {
            $store = $this->_storeManager->getStore($option['value']);
            $html = '<option '.$class.' data-url="' . $store->getBaseUrl() . 'megamenu/preview"  value="' . $option['value'] . '"';
            $html .= '>' . $option['label'] . '</option>';
        }
        return $html;
    }

    public function renderCellTemplate($fieldName){
        $fields    = $this->getFields();
        $inputName = $this->_getCellInputElementName($fieldName);
        $field     = $fields[$fieldName];
        $mediaUrl  = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $id        = 'option-' . $fieldName;
        $classes   = 'ves-' . $id;

        if (isset($field['type'])) {
            $html = '';
            switch ($field['type']) {
                case 'textarea':
                $html = '<textarea id="'.$id.'" class="'.$classes.'"  data-bind="value: '.$fieldName.'"></textarea>';
                $html .= '<div class="field-cm">'.(isset($field['note'])?$field['note']:'').'</div>';
                break;
                case 'text':
                $html = '<input type="text" id="'.$id.'" class="'.$classes.'" data-bind="value: ' . $fieldName . '"/>';
                $html .= '<div class="field-cm">'.(isset($field['note'])?$field['note']:'').'</div>';
                break;
                case 'icon':
                $html = '<div class="ves-caret">';
                $html .= '<i data-bind="attr:{class: ' . $fieldName . '}, click: $parents[$parents.length-2].showIconList.bind($data,\''.$fieldName.'\')"></i>';
                $html .= '<input type="text" id="' . $id . '" class="' . $classes . '" data-bind="value: ' . $fieldName . '"/>';
                $html .= '<button data-bind="click: $parents[$parents.length-2].showIconList.bind($data,\''.$fieldName.'\')">Insert Icon</button>';
                $html .= '</div>';
                $html .= '<div class="field-cm">'.(isset($field['note'])?$field['note']:'').'</div>';
                break;
                case 'select':
                $html = '<select id="'.$id.'" class="'.$classes.'" data-bind="value: '.$fieldName.'">';
                if(isset($field['values'])){
                    foreach ($field['values'] as $option) {
                        $html .= $this->_optionToHtml($option);
                    }
                }
                $html .= '</select>';
                $html .= '<div class="field-cm">'.(isset($field['note'])?$field['note']:'').'</div>';
                break;
                case 'switcher':
                $html = '<div class="admin__actions-switch" data-bind="attr: {\'data-value\': ' . $fieldName . '}" data-role="switcher ">
                    <input class="admin__actions-switch-checkbox ' . $classes . '"
                           type="checkbox"
                           data-bind="checked: ' . $fieldName . ', attr: { enabled: ' . $fieldName . '}">
                    <label class="admin__actions-switch-label"
                           data-bind="attr: { for: ' . $fieldName . ' }, click: $parents[$parents.length-2].switcher.bind($data,\''.$fieldName.'\')">
                        <span data-bind="attr: {
                                   \'data-text-on\': $t(\'Yes\'),
                                   \'data-text-off\': $t(\'No\')
                              }"
                              class="admin__actions-switch-text"></span>
                    </label>
                </div>';
                $html .= '<div class="field-cm">'.(isset($field['note'])?$field['note']:'').'</div>';
                break;
                case 'image':
                $editorId = 'editor'.time().rand();
                $html = '<div class="preview-image">';
                $html .= '<img data-bind="attr:{src: '.$fieldName.'}" />';
                $html .= '</div>';
                $html .= '<div class="input-media">';
                $html .= '<input data-bind="{value: '.$fieldName.'}" class="'.$classes.'" id="'.$editorId.'" type="text"/>';

                $html .= $this->_layout->createBlock(
                    'Magento\Backend\Block\Widget\Button',
                    '',
                    [
                    'data' => [
                    'label' => __('Insert Image'),
                    'type' => 'button',
                    'class' => 'action-wysiwyg',
                    'onclick' => "MediabrowserUtility.openDialog('" . $this->_backendData->getUrl('cms/wysiwyg_images/index',
                        [
                        'target_element_id'=>$editorId,
                        'as_is' => 'ves'
                        ]
                        ) . "', null, null,'" . $this->escaper->escapeQuote(
                        __('Upload Image'),
                        true
                        ) . "', '" . '' . "');",
                        ]
                        ]
                        )->toHtml();
                $html .= '</div>';
                $html .= '<div class="field-cm">'.(isset($field['note'])?$field['note']:'').'</div>';
                break;
                case 'editor':
                $tinyMCEConfig = json_encode($this->_wysiwygConfig->getConfig());
                $editorId = 'editor'.time().rand();
                $html = '<textarea id="'.$editorId.'" data-key=' . $fieldName . ' class="'.$classes.' ves-editor" style="height:400px;"  data-bind="{value: '.$fieldName.', if: status==1}" data-ui-id="product-tabs-attributes-tab-fieldset-element-textarea-'.$editorId.' aria-hidden="true"></textarea>';
                $html .= $this->_layout->createBlock(
                    'Magento\Backend\Block\Widget\Button',
                    '',
                    [
                    'data' => [
                    'label' => __('WYSIWYG Editor'),
                    'type' => 'button',
                    'class' => 'action-wysiwyg',
                    'style' => 'margin-top: 10px;',
                    'onclick' => 'megamenuWysiwygEditor.open(\'' . $this->_backendData->getUrl(
                        'vesmegamenu/product/wysiwyg'
                        ) . '\', \''.$editorId.'\' , ' . json_encode($tinyMCEConfig) . ')',
                    ]
                    ]
                    )->toHtml();
                $html .= '<div class="field-cm">'.(isset($field['note'])?$field['note']:'').'</div>';
                break;
                case 'separator':
                $html = '<div class="separator"></div>';
                $html .= '<div class="field-cm">'.(isset($field['note'])?$field['note']:'').'</div>';
                break;
                case 'color':
                $id = 'option-'.time().rand();
                $html = '<input type="text" class="ip-color '.$classes.'" id="'.$id.'"  data-bind="value: '.$fieldName.'"/>';
                $mcPath = $mediaUrl.'ves/megamenu';
                $html .= '<script>
                require([
                "jquery",
                "Ves_Megamenu/js/mcolorpicker/mcolorpicker.min"
                ], function ($) {
                    jQuery(document).ready(function($){
                        var folderImageUrl = "'.$mcPath.'/images";
                        jQuery.noConflict();
                        jQuery.fn.mColorPicker.init.replace = false;
                        jQuery.fn.mColorPicker.defaults.imageFolder = "'. $mcPath .'/images/";
                        jQuery.fn.mColorPicker.init.allowTransparency = true;
                        jQuery.fn.mColorPicker.init.showLogo = false;
                        jQuery("#' . $id . '").attr("data-hex", true).width("250px").mColorPicker().change(function(){  });
                        jQuery("#mColorPickerImg").css("background-image","url('.$mcPath.'/images/picker.png)");
                        jQuery("#mColorPickerFooter").css("background-image","url('.$mcPath.'/images/grid.gif)");
                        jQuery("#mColorPickerFooter img").attr({"src":"'.$mcPath.'/images/meta100.png"});
                        jQuery(document).on("click", "#'.$id.'", function(){
                            jQuery("#icp_'. $id .' img").trigger("click");
                        });
                        jQuery(document).on("change", "#'.$id.'", function(){
                            var value = jQuery(this).val();
                            if(value == "transparent"){
                                jQuery(this).css("color", "#000");
                            }
                        }).change();
                    });
                });</script>';
                $html .= '<div class="field-cm">'.(isset($field['note'])?$field['note']:'').'</div>';
                break;
            }
            if($html) return $html;
        }
        return '<input type="text"  id="'.$this->_getCellInputElementId('<%- _id %>', $fieldName).'" name="'.$inputName.'" class="' .(isset($field['class']) ? $field['class'] : 'input-text') . '" ' . (isset($field['style']) ? ' style="' . $field['style'] . '"' : '') . ' />';
    }

    public function getWysiwygConfig(){
        $config = [];
        $config['add_variables']  = true;
        $config['add_widgets']    = true;
        $config['add_directives'] = true;
        $wysiwgConfig = $this->_wysiwygConfig->getConfig($config)->getData();
        $wysiwgConfig['forced_root_block'] = false;
        return $wysiwgConfig;
    }
}