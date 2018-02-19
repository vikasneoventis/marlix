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

namespace Ves\Megamenu\Block;

class Menu extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Ves\Megamenu\Model\Menu
     */
    protected $_menu;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Ves\Megamenu\Helper\MobileDetect
     */
    protected $_mobileDetect;

    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context            
     * @param \Ves\Megamenu\Helper\Data                        $helper             
     * @param \Ves\Megamenu\Model\Menu                         $menu               
     * @param \Magento\Customer\Model\Session                  $customerSession    
     * @param \Ves\Megamenu\Helper\MobileDetect                $mobileDetectHelper              
     * @param array                                            $data               
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Megamenu\Helper\Data $helper,
        \Ves\Megamenu\Model\Menu $menu,
        \Magento\Customer\Model\Session $customerSession,
        \Ves\Megamenu\Helper\MobileDetect $mobileDetectHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper          = $helper;
        $this->_menu            = $menu;
        $this->_customerSession = $customerSession;
        $this->_mobileDetect    = $mobileDetectHelper;
        $this->httpContext = $httpContext;
    }
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [\Ves\Megamenu\Model\Menu::CACHE_TAG,
            ], ]);
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $menuId = $this->getData('id');
        $menuId = $menuId?$menuId:0;
        $code = $this->getConfig('alias');

        $conditions = $code.".".$menuId;

        return [
        'VES_MEGAMENU_MENU_BLOCK',
        $this->_storeManager->getStore()->getId(),
        $this->_design->getDesignTheme()->getId(),
        $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
        $conditions
        ];
    }

    public function getMobileTemplateHtml($menuAlias)
    {
        $html = '';
        if($menuAlias){
            $html = $this->getLayout()->createBlock('Ves\Megamenu\Block\MobileMenu')->setData('alias', $menuAlias)->toHtml();
        }
        return $html;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Ves\Megamenu\Model\Menu::CACHE_TAG . '_' . $this->getBlockId()];
    }

    public function getMobileDetect() {
        return $this->_mobileDetect;
    }

    public function _toHtml() {
        if (!$this->getTemplate()) {
            $this->setTemplate("Ves_Megamenu::widget/menu.phtml");
        }
        $store = $this->_storeManager->getStore();
        $html = $menu = '';
        if ($menuId = $this->getData('id')) {
            $menu = $this->_menu->setStore($store)->load((int)$menuId);
            if ($menu->getId() != $menuId) {
                return;
            }
        } else if ($alias = $this->getData('alias')) {
            $request = $this->getRequest();

            $menu = $this->_menu->setStore($store)->load(addslashes($alias));
            if ($menu->getAlias() != $alias) {
                return;
            }
        }

        if ($menu) {
            $customerGroups = $menu->getData('customer_group_ids');
            $customerGroupId = (int)$this->_customerSession->getCustomerGroupId();
            if(is_array($customerGroups) && !in_array($customerGroupId, $customerGroups)) return;
        }

        if ($menu && $menu->getStatus()) {
            $this->setData("menu", $menu);
        }
        return parent::_toHtml();
    }
}