<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Plugin;

use \Magento\Catalog\Block\Product\View\Options;
use \Zend\Stdlib\StringWrapper\MbString;

/**
 * Class AroundSelectValuesHtml.
 * This plugin add stock message to options, disable|hide options.
 *
 * @package MageWorx\OptionInventory\Plugin\Product\View\Options\Type
 */
class AroundOptionsHtml
{
    /**
     * @var MbString
     */
    protected $mbString;
    
    /**
     * These nodes should be found and filled
     * before return to the page.
     *
     * @var array
     */
    protected $voidNodes = [
        'textarea'
    ];

    /**
     * @param MbString $mbString
     */
    public function __construct(
        MbString $mbString
    ) {
        $this->mbString = $mbString;
    }


    /**
     * @param Select $subject
     * @param \Closure $proceed
     * @return string
     */
    public function aroundGetOptionHtml(Options $subject, \Closure $proceed, \Magento\Catalog\Model\Product\Option $option)
    {
        $result = $proceed($option);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;

        $this->mbString->setEncoding('UTF-8', 'html-entities');
        $result = $this->mbString->convert($result);

        libxml_use_internal_errors(true);
        $dom->loadHTML($result);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // find and prepare void nodes
        $query = '//*[not(node())]';
        $nodes = $xpath->query($query);

        foreach ($nodes as $node) {
            if (in_array($node->nodeName, $this->voidNodes)) {
                // Fill the found node with 'NOT_VOID' comment. Remove this comment later before return $result.
                $node->appendChild(new \DOMComment('NOT_VOID'));
            }
        }

        $xpath->query('//div')->item(0)->setAttribute("option_id", $option->getMageworxOptionId());

        $resultBody = $dom->getElementsByTagName('body')->item(0);
        $result = $this->getInnerHtml($resultBody);

        return str_replace('<!--NOT_VOID-->', '', $result);
    }

    /**
     * @param \DOMElement $node
     * @return string
     */
    protected function getInnerHtml(\DOMElement $node)
    {
        $innerHTML= '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);

            if (strpos($innerHTML, '</script>') !== false) {
                $innerHTML = str_replace(
                    ['<script type="text/x-magento-init"><![CDATA[', ']]></script>'],
                    ['<script type="text/x-magento-init">', '</script>'],
                    $innerHTML
                );
            }
        }

        return $innerHTML;
    }
}
