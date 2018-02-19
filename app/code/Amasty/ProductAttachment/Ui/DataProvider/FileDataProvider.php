<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\ProductAttachment\Ui\DataProvider;


class FileDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    protected function searchResultToOutput(\Magento\Framework\Api\Search\SearchResultInterface $searchResult)
    {
        $searchResult->addProducts();
        return parent::searchResultToOutput($searchResult);
    }
}
