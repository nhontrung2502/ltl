<?php
namespace Eniture\WweLtlFreightQuotes\Model\Source;

class WweLTLInsuranceCategory
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return ['ratingMethod' => ['value' => '84',  'label'  => 'General Merchandise'],
            ['value' => '85',  'label'  => 'Antiques / Art / Collectibles'],
            ['value' => '86',  'label'  => 'Commercial Electronics (Audio; Computer: Hardware, Servers, Parts & Accessories)'],
            ['value' => '87',  'label'  => 'Consumer Electronics (laptops, cellphones, PDAs, iPads, tablets, notebooks, etc.)'],
            ['value' => '88',  'label'  => 'Fragile Goods (Glass, Ceramic, Porcelain, etc.)'],
            ['value' => '89',  'label'  => 'Furniture (Pianos, Glassware, Tableware, Outdoor Furniture)'],
            ['value' => '90',  'label'  => 'Machinery, Appliances and Equipment (Medical, Restaurant, Industrial, Scientific)'],
            ['value' => '91',  'label'  => 'Miscellaneous / Other / Mixed'],
            ['value' => '92',  'label'  => 'Non-Perishable Foods / Beverages / Commodities / Vitamins'],
            ['value' => '93',  'label'  => 'Radioactive / Hazardous / Restricted or Controlled Items'],
            ['value' => '94',  'label'  => 'Sewing Machines, Equipment and Accessories'],
            ['value' => '95',  'label'  => 'Stone Products (Marble, Tile, Stonework, Granite, etc.)'],
            ['value' => '96',  'label'  => 'Wine / Spirits / Alcohol / Beer']
        ];
    }
}
