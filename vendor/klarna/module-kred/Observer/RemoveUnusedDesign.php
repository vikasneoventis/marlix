<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Observer;

use Klarna\Kred\Model\Api\Builder\Kred;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RemoveUnusedDesign implements ObserverInterface
{

    /**
     * Remove unused design options from Kred integration
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $builder = $observer->getBuilder();
        if ($builder instanceof Kred) {
            $create = $builder->getRequest();

            unset($create['options']['radius_border']);

            $observer->getBuilder()->setRequest($create);
        }
    }
}
