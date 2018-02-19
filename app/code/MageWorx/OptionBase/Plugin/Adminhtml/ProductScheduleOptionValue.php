<?php

/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Plugin\Adminhtml;

use MageWorx\OptionBase\Helper\Data as OptionBaseHelper;
use \Magento\Framework\App\Request\Http as HttpRequest;

class ProductScheduleOptionValue
{
    /**
     * @var OptionBaseHelper
     */
    protected $helper;

    /**
     * @var HttpRequest
     */
    protected $request;

    public function __construct(
        OptionBaseHelper $helper,
        HttpRequest $request
    ) {
    
        $this->helper = $helper;
        $this->request = $request;
    }

    public function beforeSaveValues($subject)
    {
        if ($this->out()) {
            return [];
        }

        $values = $subject->getValues();

        foreach ($values as $id => $value) {
            $values[$id]['mageworx_option_type_id'] = null;
        }

        $subject->setValues($values);

        return [];
    }

    private function out()
    {
        if (!$this->request->getParam('staging')) {
            return true;
        }

        if (!$this->helper->isEnterprise()) {
            return true;
        }

        return false;
    }
}
