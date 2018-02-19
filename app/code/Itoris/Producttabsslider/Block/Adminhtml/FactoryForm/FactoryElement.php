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


namespace Itoris\Producttabsslider\Block\Adminhtml\FactoryForm;
class FactoryElement extends \Magento\Framework\Data\Form\Element\Factory
{
    public function create($elementType, array $config = [])
    {

        if (in_array($elementType, $this->_standardTypes)) {
            if($elementType=='fieldset' || ($elementType=='editor') && isset($config['data']['editor_disabled']) && $config['data']['editor_disabled']==true){

                $className = 'Itoris\Producttabsslider\Block\Adminhtml\\' . ucfirst($elementType);
            }else{
                $className = 'Magento\Framework\Data\Form\Element\\' . ucfirst($elementType);
            }

        } else {
            $className = $elementType;
        }

        $element = $this->_objectManager->create($className, $config);
        if (!$element instanceof \Magento\Framework\Data\Form\Element\AbstractElement) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'n extend \Magento\Framework\Data\Form\Element\AbstractElement'
            );
        }
        return $element;
    }
}