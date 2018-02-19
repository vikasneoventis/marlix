<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinks\Model\Crosslink\Source;

use MageWorx\SeoCrossLinks\Model\Crosslink;
use MageWorx\SeoCrossLinks\Model\Source;

/**
 * Used in creating options for default "link to" config value selection
 *
 */
class LinkTo extends Source
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => Crosslink::REFERENCE_TO_STATIC_URL,     'label' => __('Custom URL')],
            ['value' => Crosslink::REFERENCE_TO_PRODUCT_BY_SKU, 'label' => __('To Product by SKU')],
            ['value' => Crosslink::REFERENCE_TO_CATEGORY_BY_ID, 'label' => __('To Category by ID')],
        ];
    }
}
