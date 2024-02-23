<?php

namespace Eniture\WweLtlFreightQuotes\Model\Carrier;

use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * class for admin configuration that runs first
 */
class WweLTLAdminConfiguration
{
    /**
     * @var object
     */
    private $registry;
    /**
     * @var Object
     */
    private $scopeConfig;

    /**peConfig
     * @param $scopeConfig
     * @param $registry ,
     */
    public function _init($scopeConfig, $registry)
    {
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->setCarriersAndHelpersCodesGlobally();
        $this->myUniqueLineItemAttribute();
    }

    /**
     * This function set unique Line Item Attributes of carriers
     */
    function myUniqueLineItemAttribute()
    {
        $lineItemAttArr = [];
        if ($this->registry->registry('UniqueLineItemAttributes') === null) {
            $this->registry->register('UniqueLineItemAttributes', $lineItemAttArr);
        }
    }

    /**
     * This function is for set carriers codes and helpers code globally
     */
    public function setCarriersAndHelpersCodesGlobally()
    {
        $this->setCodesGlobally('enitureCarrierCodes', 'ENWweLTL');
        $this->setCodesGlobally('enitureCarrierTitle', 'LTL Freight');
        $this->setCodesGlobally('enitureHelpersCodes', '\Eniture\WweLtlFreightQuotes');
        $this->setCodesGlobally('enitureActiveModules', $this->checkModuleIsEnabled());
        $this->setCodesGlobally('enitureModuleTypes', 'ltl');
    }

    /**
     * return if this module is enable or not
     * @return boolean
     */
    public function checkModuleIsEnabled()
    {
        return $this->scopeConfig->getValue("wweLtlConnSettings/first/active", ScopeInterface::SCOPE_STORE);
    }

    /**
     * This function sets Codes Globally e.g carrier code or helper code
     * @param $globArrayName
     * @param $arrValue
     */
    public function setCodesGlobally($globArrayName, $arrValue)
    {
        if ($this->registry->registry($globArrayName) === null) {
            $codesArray = [];
            $codesArray['wweLTL'] = $arrValue;
            $this->registry->register($globArrayName, $codesArray);
        } else {
            $codesArray = $this->registry->registry($globArrayName);
            $codesArray['wweLTL'] = $arrValue;
            $this->registry->unregister($globArrayName);
            $this->registry->register($globArrayName, $codesArray);
        }
    }

    /**
     * function that returns global active carrier array
     */
    public function updateActiveCarriersArray()
    {
        $isThisActive = $this->scopeConfig->getValue('wweLtlConnSettings/first/active', ScopeInterface::SCOPE_STORE);
        $isAllowOthers = $this->scopeConfig->getValue('wweLtlQuoteSetting/third/allowOther', ScopeInterface::SCOPE_STORE);

        if ($isThisActive) {
            if (is_null($this->registry->registry('EnActiveModules'))) {
                $codesArray = [];
                $codesArray['ENWweLtlFreightQuotes'] = $isAllowOthers;
                $this->registry->register('EnActiveModules', $codesArray);
            } else {
                $codesArray = $this->registry->registry('EnActiveModules');
                $codesArray['ENWweLtlFreightQuotes'] = $isAllowOthers;
                $this->registry->unregister('EnActiveModules');
                $this->registry->register('EnActiveModules', $codesArray);
            }
        }
    }
}
