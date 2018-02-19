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
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Collection as ElementCollection;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Itoris\Producttabsslider\Block\Adminhtml\FactoryForm\FactoryElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Profiler;

class FormElementFactory extends \Magento\Framework\Data\Form
{
    public function __construct(
        FactoryElement $factoryElement,
        ElementCollectionFactory $factoryCollection,
        FormKey $formKey,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection,$formKey, $data);
        $this->_allElements = $this->_factoryCollection->create(['container' => $this]);
        $this->formKey = $formKey;
    }

}