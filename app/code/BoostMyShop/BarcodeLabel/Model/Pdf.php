<?php

namespace BoostMyShop\BarcodeLabel\Model;

use Magento\Framework\App\Filesystem\DirectoryList;


class Pdf extends \Magento\Framework\DataObject
{
    public $y;
    protected $_pdf;
    protected $_filesystem;

    protected $string;
    protected $_localeDate;
    protected $_label;
    protected $_scopeConfig;
    protected $_rootDirectory;


    /**
     * Retrieve PDF
     *
     * @return \Zend_Pdf
     */
    public function getPdf($products)
    {
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);

        foreach($products as $productData)
        {
            $product = $productData['product'];
            $qty = $productData['qty'];

            $tempImage = $this->_label->getImage($product);
            $tempDir = $this->_filesystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath('');
            if (!is_dir($tempDir))
                mkdir($tempDir);
            $tempPath = $this->_filesystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath('barcodelabel.png');
            imagepng($tempImage, $tempPath);
            $zendPicture = \Zend_Pdf_Image::imageWithPath($tempPath);
            unlink($tempPath);

            $size = $this->_label->getLabelSize(false);

            for($i=1;$i<=$qty;$i++)
            {
                $page = $this->newPage($size['height'], $size['width']);
                $page->drawImage($zendPicture, 0, 0, $size['width'], $size['height']);
            }
        }


        return $pdf;
    }


    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \BoostMyShop\BarcodeLabel\Model\Label $label,
        array $data = []
    ) {
        $this->_localeDate = $localeDate;
        $this->string = $string;
        $this->_scopeConfig = $scopeConfig;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->inlineTranslation = $inlineTranslation;
        $this->_label = $label;
        $this->_filesystem = $filesystem;
        parent::__construct($data);
    }


    protected function _setPdf(\Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }


    protected function _getPdf()
    {
        if (!$this->_pdf instanceof \Zend_Pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define the PDF object before using.'));
        }

        return $this->_pdf;
    }


    public function newPage($height, $width)
    {
        $pageSize = $width.':'.$height;

        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;

        return $page;
    }


}
