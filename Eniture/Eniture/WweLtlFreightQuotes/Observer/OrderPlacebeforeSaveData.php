<?php

namespace Eniture\WweLtlFreightQuotes\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class OrderPlacebeforeSaveData
 *
 * @package Eniture\WweLtlFreightQuotes\Observer
 */
class OrderPlacebeforeSaveData implements ObserverInterface
{
    /**
     * @var SessionManagerInterface
     */
    private $coreSession;
    public $offerLiftgateAsAnOption;

    /**
     * OrderPlacebeforeSaveData constructor.
     *
     * @param SessionManagerInterface $coreSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SessionManagerInterface $coreSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->coreSession = $coreSession;
        $this->offerLiftgateAsAnOption = $scopeConfig->getValue("WweLtQuoteSetting/third/OfferLiftgateAsAnOption", ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $isMulti = '0';
            $multiShip = false;
            $order = $observer->getEvent()->getOrder();
            $quote = $order->getQuote();

            if (isset($quote)) {
                $isMulti = $quote->getIsMultiShipping();
            }

            $method = $order->getShippingMethod();

            if (strpos($method, 'ENWweLTL') !== false) {
                $semiOrderDetailData = $this->coreSession->getSemiOrderDetailSession();
                $orderDetailData = $this->coreSession->getWweLtlOrderDetailSession();
                if ($orderDetailData != null && $semiOrderDetailData == null) {
                    if (count($orderDetailData['shipmentData']) > 1) {
                        $multiShip = true;
                    }
                    $orderDetailData = $this->getData($order, $method, $orderDetailData, $multiShip);
                } elseif ($semiOrderDetailData) {
                    $orderDetailData = $semiOrderDetailData['wweLTL'];
                    $this->coreSession->unsSemiOrderDetailSession();
                }
                $order->setData('order_detail_data', json_encode($orderDetailData));
                $order->save();
                if (!$isMulti) {
                    $this->coreSession->unsWweLtlOrderDetailSession();
                }
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function getData($order, $method, $orderDetailData, $multiShip)
    {
        $liftGate = $resi = false;
        $shippingMethod = empty($method) ? [] : explode('_', $method);
        /*These Lines are added for compatibility only*/
        $lgArray = ['always' => 1, 'asOption' => '', 'residentialLiftgate' => ''];
        $orderDetailData['residentialDelivery'] = 0;
        /*These Lines are added for compatibility only*/

        $arr = empty($method) ? [] : (explode('+', $method));
        if (in_array('LG', $arr)) {
            $orderDetailData['liftGateDelivery'] = $lgArray;
            $liftGate = true;
        }
        if (in_array('R', $arr)) {
            $orderDetailData['residentialDelivery'] = 1;
            $resi = true;
        }
        foreach ($orderDetailData['shipmentData'] as $key => $value) {
            if ($multiShip) {
                if ($shippingMethod[1] == 'ownArrangement') {
                    $orderDetailData['shipmentData'][$key]['quotes'] = [
                        'code' => $shippingMethod[1],
                        'title' => str_replace("WWE LTL Freight Quotes - ", "", $order->getShippingDescription()),
                        'rate' => number_format((float)$order->getShippingAmount(), 2, '.', '')
                    ];
                } else {
                    $quotes = reset($value['quotes']);
                    if ($liftGate) {
                        $orderDetailData['shipmentData'][$key]['quotes'] = $quotes['liftgate'];
                    } else {
                        $orderDetailData['shipmentData'][$key]['quotes'] = $quotes['simple'];
                    }
                }
            } else {
                $orderDetailData['shipmentData'][$key]['quotes'] = [

                    'code' => $shippingMethod[1],
                    'title' => str_replace("WWE LTL Freight Quotes - ", "", $order->getShippingDescription()),
                    'rate' => number_format((float)$order->getShippingAmount(), 2, '.', '')
                ];
            }
        }
        return $orderDetailData;
    }
}
