<?php
 
namespace Bss\AdminPreview\Model\Config\Source;
 
use Magento\Framework\Option\ArrayInterface;
 
class BackendEditLinks implements ArrayInterface
{
    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];
 
        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
 
        return $ret;
    }
 
    /**
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {
        return [
            'product' => __('Product'),
            'category' => __('Category'),
            'cms' => __('Cms Page'),
            'staticblock' => __('Static Block'),
            'customer' => __('Customer')
        ];
    }
}