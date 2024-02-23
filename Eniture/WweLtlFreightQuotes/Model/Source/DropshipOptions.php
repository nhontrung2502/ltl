<?php
namespace Eniture\WweLtlFreightQuotes\Model\Source;

use Eniture\FedExLTLFreightQuotes\Helper\Data;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Source class for Warehouse and Dropship
 */
class DropshipOptions extends AbstractSource
{
    /**
     * @var Data
     */
    public $dataHelper;
    /**
     * @var array
     */
    public $options = [];

    /**
     * DropshipOptions constructor.
     *
     * @param \Eniture\WweLtlFreightQuotes\Helper\Data $dataHelper
     */
    public function __construct(
        \Eniture\WweLtlFreightQuotes\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $get_dropship = $this->dataHelper->fetchWarehouseSecData('dropship');

        if (isset($get_dropship) && count($get_dropship) > 0) {
            foreach ($get_dropship as $manufacturer) {
                (isset($manufacturer['nickname']) && $manufacturer['nickname'] == '') ? $nickname = '' : $nickname = html_entity_decode($manufacturer['nickname'], ENT_QUOTES) . ' - ';
                $city       = $manufacturer['city'];
                $state      = $manufacturer['state'];
                $zip        = $manufacturer['zip'];
                $dropship   = $nickname . $city . ', ' . $state . ', ' . $zip;
                $this->options[] = [
                        'label' => __($dropship),
                        'value' => $manufacturer['warehouse_id'],
                    ];
            }
        }
        return $this->options;
    }

    /**
     * @param int|string $value
     * @return bool|string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions(false);

        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;
    }
}
