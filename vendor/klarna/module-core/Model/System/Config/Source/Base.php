<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Model\System\Config\Source;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\Option\ArrayInterface;

class Base implements ArrayInterface
{

    /**
     * Determines which config option to pull from
     *
     * @var string
     */
    protected $optionName = '';

    /**
     * @var DataInterface
     */
    protected $config;

    public function __construct(DataInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $values = $this->config->get($this->optionName);

        if ($values) {
            foreach ($values as $name => $value) {
                $options[] = [
                    'label' => $value['label'],
                    'value' => $name
                ];
            }
        }

        return $options;
    }
}
