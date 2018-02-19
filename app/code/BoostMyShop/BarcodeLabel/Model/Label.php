<?php

namespace BoostMyShop\BarcodeLabel\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class Label
{
    protected $_configFactory;
    protected $_config = null;
    protected $_items;
    protected $_filesystem;

    protected $_coef = 2;

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \BoostMyShop\BarcodeLabel\Model\Label\Items $items,
        \BoostMyShop\BarcodeLabel\Model\ConfigFactory $config,
        \Magento\Framework\Filesystem $filesystem
    ){
        $this->_configFactory = $config;
        $this->_items = $items;
        $this->_filesystem = $filesystem;
    }

    protected function convertToPixel($value, $applyCoef = true)
    {
        //1 point = 1/72 of inches
        //1 point = 0,352778 mm

        $unit = $this->getConfig()->getSetting('label_layout/unit');

        switch($unit)
        {
            case 'cm':
                $value = $value * 10 / 0.352778;
                break;
            case 'inch':
                $value = $value * 72;
                break;
        }

        if ($applyCoef)
            $value = $value * $this->_coef;

        return (int)$value;
    }

    public function getLabelSize($applyCoef = true)
    {
        $sizes = array();
        $sizes['height'] = $this->convertToPixel($this->getConfig()->getSetting('label_layout/paper_height'), $applyCoef);
        $sizes['width'] = $this->convertToPixel($this->getConfig()->getSetting('label_layout/paper_width'), $applyCoef);
        return $sizes;
    }

    public function getImage($product)
    {
        //create base image
        $labelSize = $this->getlabelSize();
        $height = $labelSize['height'];
        $width = $labelSize['width'];
        $im = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, $width, $height, $white);

        $this->drawItems($im, $product);

        //return image
        return $im;
    }

    protected function drawItems($img, $product)
    {
        $items = $this->_items->getDisplayableItems();
        foreach($items as $item)
        {
            if ($item['print'])
                $this->drawItem($img, $product, $item);
        }
    }

    protected function drawItem($img, $product, $item)
    {
        $value = '';
        if (isset($item['prefix']))
            $value = $item['prefix'];

        switch($item['source'])
        {
            case 'attribute':
                if (!$item['attribute'])
                    return;
                $attributeValue = $product->getResource()->getAttribute($item['attribute'])->getFrontend()->getValue($product);
                if (!$attributeValue)
                    $attributeValue = $product->getData($item['attribute']);
                $value .= $attributeValue;
                break;
            case 'config':
                $value .= $this->getConfig()->getSetting($item['config_path']);
                break;
        }

        switch($item['renderer'])
        {
            case 'text':
                $this->drawText($img, $value, $item['position'], $item['size']);
                break;
            case 'price':
                if ($value)
                {
                    $value = number_format($value, 2, '.', '').$this->getConfig()->getCurrencySymbol();
                    $this->drawText($img, $value, $item['position'], $item['size']);
                }
                break;
            case 'image':
                $value = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath($value);
                $this->drawImage($img, $value, $item['position'], $item['size']);
                break;
            case 'barcode':
                $barcodeImage = $this->createBarcodeImage($this->getConfig()->getSetting('general/barcode_type'), $value);
                $this->drawImage($img, $barcodeImage, $item['position'], $item['size']);
                break;
        }

    }

    protected function getConfig()
    {
        if ($this->_config == null)
        {
            $this->_config = $this->_configFactory->create();
        }
        return $this->_config;
    }

    protected function drawText($image, $text, $positionString, $fontSize)
    {
        $position = $this->convertPosition($positionString);

        $color = imagecolorallocate($image, 0, 0, 0);
        $font =  __DIR__.'/../Fonts/LinLibertineC_Re-2.8.0.ttf';

        $imageWidth = imagesx($image);
        $words = explode(' ', $text);
        $lines = array($words[0]);
        $currentLine = 0;

        for($i=1; $i<count($words); $i++){

            $lineSize = imagettfbbox($fontSize, NULL, $font, $lines[$currentLine] . ' ' . $words[$i]);

            // if the text width in pixel is lower than the main image width
            if($lineSize[2] - $lineSize[0] < $imageWidth){
                // then add the word into the current string
                $lines[$currentLine] .= ' ' . $words[$i];
            }else{
                // else, jump to the next line
                $currentLine++;
                $lines[$currentLine] = $words[$i];
            }
        }

        // Loop through the lines and place them on the image
        $lineCount = 1;
        foreach ($lines as $line){

            // get size for each lines
            $lineBox = imagettfbbox($fontSize, NULL, $font, "$line");
            $linePositionForX = $position['x'];
            $linePositionForY = $position['y'] + $fontSize * $lineCount;

            // draw the wrapped line as image in the main image
            imagettftext($image, $fontSize, 0, $linePositionForX, $linePositionForY, $color, $font, $line);

            $lineCount++;
        }
    }

    /**
     * @param $img
     * @param $imageReference : can be image path on server OR image resource directly
     * @param $position
     * @param $size
     * @return bool
     */
    protected function drawImage($img, $imageReference, $position, $size)
    {
        $imageResource = null;
        if (is_resource($imageReference))
        {
            $imageResource = $imageReference;
        }
        else
        {
            if (!file_exists($imageReference))
                return false;
            $extension = strtolower( pathinfo($imageReference, PATHINFO_EXTENSION) );
            switch ($extension) {
                case 'jpeg';
                case 'jpg';
                    $imageResource = imagecreatefromjpeg($imageReference);
                    break;
                case 'png':
                    $imageResource = imagecreatefrompng($imageReference);
                    break;
                default:
                    return false;
                    break;
            }
        }

        $position = $this->convertPosition($position);
        $size = $this->convertSize($size);

        $logoImageWidth = imagesx($imageResource);
        $logoImageHeight = imagesy($imageResource);

        imagecopyresized($img, $imageResource, $position['x'], $position['y'], 0, 0, $size['width'], $size['height'], $logoImageWidth, $logoImageHeight);
    }

    protected function createBarcodeImage($barcodeStandard, $barcode)
    {
        if ($barcodeStandard == "Ean13")
            $barcode = substr($barcode, 0, 12);

        $barcodeOptions = array('text' => $barcode);
        $rendererOptions = array();

        $factory = \Zend_Barcode::factory($barcodeStandard, 'image', $barcodeOptions, $rendererOptions);

        $image = $factory->draw();


        return $image;
    }

    protected function convertSize($sizeString)
    {
        $sizeString = explode(',', $sizeString);
        if (count($sizeString) != 2)
            $sizeString = explode(',', '50,50');
        $position = ['width' => $this->convertToPixel($sizeString[0]), 'height' => $this->convertToPixel($sizeString[1])];
        return $position;
    }

    protected function convertPosition($positionString)
    {
        $positionString = explode(',', $positionString);
        if (count($positionString) != 2)
            $positionString = explode(',', '0,0');
        $position = ['x' => $this->convertToPixel($positionString[0]), 'y' => $this->convertToPixel($positionString[1])];
        return $position;
    }

}
