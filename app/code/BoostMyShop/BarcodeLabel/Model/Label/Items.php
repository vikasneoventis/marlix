<?php

namespace BoostMyShop\BarcodeLabel\Model\Label;

class Items
{

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \BoostMyShop\BarcodeLabel\Model\ConfigFactory $config
    ){
        $this->_config = $config;
    }

    public function getDisplayableItems()
    {
        $items = [
            'sku' => ['label' => 'Sku',  'source' => 'attribute', 'attribute' => 'sku', 'renderer' => 'text'],
            'name' => ['label' => 'Name', 'source' => 'attribute', 'attribute' => 'name', 'renderer' => 'text'],
            'price' => ['label' => 'Price','source' => 'attribute', 'attribute' => 'price', 'renderer' => 'price'],
            'logo' => ['label' => 'Logo', 'source' => 'config', 'prefix' => 'barcodelabel/logo/', 'config_path' => 'attributes/logo',  'renderer' => 'image'],
            'image' => ['label' => 'Product Image','source' => 'attribute', 'prefix' => 'catalog/product', 'attribute' => 'image', 'renderer' => 'image'],
            'freetext' => ['label' => 'Free text', 'source' => 'config',  'config_path' => 'attributes/freetext', 'renderer' => 'text'],
            'barcode' => ['label' => 'Barcode', 'source' => 'attribute', 'attribute' => $this->_config->create()->getSetting('general/barcode_attribute'), 'renderer' => 'barcode']
        ];


        if ($this->_config->create()->getSetting('attributes/location'))
            $items['location'] = ['label' => 'Location', 'source' => 'attribute', 'attribute' => $this->_config->create()->getSetting('attributes/location'), 'renderer' => 'text'];
        if ($this->_config->create()->getSetting('attributes/manufacturer'))
            $items['manufacturer'] = ['label' => 'Manufacturer','source' => 'attribute', 'attribute' => $this->_config->create()->getSetting('attributes/manufacturer'), 'renderer' => 'text'];

        for($i=1;$i<=6;$i++)
        {
            if ($this->_config->create()->getSetting('attributes/custom'.$i))
                $items['custom'.$i] = ['label' => $this->_config->create()->getSetting('attributes/custom'.$i),'source' => 'attribute', 'attribute' => $this->_config->create()->getSetting('attributes/custom'.$i), 'renderer' => 'text'];
        }
        foreach($items as $k => $item)
        {
            foreach($this->getItemFields() as $field)
            {
                $path = 'label_layout/content_'.$k.'_'.$field;
                $items[$k][$field] = $this->_config->create()->getSetting($path);
            }
        }

        return $items;
    }

    public function getItemFields()
    {
        return ['print', 'position', 'size'];
    }

}
