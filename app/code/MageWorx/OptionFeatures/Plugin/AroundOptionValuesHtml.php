<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Plugin;

use Magento\Catalog\Block\Product\View\Options\Type\Select;
use Magento\Framework\App\RequestInterface as Request;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\State;
use Zend\Stdlib\StringWrapper\MbString;

class AroundOptionValuesHtml
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var MbString
     */
    protected $mbString;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $backendQuoteSession;

    /**
     * @param Request $request
     * @param Helper $helper
     * @param State $state
     * @param Cart $cart
     * @param MbString $mbString
     */
    public function __construct(
        Request $request,
        Cart $cart,
        State $state,
        Helper $helper,
        MbString $mbString,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession
    ) {
        $this->request = $request;
        $this->cart = $cart;
        $this->state = $state;
        $this->helper = $helper;
        $this->mbString = $mbString;
        $this->backendQuoteSession = $backendQuoteSession;
    }

    /**
     * @param Select $subject
     * @param \Closure $proceed
     * @return string
     */
    public function aroundGetValuesHtml(Select $subject, \Closure $proceed)
    {
        $result = $proceed();

        if (!$this->helper->isQtyInputEnabled() || !$result) {
            return $result;
        }

        $option = $subject->getOption();

        $optionsQty = [];
        if ($this->request->getControllerName() != 'product') {
            $quoteItemId = (int)$this->request->getParam('id');
            if ($quoteItemId) {
                if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
                    $quoteItem = $this->backendQuoteSession->getQuote()->getItemById($quoteItemId);
                } else {
                    $quoteItem = $this->cart->getQuote()->getItemById($quoteItemId);
                }
                if ($quoteItem) {
                    $buyRequest = $quoteItem->getBuyRequest();
                    if ($buyRequest) {
                        $optionsQty = $buyRequest->getOptionsQty();
                    }
                }
            }
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;

        $this->mbString->setEncoding('UTF-8', 'html-entities');
        $result = $this->mbString->convert($result);

        $dom->loadHTML($result);
        $body = $dom->documentElement->firstChild;

        if ($option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX && $option->getQtyInput()) {
            $count = 1;
            foreach ($option->getValues() as $value) {
                $count++;

                $optionValueQty = $this->getOptionQty($optionsQty, $option, $value->getOptionTypeId());

                $qtyInput = '<div class="label-qty" style="display: inline-block; padding: 5px; margin-left: 3em"><b>Qty: </b>';
                $qtyInput .= '<input name="options_qty['.$option->getId().']['.$value->getOptionTypeId().']"';
                $qtyInput .= ' id="options_' . $option->getId() .'_'. $value->getOptionTypeId().'_qty"';
                $qtyInput .= ' class="qty mageworx-option-qty" type="number" value="'.$optionValueQty.'" min="0" disabled';
                $qtyInput .= ' style="width: 3em; text-align: center; vertical-align: middle;"';
                $qtyInput .= ' data-parent-selector="options['.$option->getId().']['.$value->getOptionTypeId().']"';
                $qtyInput .= ' />';
                $qtyInput .= '</div>';

                $tpl = new \DOMDocument('1.0', 'UTF-8');
                $tpl->loadHtml($qtyInput);

                $xpath = new \DOMXPath($dom);
                $idString = 'options_'.$option->getId().'_'.$count;
                $input = $xpath->query("//*[@id='$idString']")->item(0);

                $input->setAttribute('style', 'vertical-align: middle');
                $input->parentNode->appendChild($dom->importNode($tpl->documentElement, true));
            }
        } else {
            if ($option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE ||
                $option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO ||
                !$option->getQtyInput()
            ) {
                $qtyInput = '<input name="options_qty[' . $option->getId() . ']" id="options_' . $option->getId() . '"';
                $qtyInput .= ' class="qty mageworx-option-qty" type="hidden" value="1"';
                $qtyInput .= ' style="width: 3em; text-align: center; vertical-align: middle;"';
                $qtyInput .= ' data-parent-selector="options[' . $option->getId() . ']"';
                $qtyInput .= ' />';
            } else {
                $optionQty = $this->getOptionQty($optionsQty, $option, $option->getId());

                $qtyInput = '<div class="label-qty" style="display: inline-block; padding: 5px;"><b>Qty: </b>';
                $qtyInput .= '<input name="options_qty[' . $option->getId() . ']" id="options_' . $option->getId() . '"';
                $qtyInput .= ' class="qty mageworx-option-qty" type="number" value="' . $optionQty . '" min="0" disabled';
                $qtyInput .= ' style="width: 3em; text-align: center; vertical-align: middle;"';
                $qtyInput .= ' data-parent-selector="options[' . $option->getId() . ']"';
                $qtyInput .= ' />';
                $qtyInput .= '</div>';
            }

            $tpl = new \DOMDocument();
            $tpl->loadHtml($qtyInput);
            $body->appendChild($dom->importNode($tpl->documentElement, true));
        }

        libxml_clear_errors();

        $resultBody = $dom->getElementsByTagName('body')->item(0);
        $result = $this->getInnerHtml($resultBody);

        return $result;
    }

    /**
     * @param \DOMElement $node
     * @return string
     */
    protected function getInnerHtml(\DOMElement $node)
    {
        $innerHTML = '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }

    protected function getOptionQty($optionsQty, $option, $optionValue)
    {
        $qty = 0;
        if (isset($optionsQty[$option->getOptionId()])) {
            if (!is_array($optionsQty[$option->getOptionId()])) {
                $qty = $optionsQty[$option->getOptionId()];
            } else {
                if (isset($optionsQty[$option->getOptionId()][$optionValue])) {
                    $qty = $optionsQty[$option->getOptionId()][$optionValue];
                }
            }
        }
        return $qty;
    }
}
