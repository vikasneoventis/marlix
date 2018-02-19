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

use Magento\Catalog\Model\Product;

class Description extends \Magento\Catalog\Block\Product\View\Description
{

    protected $_template='Itoris_Producttabsslider::product/view/attribute.phtml';
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data=['at_call'=>'getDescription','at_code'=>'description','css_class'=>'description','at_label'=>'none','title'=>'Details']) {

        $data=['at_call'=>'getDescription','at_code'=>'description','css_class'=>'description','at_label'=>'none','title'=>'Details'];
        parent::__construct($context,$registry, $data);
    }

}