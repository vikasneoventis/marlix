<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Model;

use Klarna\Kred\Api\PushqueueInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Pushqueue extends AbstractModel implements IdentityInterface, PushqueueInterface
{
    const CACHE_TAG = 'klarna_kco_push_queue';

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    protected function _construct()
    {
        $this->_init('Klarna\Kred\Model\ResourceModel\Pushqueue');
    }
}
