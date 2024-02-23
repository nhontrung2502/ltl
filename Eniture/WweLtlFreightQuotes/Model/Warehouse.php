<?php
namespace Eniture\WweLtlFreightQuotes\Model;

use Magento\Framework\Model\AbstractModel;

class Warehouse extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\WweLtlFreightQuotes\Model\ResourceModel\Warehouse');
    }
}
