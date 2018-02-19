<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Cron;

class GenerateCategoryTemplate extends GenerateTemplate
{
    /**
     * Dispatch event for category meta title
     */
    public function generateMetaTitle()
    {
        $this->generateEntityByTypeId(\MageWorx\SeoXTemplates\Model\Template\Category::TYPE_CATEGORY_META_TITLE);
    }

    /**
     * Dispatch event for category meta description
     */
    public function generateMetaDescription()
    {
        $this->generateEntityByTypeId(\MageWorx\SeoXTemplates\Model\Template\Category::TYPE_CATEGORY_META_DESCRIPTION);
    }

    /**
     * Dispatch event for category meta keywords
     */
    public function generateMetaKeywords()
    {
        $this->generateEntityByTypeId(\MageWorx\SeoXTemplates\Model\Template\Category::TYPE_CATEGORY_META_KEYWORDS);
    }

    /**
     * Dispatch event for category description
     */
    public function generateDescription()
    {
        $this->generateEntityByTypeId(\MageWorx\SeoXTemplates\Model\Template\Category::TYPE_CATEGORY_DESCRIPTION);
    }

    /**
     * Dispatch event for category SEO name
     */
    public function generateSeoName()
    {
        $this->generateEntityByTypeId(\MageWorx\SeoXTemplates\Model\Template\Category::TYPE_CATEGORY_SEO_NAME);
    }
}
