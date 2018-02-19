<?php

namespace Trollweb\Bring\Helper;

class Price extends \Magento\Framework\App\Helper\AbstractHelper {
    const ROUND_NONE = 'none';
    const ROUND_NEAREST_INT = 'nearest_int';
    const ROUND_UP_INT = 'up_int';
    const ROUND_DOWN_INT = 'down_int';
    const ROUND_NEAREST_TEN = 'nearest_ten';
    const ROUND_UP_TEN = 'up_ten';
    const ROUND_DOWN_TEN = 'down_ten';

    private $config;

    public function __construct(
        \Trollweb\Bring\Helper\Config $config,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    public function round($price)
    {
        $strategy = $this->config->getPriceRoundingStrategy();

        switch ($strategy) {
            case self::ROUND_NEAREST_INT:
                return round($price, 0);
            case self::ROUND_UP_INT:
                return ceil($price);
            case self::ROUND_DOWN_INT:
                return floor($price);
            case self::ROUND_NEAREST_TEN:
                return round($price, -1);
            case self::ROUND_UP_TEN:
                if ($price == 0) {
                    return 0;
                }
                if ($price < 10) {
                    return 10;
                }

                return ceil($price / 10) * 10;
            case self::ROUND_DOWN_TEN:
                if ($price < 10) {
                    return 0;
                }

                return floor($price / 10) * 10;
            case self::ROUND_NONE:
            default:
                return $price;
        }
    }
}
