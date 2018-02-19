<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\Template\Manager;

use MageWorx\SeoXTemplates\Model\ResourceModel\Template\Category\CollectionFactory;

/**
 * Cache status manager
 */
class Category implements \MageWorx\SeoXTemplates\Model\Template\ManagerInterface
{

    /**
     *
     * @var \MageWorx\SeoXTemplates\Model\ResourceModel\Template\Category\CollectionFactory
     */
    protected $templateCategoryCollectionFactory;

    /**
     *
     * @param CollectionFactory $templateCategoryCollectionFactory
     */
    public function __construct(CollectionFactory $templateCategoryCollectionFactory)
    {
        $this->templateCategoryCollectionFactory = $templateCategoryCollectionFactory;
    }

    /**
     * @return array
     */
    public function getAvailableIds()
    {
        /** @var \MageWorx\SeoXTemplates\Model\ResourceModel\Template\Category\Collection */
        $collection = $this->templateCategoryCollectionFactory->create();

        return $collection->getAllIds();
    }
}
