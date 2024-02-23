<?php
namespace Eniture\WweLtlFreightQuotes\Model\Source;

class WweLTLRatingMethod
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return ['ratingMethod' => ['value' => '1',  'label'  => 'Cheapest'],
            ['value' => '2',  'label'  => 'Cheapest Options'],
            ['value' => '3',  'label'  => 'Average'],
        ];
    }
}
