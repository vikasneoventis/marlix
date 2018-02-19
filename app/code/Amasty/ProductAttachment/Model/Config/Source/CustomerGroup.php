<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model\Config\Source;

class CustomerGroup implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Customer\Model\Customer\Attribute\Source\Group
     */
    protected $groupSource;

    public function __construct(\Magento\Customer\Model\Customer\Attribute\Source\Group $groupSource)
    {
        $this->groupSource = $groupSource;
    }

    public function toOptionArray()
    {
        return array_merge(
            [['value' => "0", 'label' => __('NOT LOGGED IN')]],
            $this->groupSource->getAllOptions()
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('No'), 1 => __('Yes')];
    }
}
