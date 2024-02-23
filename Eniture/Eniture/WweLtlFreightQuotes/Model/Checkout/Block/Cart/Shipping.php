<?php
/**
 * Eniture
 *
 * @package EnableCity
 * @author Eniture
 * @license https://eniture.com
 */

namespace Eniture\WweLtlFreightQuotes\Model\Checkout\Block\Cart;

use Magento\Checkout\Block\Cart\LayoutProcessor;
use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Checkout cart shipping block plugin
 */
class Shipping extends LayoutProcessor
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param AttributeMerger $merger
     * @param Collection $countryCollection
     * @param \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        AttributeMerger $merger,
        Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($merger, $countryCollection, $regionCollection);
    }

    /**
     * Show City in Shipping Estimation
     *
     * @return bool
     * @codeCoverageIgnore
     */
    protected function isCityActive()
    {
        if ($this->scopeConfig->getValue('carriers/ENWweLTL/active')) {
            return true;
        }
    }
}
