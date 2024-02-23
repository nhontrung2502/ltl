<?php
/**
 * Small Package
 * @package      Small Package
 * @author      Eniture Technology
 */

namespace Eniture\WweLtlFreightQuotes\Model\Carrier;

/**
 *
 * Class for set carriers globally
 */
class WweLTLSetCarriersGlobally
{
    /**
     * @var
     */
    public $dataHelper;
    /**
     * @var
     */
    public $registry;

    /**
     * constructor of class
     * @param $dataHelper
     */
    public function _init($dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * function for managing carriers globally
     * @param $wweArr
     * @param $registry
     * @return boolean
     */
    public function manageCarriersGlobally($wweArr, $registry)
    {
        $this->registry = $registry;
        if ($this->registry->registry('enitureCarriers') === null) {
            $enitureCarriersArray = [];
            $enitureCarriersArray['wweLTL'] = $wweArr;
            $this->registry->register('enitureCarriers', $enitureCarriersArray);
        } else {
            $carriersArr = $this->registry->registry('enitureCarriers');
            $carriersArr['wweLTL'] = $wweArr;
            $this->registry->unregister('enitureCarriers');
            $this->registry->register('enitureCarriers', $carriersArr);
        }

        $activeEnModulesCount = $this->getActiveEnitureModulesCount();

        if (count($this->registry->registry('enitureCarriers')) < $activeEnModulesCount) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * function that return count of active Eniture modules
     * @return int
     */
    public function getActiveEnitureModulesCount()
    {
        $activeModules = array_keys($this->dataHelper->getActiveCarriersForENCount());
        $activeEnModulesArr = array_filter($activeModules, function ($moduleName) {
            if (substr($moduleName, 0, 2) == 'EN') {
                return true;
            }
            return false;
        });

        return count($activeEnModulesArr);
    }
}
