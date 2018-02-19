<?php
namespace Itoris\Producttabsslider\Ui\DataProvider\Product\Form\Modifier;
/**
 * Created by PhpStorm.
 * User: Workstation1
 * Date: 23.06.2016
 * Time: 14:42
 */
use Magento\Ui\Component\Form\Fieldset;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Container;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\CurrencyInterface;
class ProductTabs extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;
    protected $meta = [];
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    const GROUP_CUSTOM_OPTIONS_SCOPE = 'data.product';
    const GROUP_CUSTOM_OPTIONS_NAME = 'itoris_producttabs';
    const GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME = 'search-engine-optimization';
    const GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER = 32;
    const IMPORT_OPTIONS_MODAL = 'import_options_modal';
    const CUSTOM_OPTIONS_LISTING = 'product_custom_options_listing';
    const CONTAINER_OPTION = 'container_option';
    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }
    protected function getNextGroupSortOrder(array $meta, $groupCodes, $defaultSortOrder, $iteration = 1)
    {
        $groupCodes = (array)$groupCodes;

        foreach ($groupCodes as $groupCode) {
            if (isset($meta[$groupCode]['arguments']['data']['config']['sortOrder'])) {
                return $meta[$groupCode]['arguments']['data']['config']['sortOrder'] + $iteration;
            }
        }

        return $defaultSortOrder;
    }
    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {        
        $this->meta = $meta;
        $this->createTabsPanel();

        return $this->meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return array_replace_recursive($data, [

        ]);
    }
    /**
     * Create "Customizable Options" panel
     *
     * @return $this
     */
    protected function createTabsPanel()
    {
        if($this->locator->getProduct()->getId()) {
            $em = \Magento\Framework\App\ObjectManager::getInstance();
            
            //check ACL
            if (!$em->get('\Magento\Framework\Authorization')->isAllowed('Itoris_Producttabsslider::product_tabs')) return $this;
            
            $layout = $em->create('Magento\Framework\View\LayoutInterface');
            $block = $layout->createBlock('Itoris\Producttabsslider\Block\Adminhtml\Grid\ProductTabGrid', 'itoris.product.tabs');

            $product = $em->get('Magento\Framework\Registry')->registry('product');
            $prodId = $product->getId();
            $block->setProductId($prodId)
                ->setUseAjax(true);;

            $this->meta = array_replace_recursive(
                $this->meta,
                [
                    static::GROUP_CUSTOM_OPTIONS_NAME => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Product Tabs'),
                                    'componentType' => Fieldset::NAME,
                                    'dataScope' => static::GROUP_CUSTOM_OPTIONS_SCOPE,
                                    'collapsible' => true,
                                    'sortOrder' => $this->getNextGroupSortOrder(
                                        $this->meta,
                                        static::GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME,
                                        static::GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER
                                    ),
                                ],
                            ],
                        ],
                        'children' => [
                            'itoris_product_grid' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label' => null,
                                            'formElement' => Container::NAME,
                                            'componentType' => Container::NAME,
                                            'template' => 'ui/form/components/complex',
                                            'sortOrder' => 9,
                                            'content' => $block->toHtml(),
                                        ],
                                    ],
                                ],
                            ]
                        ]

                    ]
                ]
            );
        }
        return $this;
    }

}