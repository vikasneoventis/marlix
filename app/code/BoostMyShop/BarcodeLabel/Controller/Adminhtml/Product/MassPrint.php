<?php

namespace BoostMyShop\BarcodeLabel\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class MassPrint extends \Magento\Backend\App\AbstractAction
{
    protected $_label;
    protected $filter;
    protected $collectionFactory;
    protected $productFactory;
    protected $_stockState;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->productFactory = $productFactory;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_stockState = $stockState;

        parent::__construct($context);
    }

    public function execute()
    {
        $products = [];

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection->getItems() as $product) {

            $product = $this->productFactory->create()->load($product->getId());
            $qty = $this->_stockState->getStockQty($product->getId());

            $products[] = ['product' => $product, 'qty' => $qty];
        }

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
