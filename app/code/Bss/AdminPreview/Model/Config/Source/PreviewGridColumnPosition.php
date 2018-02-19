<?php
 
namespace Bss\AdminPreview\Model\Config\Source;
 
use Magento\Framework\Option\ArrayInterface;
 
class PreviewGridColumnPosition implements ArrayInterface
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
            'actions' => __('In Actions Column'),
            'new' => __('In New Column')
        ];
    }
}