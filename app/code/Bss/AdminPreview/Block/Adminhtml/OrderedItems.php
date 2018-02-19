<?php

namespace Bss\AdminPreview\Block\Adminhtml;

use Magento\Framework\App\Filesystem\DirectoryList;

class OrderedItems extends \Magento\Framework\View\Element\Template
{
    protected $_dataHelper;
    protected $_imageFactory;
    protected $order;
    protected $priceHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Sales\Model\Order $order,
        \Bss\AdminPreview\Helper\Data $_dataHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->urlBuilder = $context->getUrlBuilder();
        $this->fileSystem = $context->getFilesystem();
        $this->_imageFactory = $imageFactory;
        $this->_dataHelper = $_dataHelper;
        $this->order = $order;
        $this->priceHelper = $priceHelper;
    }

    public function getOrderItems($id){
        return $this->order->load($id)->getItems();
    }

    public function getProductUrl($id,$store,$parentId,$onlyLink = null){
        return $this->_dataHelper->getProductUrl($id,$store,$parentId,$onlyLink = null);
    }

    public function getProductImage($id,$store){
        return $this->_dataHelper->getProductImage($id,$store);
    }

    public function getProductSku($id){
        return $this->_dataHelper->getProductSku($id);
    }

    public function getProductOriginalPrice($id){
        return $this->_dataHelper->getProductOriginalPrice($id);
    }

    public function formatPrice($price){
        return $this->priceHelper->currency($price, true, false);
    }

    public function getColumnsTitle(){
        $arrayTitle = array_flip(explode(',',$this->_dataHelper->getColumnsTitle()));
        foreach ($arrayTitle as $key => $value) {
            $arrayTitle[$key] = ucwords(str_replace('_',' ',$key));
        }
        return $arrayTitle;
    }
    public function getStoreId($orderId){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeId = $objectManager->create('Magento\Sales\Model\Order')->load($orderId)->getStoreId();
        return $storeId;
    }
    public function getProductItemColumnsHtml($order){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $titles = $this->getColumnsTitle();
        $orderId = $order['entity_id'];
        $orderItems = $this->getOrderItems($orderId);
        //get item product id array
        $childIds = array();
        foreach ($orderItems as $key => $item) {
            if($item->getProductType() == 'configurable'){
                $childIds[$item['product_id']][$key] = array();
                $next = $key + 1;
                $childIds[$item['product_id']][$key] = $next;
            }
        }
        $arr = array();
        foreach ($orderItems as $key => $item) {
            if($item->getProductType() == 'configurable' && !in_array($item['product_id'],$arr) || $item->getProductType() != 'configurable') $arr[$key] = $item['product_id'];
        }
        $ItemIds = array();
        foreach ($arr as $key => $value) {
            array_push($ItemIds, $key);
            foreach ($childIds as $key1 => $value1) {
                if($key1 == $value){
                    foreach ($value1 as $key2 => $v) {
                        array_push($ItemIds, $v);
                    }
                }
            }
        }
        $ItemIds = array_unique($ItemIds);
        $items = array();
        foreach ($ItemIds as $key => $value) {
            $item = $objectManager->create('Magento\Sales\Model\Order\Item')->load($value);
            array_push($items, $item);
        }
        $columnsHtml = array();
        $storeId = $this->getStoreId($orderId);
        foreach ($items as $key => $orderItem) {
            $_productId = $orderItem->getProductId();
            $_parentItemId = $orderItem->getParentItemId();
            $columnsHtml[$key] = array();
            if($_parentItemId){
                $parentItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($_parentItemId);
                $parentProductType = $parentItem->getProductType();
                $_parentProductId = $parentItem->getProductId();
            }
            foreach ($titles as $keyTitle => $value) {
                switch ($keyTitle) {
                    case 'sku':
                    $sku = $this->getProductSku($_productId);
                    if($_parentItemId){
                        $sku = '<td class="bss-show-hide-preview-'.$_parentItemId.'">'.$sku.'</td>';
                    }else{
                        if($orderItem->getProductType() == 'configurable' || $orderItem->getProductType() == 'bundle'){
                            $sku = '<td class="parent-preview-id-'.$orderItem->getItemId().'">'.$sku.'</td>';
                        }else{
                            $sku = '<td>'.$sku.'</td>';
                        }
                    }
                    array_push($columnsHtml[$key],$sku);
                    break;
                    case 'name':
                    if($storeId && $storeId != null){
                        if($_parentItemId){
                            $name = $this->getProductUrl($_productId,$storeId,$_parentProductId);
                        }else{
                            if($orderItem->getProductType() == 'grouped'){
                                $options = $orderItem->getProductOptions();
                                $parentId = $options['super_product_config']['product_id'];
                                $name = $this->getProductUrl($_productId,$storeId,$parentId);
                            }else{
                                $name = $this->getProductUrl($_productId,$storeId,null);
                            }
                        }
                    }else{
                        if($_parentItemId){
                            $name = $this->getProductUrl($_productId,null,$_parentProductId);
                        }else{
                            if($orderItem->getProductType() == 'grouped'){
                                $options = $orderItem->getProductOptions();
                                $parentId = $options['super_product_config']['product_id'];
                                $name = $this->getProductUrl($_productId,null,$parentId);
                            }else{
                                $name = $this->getProductUrl($_productId,null,null);
                            }
                        }
                    }
                    if($_parentItemId){
                        $name = '<td class="bss-show-hide-preview-'.$_parentItemId.'">'.$name.'</td>';
                    }else{
                        if($orderItem->getProductType() == 'configurable' || $orderItem->getProductType() == 'bundle'){
                            $name = '<td class="parent-preview-id-'.$orderItem->getItemId().'">'.$name.'</td>';
                        }else{
                            $name = '<td>'.$name.'</td>';
                        }
                    }
                    array_push($columnsHtml[$key],$name);
                    break;
                    case 'image':
                    if($storeId && $storeId != null){
                        $image = $this->getProductImage($_productId,$storeId);
                    }else{
                        $image = $this->getProductImage($_productId);
                    }
                    if($_parentItemId){
                        $image = '<td class="bss-show-hide-preview-'.$_parentItemId.'">'.$image.'</td>';
                    }else{
                        if($orderItem->getProductType() == 'configurable' || $orderItem->getProductType() == 'bundle'){
                            $image = '<td class="parent-preview-id-'.$orderItem->getItemId().'">'.$image.'</td>';
                        }else{
                            $image = '<td>'.$image.'</td>';
                        }
                    }
                    array_push($columnsHtml[$key],$image);
                    break;

                    case 'original_price':
                    $original_price = $this->formatPrice($orderItem->getOriginalPrice());
                    if($_parentItemId){
                        if($parentProductType == 'configurable') $original_price = $this->formatPrice($parentItem->getOriginalPrice());
                        $original_price = '<td class="bss-show-hide-preview-'.$_parentItemId.'">'.$original_price.'</td>';
                    }else{
                        if($orderItem->getProductType() == 'configurable' || $orderItem->getProductType() == 'bundle'){
                            $original_price = '<td class="parent-preview-id-'.$orderItem->getItemId().'">'.$original_price.'</td>';
                        }else{
                            $original_price = '<td>'.$original_price.'</td>';
                        }
                    }
                    array_push($columnsHtml[$key],$original_price);
                    break;

                    case 'price':
                    $price =  $this->formatPrice($orderItem->getPrice());
                    if($_parentItemId){
                        if($parentProductType == 'configurable') $price = $this->formatPrice($parentItem->getPrice());
                        $price = '<td class="bss-show-hide-preview-'.$_parentItemId.'">'.$price.'</td>';
                    }else{
                        if($orderItem->getProductType() == 'configurable' || $orderItem->getProductType() == 'bundle'){
                            $price = '<td class="parent-preview-id-'.$orderItem->getItemId().'">'.$price.'</td>';
                        }else{
                            $price = '<td>'.$price.'</td>';
                        }
                    }
                    array_push($columnsHtml[$key],$price);
                    break;

                    case 'qty_ordered':
                    $qty_ordered = round($orderItem->getQtyOrdered());
                    if($_parentItemId){
                        $qty_ordered = '<td class="bss-show-hide-preview-'.$_parentItemId.'">'.$qty_ordered.'</td>';
                    }else{
                        if($orderItem->getProductType() == 'configurable' || $orderItem->getProductType() == 'bundle'){
                            $qty_ordered = '';
                            $qty_ordered = '<td class="parent-preview-id-'.$orderItem->getItemId().'">'.$qty_ordered.'</td>';
                        }else{
                            $qty_ordered = '<td>'.$qty_ordered.'</td>';
                        }
                    }
                    array_push($columnsHtml[$key],$qty_ordered);
                    break;

                    case 'row_total':
                    $row_total = $this->formatPrice($orderItem->getRowTotal());
                    if($_parentItemId){
                        if($parentProductType == 'configurable') $row_total = $this->formatPrice($parentItem->getRowTotal());
                        $row_total = '<td class="bss-show-hide-preview-'.$_parentItemId.'">'.$row_total.'</td>';
                    }else{
                        if($orderItem->getProductType() == 'configurable' || $orderItem->getProductType() == 'bundle'){
                            $row_total = '<td class="parent-preview-id-'.$orderItem->getItemId().'">'.$row_total.'</td>';
                        }else{
                            $row_total = '<td>'.$row_total.'</td>';
                        }
                    }
                    array_push($columnsHtml[$key],$row_total);
                    break;

                    case 'tax_amount':
                    $tax_amount = $this->formatPrice($orderItem->getTaxAmount());
                    if($_parentItemId){
                        if($parentProductType == 'configurable') $tax_amount = $this->formatPrice($parentItem->getTaxAmount());
                        $tax_amount = '<td class="bss-show-hide-preview-'.$_parentItemId.'">'.$tax_amount.'</td>';
                    }else{
                        if($orderItem->getProductType() == 'configurable' || $orderItem->getProductType() == 'bundle'){
                            $tax_amount = '<td class="parent-preview-id-'.$orderItem->getItemId().'">'.$tax_amount.'</td>';
                        }else{
                            $tax_amount = '<td>'.$tax_amount.'</td>';
                        }
                    }
                    array_push($columnsHtml[$key],$tax_amount);
                    break;

                    case 'tax_percent':
                    $tax_percent = $this->formatPrice($orderItem->getTaxPercent());
                    if($_parentItemId){
                        if($parentProductType == 'configurable') $tax_percent = $this->formatPrice($parentItem->getTaxPercent());
                        $tax_percent = '<td class="bss-show-hide-preview-'.$_parentItemId.'">'.$tax_percent.'</td>';
                    }else{
                        if($orderItem->getProductType() == 'configurable' || $orderItem->getProductType() == 'bundle'){
                            $tax_percent = '<td class="parent-preview-id-'.$orderItem->getItemId().'">'.$tax_percent.'</td>';
                        }else{
                            $tax_percent = '<td>'.$tax_percent.'</td>';
                        }
                    }
                    array_push($columnsHtml[$key],$tax_percent);
                    break;

                    case 'row_total_incl_tax':
                    $row_total_incl_tax = $this->formatPrice($orderItem->getRowTotalInclTax());
                    if($_parentItemId){
                        if($parentProductType == 'configurable') $row_total_incl_tax = $this->formatPrice($parentItem->getRowTotalInclTax());
                        $row_total_incl_tax = '<td class="bss-show-hide-preview-'.$_parentItemId.'">'.$row_total_incl_tax.'</td>';
                    }else{
                        if($orderItem->getProductType() == 'configurable' || $orderItem->getProductType() == 'bundle'){
                            $row_total_incl_tax = '<td class="parent-preview-id-'.$orderItem->getItemId().'">'.$row_total_incl_tax.'</td>';
                        }else{
                            $row_total_incl_tax = '<td>'.$row_total_incl_tax.'</td>';
                        }
                    }
                    array_push($columnsHtml[$key],$row_total_incl_tax);
                    break;
                    
                    default:

                    break;
                }
            }  
        }
        return $columnsHtml;
    }
}
