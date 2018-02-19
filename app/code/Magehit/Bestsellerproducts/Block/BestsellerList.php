<?php

namespace Magehit\Bestsellerproducts\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class BestsellerList extends \Magento\Catalog\Block\Product\ListProduct {

    /**
     * Product collection model
     *
     * @var Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_collection;

    /**
     * Product collection model
     *
     * @var Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_productCollection;

    /**
     * Image helper
     *
     * @var Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * Catalog Layer
     *
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Initialize
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
    \Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Data\Helper\PostHelper $postDataHelper, \Magento\Catalog\Model\Layer\Resolver $layerResolver, CategoryRepositoryInterface $categoryRepository,\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection, \Magento\Framework\Url\Helper\Data $urlHelper, \Magento\Catalog\Model\ResourceModel\Product\Collection $collection, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Helper\Image $imageHelper, array $data = []
    ) {
        $this->imageBuilder = $context->getImageBuilder();
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->_collection = $collection;
        $this->_imageHelper = $imageHelper;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);

        $this->pageConfig->getTitle()->set(__($this->getPageTitle()));
    }

    /**
     * Get product collection
     */
    protected function getProducts() {
		$sortby = 'rand()';
		$storeId= 0;
		$fromDate = $this->getStartDate();
		$toDate = $this->getEndDate();
		$sqlQuery = "e.entity_id = aggregation.product_id";
		if($storeId > 0){
			$sqlQuery .=" AND aggregation.store_id={$storeId}";
		}
		if($fromDate !='' && $toDate !=''){
			$sqlQuery .=" AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'";
		}
		$this->_collection->clear()->getSelect()->reset('where');
        $collection = $this->_collection
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('news_from_date')
                ->addAttributeToSelect('news_to_date')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('special_from_date')
                ->addAttributeToSelect('special_to_date');
                /* ->addAttributeToFilter('is_saleable', 1, 'left') */
				/* ->addAttributeToFilter('status', 1)
				->addAttributeToFilter('visibility', 4); */
		if($this->getSortbyCollection() == "product_name"){
			$sortby = "rand()";
		}else if($this->getSortbyCollection() == "product_price"){
			$sortby = "price DESC";
		}else if($this->getSortbyCollection() == "qty_ordered"){
			$sortby = "sold_quantity DESC";
		}
		$collection->getSelect()->joinRight(
			array('aggregation' => 'sales_bestsellers_aggregated_monthly'),
			$sqlQuery,
			array('SUM(aggregation.qty_ordered) AS sold_quantity')
		)->group('e.entity_id')->order($sortby);
		$collection->getSelect();
        // Set Pagination Toolbar for list page
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'bestsellerproducts.grid.record.pager')->setLimit(8)->setCollection($collection);
        $this->setChild('pager', $pager); // set pager block in layout
        $this->_productCollection = $collection;
        return $this->_productCollection;
    }

    /*
     * Load and return product collection 
     */

    public function getLoadedProductCollection() {
        return $this->getProducts();
    }

    /*
     * Get product toolbar
     */

    public function getToolbarHtml() {
        return $this->getChildHtml('pager');
    }

    /*
     * Get grid mode
     */

    public function getMode() {
        return 'grid';
    }

    /**
     * Get image helper
     */
    public function getImageHelper() {
        return $this->_imageHelper;
    }

    /* Check module is enabled or not */

    public function getSectionStatus() {
        return $this->_scopeConfig->getValue('bestsellerproducts_settings/bestseller_products/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /* Check vertical block is enabled or not */

    public function getVerticalStatus() {
        return $this->_scopeConfig->getValue('bestsellerproducts_settings/vertical_setting/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /* Get the configured limit of products */

    public function getProductLimit() {
        return $this->_scopeConfig->getValue('bestsellerproducts_settings/vertical_setting/limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /* Get the configured title of section */

    public function getPageTitle() {
        return $this->_scopeConfig->getValue('bestsellerproducts_settings/vertical_setting/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
	/**
     * Get the configured slide auto of section
     * @return int
     */
	public function getSlideAuto(){
		return $this->_scopeConfig->getValue('bestsellerproducts_settings/vertical_setting/slide_auto', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	/**
     * Get the configured show pagination of section
     * @return int
     */
	public function getPagination(){
		return $this->_scopeConfig->getValue('bestsellerproducts_settings/vertical_setting/slide_pagination', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	/**
     * Get the configured show navigation of section
     * @return int
     */
	public function getNavigation(){
		return $this->_scopeConfig->getValue('bestsellerproducts_settings/vertical_setting/slide_navigation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	/**
     * Get the configured sortby of section
     * @return int
     */
	public function getSortbyCollection(){
		return $this->_scopeConfig->getValue('bestsellerproducts_settings/bestseller_products/sortby', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getStartDate(){
		return $this->_scopeConfig->getValue('bestsellerproducts_settings/bestseller_products/startdate', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function getEndDate(){
		return $this->_scopeConfig->getValue('bestsellerproducts_settings/bestseller_products/enddate', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function getVisibleStatus(){
		
		$visibleStatus = 4;
		return $visibleStatus;
	}
}
