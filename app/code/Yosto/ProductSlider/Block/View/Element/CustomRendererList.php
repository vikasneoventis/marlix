<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\ProductSlider\Block\View\Element;


use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\RendererList;

/**
 * Class CustomRendererList
 * @package Yosto\ProductSlider\Block\View\Element
 */
class CustomRendererList extends RendererList
{

    protected $_widgetName;

    /**
     * Retrieve renderer by code
     *
     * @param string $type
     * @param string $default
     * @param string $rendererTemplate
     * @return bool|AbstractBlock
     * @throws \RuntimeException
     */
    public function getRenderer($type, $default = null, $rendererTemplate = null)
    {
        /** @var \Magento\Framework\View\Element\Template $renderer */
        $renderer = null;
        if ($type == Configurable::TYPE_CODE) {

            $renderer = $this->getLayout()
                ->createBlock('Yosto\ProductSlider\Block\Product\Renderer\Listing\Configurable')
                ->setWidgetName($this->getWidgetName())
                ->setTemplate('Yosto_ProductSlider::product/listing/renderer.phtml');

        } else {
            $renderer = $this->getChildBlock($type) ?: $this->getLayout()->createBlock('Magento\Framework\View\Element\Template');
        }
        if (!$renderer instanceof BlockInterface) {
            throw new \RuntimeException('Renderer for type "' . $type . '" does not exist.');
        }
        $renderer->setRenderedBlock($this);

        if (!isset($this->rendererTemplates[$type])) {
            $this->rendererTemplates[$type] = $renderer->getTemplate();
        } else {
            $renderer->setTemplate($this->rendererTemplates[$type]);
        }

        if ($rendererTemplate) {
            $renderer->setTemplate($rendererTemplate);
        }
        return $renderer;
    }

    public function getWidgetName()
    {
        return $this->_widgetName;
    }

    public function setWidgetName($widgetName)
    {
        $this->_widgetName = $widgetName;
        return $this;
    }

}