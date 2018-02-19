<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Block;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MageWorx\OptionFeatures\Helper\Data as Helper;

class Features extends Template
{
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        $data = [
            'question_image' => $this->getViewFileUrl('MageWorx_OptionFeatures::image/question.png'),
            'value_description_enabled' => $this->helper->isDescriptionEnabled(),
            'option_description_enabled' => $this->helper->isOptionDescriptionEnabled(),
            'option_description_mode' => $this->helper->getOptionDescriptionMode(),
            'option_description_modes' => [
                'disabled' => Helper::OPTION_DESCRIPTION_DISABLED,
                'tooltip' => Helper::OPTION_DESCRIPTION_TOOLTIP,
                'text' => Helper::OPTION_DESCRIPTION_TEXT,
            ]
        ];

        return $this->jsonEncoder->encode($data);
    }
}
