<?php
/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2016 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Netresearch\OPS\Model\System\Config\Backend\Flex;

use Magento\Framework\Exception\LocalizedException;

/**
 * Methods.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */

class Methods extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    protected $_eventPrefix = 'ops_config_backend_flex_methods';

    /**
     * additional validation for unique brands
     *
     * @override
     * @throws \Exception if the brands are not unique -> validation failed
     * @return $this
     */
    public function save()
    {
        $methods = $this->getValue();
        if (is_array($methods) && count($methods) > 1) {
            $alreadyProcessedMethods = [];
            foreach ($methods as $method) {
                if (is_array($method)
                    && array_key_exists('pm', $method)
                    && array_key_exists('brand', $method)
                ) {
                    if (empty($method['title'])||empty($method['pm'])) {
                        throw new LocalizedException(__('Can not save empty title or PM fields'));
                    }
                    if (in_array($method['pm'].'_'.$method['brand'], $alreadyProcessedMethods)) {
                        throw new LocalizedException(__('PM and Brand combination must be unique'));
                    }
                    $alreadyProcessedMethods[] = $method['pm'].'_'.$method['brand'];
                }
            }
        }
        return parent::save();
    }
}
