<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\Api;

class Factory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Creates new instances of API models
     *
     * @param string $className
     * @param array $data
     * @return \Klarna\Kco\Api\ApiInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, $data = [])
    {
        $method = $this->_objectManager->get($className);
        if (!$method instanceof \Klarna\Kco\Api\ApiInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('%1 class doesn\'t implement \Klarna\Kco\Api\ApiInterface', $className)
            );
        }
        return $method;
    }
}
