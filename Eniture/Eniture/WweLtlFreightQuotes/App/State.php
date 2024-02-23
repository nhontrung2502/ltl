<?php

namespace Eniture\WweLtlFreightQuotes\App;

use Magento\Framework\App\Area;

/**
 * Class State
 * @package Eniture\WweLtlFreightQuotes\App
 *
 *
 */
class State extends \Magento\Framework\App\State
{
    /**
     * @return bool
     */
    public function validateAreaCode()
    {
        if (!isset($this->_areaCode)) {
            $this->setAreaCode(Area::AREA_GLOBAL);
        }
    }
}
