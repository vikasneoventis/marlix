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

namespace Itoris\Producttabsslider\Block\Frontend;
class Block extends \Magento\Cms\Block\Block

{
    protected $_isScopePrivate=true;
    protected $objectManager;
    const CACHE_TAG = 'block_filter_product_tabs';
    public function getParseTextsTabs()
    {


        $this->_isScopePrivate=true;
        $this->_cacheState=false;
        $this->objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $registry \Magento\Framework\Registry*/
        $registry = $this->objectManager->create('Magento\Framework\Registry');
        $idProduct=$this->getProduct();
       // $layout=$this->getLayout()->createBlock('Itoris\Producttabsslider\Block\Frontend\Review','itoris.review.producttabs');
        $helper= $this->objectManager->create('Itoris\Producttabsslider\Helper\Block');
        return $helper->getHtml($this->_filterProvider,$idProduct,$this->_storeManager,$this);
    }

}