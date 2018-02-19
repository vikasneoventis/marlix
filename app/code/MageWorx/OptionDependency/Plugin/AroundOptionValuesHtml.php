<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Plugin;

use \Magento\Catalog\Block\Product\View\Options\Type\Select;
use \Zend\Stdlib\StringWrapper\MbString;

class AroundOptionValuesHtml
{
    /**
     * @var MbString
     */
    protected $mbString;

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
    public function aroundGetValuesHtml(Select $subject, \Closure $proceed)
    {
        $result = $proceed();
        $option = $subject->getOption();

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;

        $this->mbString->setEncoding('UTF-8', 'html-entities');
        $result = $this->mbString->convert($result);

        libxml_use_internal_errors(true);
        $dom->loadHTML($result);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        $count = 1;
        foreach ($option->getValues() as $value) {
            $count++;

            $select =
                $xpath->query('//option[@value="'.$value->getId().'"]')->item(0);

            $input =
                $xpath->query('//div/div[descendant::label[@for="options_'.$option->getId().'_'.$count.'"]]')->item(0);

            $element = $select ? $select : $input;

            if ($element) {
                $element->setAttribute("option_type_id", $value->getMageworxOptionTypeId());
            }
        }

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
        $innerHTML= '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }
}
