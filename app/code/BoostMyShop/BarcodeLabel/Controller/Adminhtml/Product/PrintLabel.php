<?php

namespace BoostMyShop\BarcodeLabel\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintLabel extends \Magento\Backend\App\AbstractAction
{
    protected $_label;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     */
    public function __construct(
        Context $context,
        ProductBuilder $productBuilder
    ) {
        $this->productBuilder = $productBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $count = (int)$this->getRequest()->getParam('count');
        $product = $this->productBuilder->build($this->getRequest());

        $products[] = ['product' => $product, 'qty' => $count];

        $pdf = $this->_objectManager->create('BoostMyShop\BarcodeLabel\Model\Pdf')->getPdf($products);
        return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
            'barcode_label_' . $product->getId() . '.pdf',
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );

    }

    protected function _isAllowed()
    {
        return true;
    }

}
