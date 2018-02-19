<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Plugin;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;
use MageWorx\OptionFeatures\Helper\Data as Helper;

class AfterGetProductJsonConfig
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @param Helper $helper
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     */
    public function __construct(
        Helper $helper,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder
    ) {
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Update product config data based on the features config
     *
     * @param $subject
     * @param $result
     * @return string
     */
    public function afterGetProductJsonConfig($subject, $result)
    {
        $resultDecoded = $this->jsonDecoder->decode($result);
        if (!$this->helper->isAbsolutePriceEnabled()) {
            $resultDecoded[Helper::KEY_ABSOLUTE_PRICE] = 0;
        }
        $resultEncoded = $this->jsonEncoder->encode($resultDecoded);

        return $resultEncoded;
    }
}
