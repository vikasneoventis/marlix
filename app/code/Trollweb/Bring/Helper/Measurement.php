<?php

namespace Trollweb\Bring\Helper;

class Measurement extends \Magento\Framework\App\Helper\AbstractHelper {
    const WEIGHT_UNIT_G = "gram";
    const WEIGHT_UNIT_KG = "kilogram";
    const SIZE_UNIT_MM = "millimeter";
    const SIZE_UNIT_CM = "centimeter";
    const SIZE_UNIT_DM = "decimeter";
    const SIZE_UNIT_M = "meter";

    private $config;

    public function __construct(
        \Trollweb\Bring\Helper\Config $config,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    public function getWeightInGrams($weight) {
        if ($this->config->getWeightUnit() == self::WEIGHT_UNIT_KG) {
            return (int) ($weight * 1000);
        }

        return (int)$weight;
    }
}
