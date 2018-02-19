<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Model\Checkout\Orderline;

use Klarna\Core\Api\BuilderInterface;
use Klarna\Core\Helper\ConfigHelper;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Tax\Model\Calculation;

/**
 * Generate order item line details
 *
 * @author  Joe Constant <joe.constant@klarna.com>
 * @author  Jason Grim <jason.grim@klarna.com>
 */
class Items extends AbstractLine
{

    /**
     * Checkout item types
     */
    const ITEM_TYPE_PHYSICAL = 'physical';
    const ITEM_TYPE_VIRTUAL  = 'digital';

    /**
     * Order lines is not a total collector, it's a line item collector
     *
     * @var bool
     */
    protected $_isTotalCollector = false;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var string
     */
    protected $eventPrefix = '';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Items constructor.
     *
     * @param ConfigHelper         $helper
     * @param Calculation          $calculator
     * @param ScopeConfigInterface $config
     * @param EventManager         $eventManager
     * @param UrlInterface         $urlBuilder
     */
    public function __construct(
        ConfigHelper $helper,
        Calculation $calculator,
        ScopeConfigInterface $config,
        EventManager $eventManager,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($helper, $calculator, $config);
        $this->eventManager = $eventManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Collect totals process.
     *
     * @param BuilderInterface $checkout
     *
     * @return $this
     */
    public function collect(BuilderInterface $checkout)
    {
        $object = $checkout->getObject();
        $items = [];

        foreach ($object->getAllItems() as $item) {
            $qtyMultiplier = 1;
            $productUrl = null;
            $imageUrl = null;
            $store = null;
            $product = null;

            if ($item instanceof InvoiceItem || $item instanceof CreditMemoItem) {
                $orderItem = $item->getOrderItem();
                $parentItem = $orderItem->getParentItem()
                    ?: ($orderItem->getParentItemId() ? $object->getItemById($orderItem->getParentItemId()) : null);

                /** @var \Magento\Sales\Model\Order $order */
                $order = $object->getOrder();
                $store = $order->getStore();

                // Skip if child product of a non bundle parent
                if ($parentItem && Type::TYPE_BUNDLE != $parentItem->getProductType()) {
                    continue;
                }

                // Skip if a bundled product with price type dynamic
                if ((Type::TYPE_BUNDLE == $orderItem->getProductType()
                    && Price::PRICE_TYPE_DYNAMIC == $orderItem->getProduct()->getPriceType())
                ) {
                    continue;
                }

                // Skip if child product of a bundle parent and bundle product price type is fixed
                if ($parentItem && Type::TYPE_BUNDLE == $parentItem->getProductType()
                    && Price::PRICE_TYPE_FIXED == $parentItem->getProduct()->getPriceType()
                ) {
                    continue;
                }

                // Skip if parent is a bundle product having price type dynamic
                if ($parentItem && Type::TYPE_BUNDLE == $orderItem->getProductType()
                    && Price::PRICE_TYPE_DYNAMIC == $orderItem->getProduct()->getPriceType()
                ) {
                    continue;
                }

                $product = $orderItem->getProduct();
            }

            if ($item instanceof QuoteItem) {
                // Skip if bundle product with a dynamic price type
                if (Type::TYPE_BUNDLE == $item->getProductType()
                    && Price::PRICE_TYPE_DYNAMIC == $item->getProduct()->getPriceType()
                ) {
                    continue;
                }

                $store = $item->getStore();
                // Get quantity multiplier for bundle products
                if ($item->getParentItemId() && ($parentItem = $object->getItemById($item->getParentItemId()))) {
                    // Skip if non bundle product or if bundled product with a fixed price type
                    if (Type::TYPE_BUNDLE != $parentItem->getProductType()
                        || Price::PRICE_TYPE_FIXED == $parentItem->getProduct()->getPriceType()
                    ) {
                        continue;
                    }

                    $qtyMultiplier = $parentItem->getQty();
                }
                $product = $item->getProduct();
            }

            if (isset($parentItem)) {
                $product = $parentItem->getProduct();
            }
            $product->setStoreId($store->getId());
            $productUrl = $product->getUrlInStore();
            $imageUrl = $this->getImageUrl($product);

            $_item = [
                'type'          => $item->getIsVirtual() ? self::ITEM_TYPE_VIRTUAL : self::ITEM_TYPE_PHYSICAL,
                'reference'     => substr($item->getSku(), 0, 64),
                'name'          => $item->getName(),
                'quantity'      => ceil($item->getQty() * $qtyMultiplier),
                'discount_rate' => 0,
                'product_url'   => $productUrl,
                'image_url'     => $imageUrl
            ];

            if ($this->helper->getSeparateTaxLine($store)) {
                $_item['tax_rate'] = 0;
                $_item['total_tax_amount'] = 0;
                $_item['unit_price'] = $this->helper->toApiFloat($item->getBasePrice())
                    ?: $this->helper->toApiFloat($item->getBaseOriginalPrice());
                $_item['total_amount'] = $this->helper->toApiFloat($item->getBaseRowTotal());
            } else {
                $taxRate = 0;
                if ($item->getBaseRowTotal() > 0) {
                    $taxRate = ($item->getTaxPercent() > 0) ? $item->getTaxPercent()
                        : ($item->getBaseTaxAmount() / $item->getBaseRowTotal() * 100);
                }

                $taxAmount = $this->calculator->calcTaxAmount($item->getBaseRowTotalInclTax(), $taxRate, true);
                $_item['tax_rate'] = $this->helper->toApiFloat($taxRate);
                $_item['total_tax_amount'] = $this->helper->toApiFloat($taxAmount);
                $_item['unit_price'] = $this->helper->toApiFloat($item->getBasePriceInclTax())
                    ?: $this->helper->toApiFloat($item->getBaseRowTotalInclTax());
                $_item['total_amount'] = $this->helper->toApiFloat($item->getBaseRowTotalInclTax());
            }

            $_item = new DataObject($_item);
            $this->eventManager->dispatch(
                $this->eventPrefix . 'orderline_item',
                [
                    'checkout'    => $checkout,
                    'object_item' => $item,
                    'klarna_item' => $_item
                ]
            );

            $items[] = $_item->toArray();

            $checkout->setItems($items);
        }

        return $this;
    }

    /**
     * Get image for product
     *
     * @param Product $product
     * @return string
     */
    protected function getImageUrl($product)
    {
        if (!$product->getSmallImage()) {
            return null;
        }
        $baseUrl = $product->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $baseUrl . 'catalog/product' . $product->getSmallImage();
    }

    /**
     * Add order details to checkout request
     *
     * @param BuilderInterface $checkout
     *
     * @return $this
     */
    public function fetch(BuilderInterface $checkout)
    {
        if ($checkout->getItems()) {
            foreach ($checkout->getItems() as $item) {
                $checkout->addOrderLine($item);
            }
        }

        return $this;
    }
}
