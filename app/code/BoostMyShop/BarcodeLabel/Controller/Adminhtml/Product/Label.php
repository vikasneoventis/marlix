<?php

namespace BoostMyShop\BarcodeLabel\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Label extends \Magento\Backend\App\AbstractAction
{
    protected $_label;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     */
    public function __construct(
        Context $context,
        \BoostMyShop\BarcodeLabel\Model\Label $label,
        ProductBuilder $productBuilder
    ) {
        $this->productBuilder = $productBuilder;
        $this->_label = $label;
        parent::__construct($context);
    }

    public function execute()
    {
        $product = $this->productBuilder->build($this->getRequest());

        $img = $this->_label->getImage($product);

        header('Content-type: image/gif');
        imagegif($img);
        die();

    }

    protected function _isAllowed()
    {
        return true;
    }

}
