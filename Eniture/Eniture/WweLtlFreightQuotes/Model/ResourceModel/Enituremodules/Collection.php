<?php

namespace Eniture\WweLtlFreightQuotes\Model\ResourceModel\Enituremodules;

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
        $this->_init('Eniture\WweLtlFreightQuotes\Model\Enituremodules', 'Eniture\WweLtlFreightQuotes\Model\ResourceModel\Enituremodules');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
