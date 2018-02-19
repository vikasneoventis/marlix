<?php
 
namespace Bss\AdminPreview\Model\Config\Source;
 
use Magento\Framework\Option\ArrayInterface;
 
class ProductColumns implements ArrayInterface
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
            'sku' => __('Sku'),
            'name' => __('Product Name'),
            'image' => __('Image'),
            'original_price' => __('Original Price'),
            'price' => __('Price'),
            'qty_ordered' => __('Order Items Quantity'),
            'row_total' => __('Row Total'),
            'tax_amount' => __('Tax Amount'),
            'tax_percent' => __('Tax Percent'),
            'row_total_incl_tax' => __('Subtotal'),
        ];
    }
}