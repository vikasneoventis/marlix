<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Model\Api;

class BuilderFactory
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
     * @return \Klarna\Core\Api\BuilderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, $data = [])
    {
        $method = $this->_objectManager->get($className);
        if (!$method instanceof \Klarna\Core\Api\BuilderInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('%1 class doesn\'t implement \Klarna\Core\Api\BuilderInterface', $className)
            );
        }
        return $method;
    }
}
