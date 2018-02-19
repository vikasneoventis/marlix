<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\Template\Manager;

use MageWorx\SeoXTemplates\Model\ResourceModel\Template\Product\CollectionFactory;

/**
 * Cache status manager
 */
class Product implements \MageWorx\SeoXTemplates\Model\Template\ManagerInterface
{

    /**
     *
     * @var \MageWorx\SeoXTemplates\Model\ResourceModel\Template\Product\CollectionFactory
     */
    protected $templateProductCollectionFactory;

    /**
     *
     * @param CollectionFactory $templateProductCollectionFactory
     */
    public function __construct(CollectionFactory $templateProductCollectionFactory)
    {
        $this->templateProductCollectionFactory = $templateProductCollectionFactory;
    }

    /**
     * @return array
     */
    public function getAvailableIds()
    {
        /** @var \MageWorx\SeoXTemplates\Model\ResourceModel\Template\Product\Collection */
        $collection = $this->templateProductCollectionFactory->create();

        return $collection->getAllIds();
    }
}
