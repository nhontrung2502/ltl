<?php

namespace Eniture\WweLtlFreightQuotes\Model;

use Magento\Directory\Model\Country;
use Magento\Framework\ObjectManagerInterface;

class WarehouseFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Eniture\WweLtlFreightQuotes\Model\Warehouse', $arguments, false);
    }
}
