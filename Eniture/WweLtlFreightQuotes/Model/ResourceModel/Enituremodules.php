<?php
namespace Eniture\WweLtlFreightQuotes\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Enituremodules extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enituremodules', 'module_id');
    }
}
