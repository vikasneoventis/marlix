<?php
/**
* @author      Michael Lühr <michael.luehr@netresearch.de>
* @category    Netresearch
* @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
* @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
namespace Netresearch\OPS\Model\Backend\Operation\Parameter\Additional;

interface AdditionalInterface
{
    public function extractAdditionalParams(\Magento\Sales\Model\AbstractModel $itemContainer);
}
