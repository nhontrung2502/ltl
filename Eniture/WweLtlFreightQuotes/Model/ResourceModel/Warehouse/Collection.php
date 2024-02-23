<?php

namespace Eniture\WweLtlFreightQuotes\Model\ResourceModel\Warehouse;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\WweLtlFreightQuotes\Model\Warehouse', 'Eniture\WweLtlFreightQuotes\Model\ResourceModel\Warehouse');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
