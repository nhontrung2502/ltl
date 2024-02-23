<?php

namespace Eniture\WweLtlFreightQuotes\Block\Adminhtml\Product;

use Eniture\WweLtlFreightQuotes\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Shipping\Model\Config;

class ProductPlanRestriction extends \Magento\Config\Block\System\Config\Form\Field
{
    const PRODUCT_TEMPLATE = 'product/productplanrestriction.phtml';

    public $enable = 'no';
    private $shipconfig;
    public $dataHelper;

    /**
     * ProductPlanRestriction constructor.
     * @param Context $context
     * @param Config $shipconfig
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $shipconfig,
        Data $dataHelper,
        array $data = []
    ) {
        $this->context = $context;
        $this->shipconfig = $shipconfig;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::PRODUCT_TEMPLATE);
        }
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return array
     */
    public function getPlanInfo()
    {
        $numLTL = $numSmPkg = $hazEn = $insEn = 0;
        $hazmat = $insurance = 'Disabled';
        $activeCarriers = array_keys($this->shipconfig->getActiveCarriers());

        foreach ($activeCarriers as $carrierCode) {
            $enCarrier = substr($carrierCode, 0, 2);
            if ($enCarrier == 'EN') {
                $carrierLabel = $this->getConfiguration($carrierCode, 'label');
                $carrierPlan = $this->getConfiguration($carrierCode, 'plan');

                $restriction['data'][$carrierCode] = [
                    'label' => $carrierLabel,
                    'plan' => $carrierPlan
                ];
                if (strpos($carrierCode, 'LTL') !== false) {
                    $numLTL++;
                }
                if (strpos($carrierCode, 'Smpkg') !== false) {
                    $numSmPkg++;
                }
                if ($carrierPlan > 1) {
                    $hazmat = $insurance = 'Enabled';
                    $hazEn++;
                }
                if ($numLTL) {
                    $restriction['data'][$carrierCode]['hazmat'] = $hazmat;
                }
                //elseif ($numSmPkg) old condition
//                if ($numSmPkg) {
                if ($carrierPlan > 1) {
                    $insEn++;
                }
                    $restriction['data'][$carrierCode]['hazmat'] = $hazmat;
                    $restriction['data'][$carrierCode]['insurance'] = $insurance;
//                }
            }
        }
        $restriction['hazCount'] = $numSmPkg + $numLTL;
        $restriction['insCount'] = $numSmPkg + $numLTL;
        $restriction['hazEnCount'] = $hazEn;
        $restriction['insEnCount'] = $insEn;
        return $restriction;
    }

    /**
     * @param $carrierCode
     * @param $reqFor
     * @return string | int/bool
     */
    public function getConfiguration($carrierCode, $reqFor)
    {
        return $this->context->getScopeConfig()->getValue(
            'eniture/' . $carrierCode . '/' . $reqFor . ''
        );
    }

    /**
     * @param array $planInfo
     * @return array
     */
    public function planMsg($planInfo)
    {
        $data = ['hazmat' => ['count' => 'hazCount',
            'enabled' => 'hazEnCount',
            'return' => 'hazmatMsg'],
            'insurance' => ['count' => 'insCount',
                'enabled' => 'insEnCount',
                'return' => 'insuranceMsg']
        ];
        $return = [];
        foreach ($data as $key => $value) {
            if ($planInfo[$value['count']] == $planInfo[$value['enabled']]) {
                $return[$value['return']] = null;
            } elseif ($planInfo[$value['enabled']] == 0) {
                $return[$value['return']] = '';
            } else {
                $return[$value['return']] = $this->setPlanMsg($planInfo['data'], $key);
            }
        }
        return $return;
    }

    /**
     * @param type $msgInfo
     * @param type $index
     * @return string
     */
    public function setPlanMsg($msgInfo, $index)
    {
        $msg = "";
        foreach ($msgInfo as $res) {
            if (isset($res[$index])) {
                if ($res[$index] == 'Enabled') {
                    $planMsg = ' ' . $res['label'] . ' : <b>' . $res[$index] . '</b>.<br>';
                }
                if ($res[$index] == 'Disabled') {
                    $planMsg = ' ' . $res['label'] . ' : Upgrade to <b>Standard Plan</b> to enable.<br>';
                }

                $msg .= $planMsg;
            }
        }

        return $msg;
    }

    /**
     * @param $planNumber
     * @param $carrierLabel
     * @return array
     */
    public function enableAttForPlans($planNumber, $carrierLabel)
    {
        if ($planNumber == 0 || $planNumber == 1 || $planNumber == -1) {
            $restriction = $this->createDataArray('', $carrierLabel);
        } else {
            $restriction = $this->createDataArray('Enabled', $carrierLabel);
        }

        return $restriction;
    }

    /**
     * @param $hazmat
     * @param $label
     * @return array
     */
    public function createDataArray($hazmat, $label)
    {
        return [
            'hazmat' => $hazmat,
            'label' => $label
        ];
    }
}
