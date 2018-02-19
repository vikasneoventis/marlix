<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Block\Head;

abstract class SocialMarkup extends \MageWorx\SeoMarkup\Block\Head
{
    /**
     *
     * {@inheritDoc}
     */
    protected function _toHtml()
    {
        return $this->getMarkupHtml();
    }
}
