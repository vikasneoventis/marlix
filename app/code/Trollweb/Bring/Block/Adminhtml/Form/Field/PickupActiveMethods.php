<?php

namespace Trollweb\Bring\Block\Adminhtml\Form\Field;

use Magento\Framework\DataObject;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;


class PickupActiveMethods extends AbstractFieldArray
{
    private $methodsRenderer;
    private $allowFreeShippingRenderer;
    
    private function getMethodsRenderer()
    {
        if (!$this->methodsRenderer) {
            $this->methodsRenderer = $this->getLayout()->createBlock(PickupMethods::class, '', [
                'data' => ['is_render_to_js_template' => true]
            ]);
        }
        return $this->methodsRenderer;
    }

    private function getAllowFreeShippingRenderer()
    {
        if (!$this->allowFreeShippingRenderer) {
            $this->allowFreeShippingRenderer = $this->getLayout()->createBlock(Checkbox::class, '', [
                'data' => ['is_render_to_js_template' => true],
                'id' => 'allow_free_shipping',
            ]);
        }
        return $this->allowFreeShippingRenderer;
    }

    protected function _prepareToRender()
    {
        $this->addColumn('method_id', [
            'label' => __('Method'),
            'renderer' => $this->getMethodsRenderer(),
        ]);

        $this->addColumn('custom_price', [
            'label' => __('Custom price'),
        ]);

        //$this->addColumn('min_weight', [
        //    'label' => __('Min weight'),
        //]);

        //$this->addColumn('max_weight', [
        //    'label' => __('Max weight'),
        //]);

        $this->addColumn('allow_free_shipping', [
            'label' => __('Allow free shipping'),
            'renderer' => $this->getAllowFreeShippingRenderer(),
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add method');
    }

    protected function _prepareArrayRow(DataObject $row)
    {
        $methodId = $row->getData("method_id");
        $options = [];

        if ($methodId) {
            $options['option_' . $this->getMethodsRenderer()->calcOptionHash($methodId)] = 'selected="selected"';
        }

        $row->setData('allow_free_shipping', $row->getData('allow_free_shipping') ? 'checked' : '');
        $row->setData('option_extra_attrs', $options);
    }
}
