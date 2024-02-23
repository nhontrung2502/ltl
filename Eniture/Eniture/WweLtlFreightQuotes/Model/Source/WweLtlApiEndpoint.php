<?php

namespace Eniture\WweLtlFreightQuotes\Model\Source;

class WweLtlApiEndpoint implements \Magento\Framework\Option\ArrayInterface
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            [
                'value' => 'legacy',
                'label' => __('Legacy API')
            ],
            [
                'value' => 'new',
                'label' => __('New API')
            ],
        ];
    }
}
