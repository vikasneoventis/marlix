<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Temando\Shipping\Model\Checkout\Schema\CheckoutField;
use Temando\Shipping\Model\Checkout\Schema\CheckoutFieldsSchema;
use Temando\Shipping\Model\Config\ModuleConfigInterface;

/**
 * CheckoutFields
 *
 * @package  Temando\Shipping\CustomerData
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class CheckoutFields implements SectionSourceInterface
{
    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var CheckoutFieldsSchema
     */
    private $schema;

    /**
     * @param ModuleConfigInterface $moduleConfig
     * @param CheckoutFieldsSchema $schema
     */
    public function __construct(ModuleConfigInterface $moduleConfig, CheckoutFieldsSchema $schema)
    {
        $this->moduleConfig = $moduleConfig;
        $this->schema = $schema;
    }

    /**
     * Obtain fields data for display in checkout, shipping method step
     *
     * @return string[]
     */
    public function getSectionData()
    {
        if (!$this->moduleConfig->isEnabled()) {
            return ['fields' => []];
        }

        $fields = array_map(function (CheckoutField $field) {
            return $field->toArray();
        }, $this->schema->getFields());

        return ['fields' => $fields];
    }
}
