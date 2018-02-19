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


namespace Itoris\Producttabsslider\Block\Adminhtml;

use Magento\Framework\Data\Form;
use Magento\Framework\Escaper;
 use Itoris\Producttabsslider\Block\Adminhtml\FactoryForm\FactoryElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
class Fieldset extends \Magento\Framework\Data\Form\Element\Fieldset
{
    protected $factoryCustom;
    public function __construct(
        FactoryElement $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_factoryElement=$factoryElement;
        $this->_renderer = Form::getFieldsetRenderer();
        $this->setType('fieldset');
        if (isset($data['advancedSection'])) {
            $this->setAdvancedLabel($data['advancedSection']);
        }
    }

    public function addField($elementId, $type, $config, $after = false, $isAdvanced = false)
    {
        if (isset($this->_types[$type])) {
            $type = $this->_types[$type];
        }
        $element = $this->_factoryElement->create($type, ['data' => $config]);
        $element->setId($elementId);
        $this->addElement($element, $after);
        if ($renderer = Form::getFieldsetElementRenderer()) {
            $element->setRenderer($renderer);
        }
        $element->setAdvanced($isAdvanced);
        // $this->increment++;
        //if($this->increment==6)
        // die;
        return $element;
    }

}