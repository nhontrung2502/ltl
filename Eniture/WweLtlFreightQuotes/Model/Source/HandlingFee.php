<?php
namespace Eniture\WweLtlFreightQuotes\Model\Source;

class HandlingFee
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'handlingFeeVal' => [ 'value' => 'flat',  'label'  => 'Flat Rate'],
                [ 'value' => '%',     'label'  => 'Percentage ( % )'],
        ];
    }
}
