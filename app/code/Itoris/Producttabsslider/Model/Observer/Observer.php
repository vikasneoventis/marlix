<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_TABS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\Producttabsslider\Model\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Block\Product\View\Description;
use Magento\Framework\View\Result\Layout;
class Observer implements ObserverInterface{
    protected $_scopeConfig;
    protected $tabs=false;
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }
    protected $bool=true;
    protected $_objectManager;
    const BLOCK_HTML_CACHE_TAG = 'block_filter_product_tabs';
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Framework\View\Element\Template $block */
        /** @var \Magento\Framework\View\Layout $layout */
        $objectManager =$this->_objectManager= \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Itoris\Producttabsslider\Helper\ExtensionConfig');
       if ($helper->isEnabled()) {
            $layout = $observer->getLayout();

            $block = $layout->getBlock('product.info.details');
            if ($block && ($block->getType() == 'Magento\Catalog\Block\Product\View\Description\Interceptor' || $block->getType() == 'Magento\Catalog\Block\Product\View\Description')) {
                //$cache = $this->_objectManager->create('Magento\Framework\App\CacheInterface');
                $layout->unsetElement('product.info.description');
                //$info = $layout->createBlock('Itoris\Producttabsslider\Block\Frontend\BlockInfo', 'childTabs');
                //$layout->unsetElement('product.info.details');
                //$block = $layout->getBlock('product.info.itoris.details');
                $layout->unsetElement('product.attributes');
                $layout->unsetElement('product.attributes');
                $layout->unsetElement('reviews.tab');
                $staticBlock = $objectManager->create('Itoris\Producttabsslider\Block\Frontend\Block')->setProduct($block->getProduct()->getId());
                $parsehtml = $staticBlock->getParseTextsTabs();
                $i = 0;
                foreach ($parsehtml as $key => $pshtml) {
                    $info = $objectManager->create('Itoris\Producttabsslider\Block\Frontend\BlockInfo', ['data' => ['title' => $key]]);
                    $info->setParseText($pshtml);
                    //$block->setTemplate('Itoris_Producttabsslider::product/details.phtml');
                    $layout->addBlock($info, $key . '_' . $i, 'product.info.details', $key . '_' . $i);
                    $layout->addToParentGroup($key . '_' . $i, 'detailed_info');
                    $i++;
                }
            }
        }
   }

}