<?php

namespace Eniture\WweLtlFreightQuotes\Helper;

use Eniture\WweLtlFreightQuotes\Model\Carrier\WweLTLFilterQuotes;
use Eniture\WweLtlFreightQuotes\Model\Interfaces\DataHelperInterface;
use Eniture\WweLtlFreightQuotes\Model\WarehouseFactory;
use Magento\Directory\Model\Country;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;
use \Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Class Data
 * @package Eniture\WweLtlFreightQuotes\Helper
 */
class Data extends AbstractHelper implements DataHelperInterface
{
    /**
     * @var Modulemanager Object
     */
    private $moduleManager;
    /**
     * @var Conn Object
     */
    private $connection;
    /**
     * @var Warehouse Table
     */
    private $WHTableName;
    /**
     * @var ship Config Object
     */
    private $shippingConfig;
    /**
     * @var context
     */
    private $context;
    /**
     * @var bool
     */
    public $canAddWh = 1;
    /**
     * @var Country
     */
    private $warehouseFactory;
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var bool
     */
    private $isResi = false;

    private $residentialDelivery;
    /**
     * @var SessionManagerInterface
     */
    public $coreSession;
    /**
     * @var string
     */
    private $resiLabel;
    /**
     * @var string
     */
    private $lgLabel;
    /**
     * @var string
     */
    private $resiLgLabel;
    /**
     * @var Manager
     */
    private $cacheManager;

    public $isMultiShipment = false;

    /*
     * @var configSettings
     * */
    public $configSettings;

    public $objectManager;
    public $wweLTLFilterQuotes;
    public $configWriter;
    public $residentialDlvry;
    public $liftGate;
    public $OfferLiftgateAsAnOption;
    public $RADforLiftgate;
    public $hndlngFee;
    public $symbolicHndlngFee;
    public $ratingMethod;
    public $labelAs;
    public $ownArangement;
    public $ownArangementText;
    public $options;
    public $dlrvyEstimates;


    /**
     * @param Context $context
     * @param Manager $moduleManager
     * @param ResourceConnection $resource
     * @param Config $shippingConfig
     * @param WweLTLFilterQuotes $wweLTLFilterQuotes
     * @param WarehouseFactory $warehouseFactory
     * @param Curl $curl
     * @param Registry $registry
     * @param SessionManagerInterface $coreSession
     * @param Manager $cacheManager
     * @param ObjectManagerInterface $objectmanager
     */
    public function __construct(
        Context $context,
        Manager $moduleManager,
        ResourceConnection $resource,
        Config $shippingConfig,
        WweLTLFilterQuotes $wweLTLFilterQuotes,
        WarehouseFactory $warehouseFactory,
        Curl $curl,
        Registry $registry,
        SessionManagerInterface $coreSession,
        Manager $cacheManager,
        ObjectManagerInterface $objectmanager,
        WriterInterface $configWriter
    ) {
        $this->moduleManager = $context->getModuleManager();
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->WHTableName = $resource->getTableName('warehouse');
        $this->shippingConfig = $shippingConfig;
        $this->context = $context;
        $this->wweLTLFilterQuotes = $wweLTLFilterQuotes;
        $this->warehouseFactory = $warehouseFactory;
        $this->curl = $curl;
        $this->registry = $registry;
        $this->coreSession = $coreSession;
        $this->cacheManager = $cacheManager;
        $this->objectManager = $objectmanager;
        $this->configWriter = $configWriter;
        parent::__construct($context);
    }

    /**
     * =======================================================
     * *********** Warehouse & DropShips Section *************
     * =======================================================
     * */

    /**
     * @param string $location
     * @return array
     */
    public function fetchWarehouseSecData($location)
    {
        $whCollection = $this->warehouseFactory->create()->getCollection()->addFilter('location', ['eq' => $location]);
        return $this->purifyCollectionData($whCollection);
    }

    /**
     * @param $location
     * @param $warehouseId
     * @return array
     */
    public function fetchWarehouseWithID($location, $warehouseId)
    {
        try {
            $whFactory = $this->warehouseFactory->create();
            $dsCollection = $whFactory->getCollection()
                ->addFilter('location', ['eq' => $location])
                ->addFilter('warehouse_id', ['eq' => $warehouseId]);
            return $this->purifyCollectionData($dsCollection);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @param $data
     * @param $whereClause
     * @return int
     */
    public function updateWarehouseData($data, $whereClause)
    {
        return $this->connection->update("$this->WHTableName", $data, "$whereClause");
    }

    /**
     * @param $data
     * @param $id
     * @return array
     */
    public function insertWarehouseData($data, $id)
    {
        $insertQry = $this->connection->insert("$this->WHTableName", $data);
        if ($insertQry == 0) {
            $lastId = $id;
        } else {
            $lastId = $this->connection->lastInsertId();
        }
        return ['insertId' => $insertQry, 'lastId' => $lastId];
    }

    /**
     * @param $data
     * @return int
     */
    public function deleteWarehouseSecData($data)
    {
        try {
            $response = $this->connection->delete("$this->WHTableName", $data);
        } catch (\Throwable $e) {
            $response = 0;
        }
        return $response;
    }

    /**
     * Data Array
     * @param $inputData
     * @return array
     */

    public function originArray($inputData)
    {
        $dataArr = [
            'city' => $inputData['city'],
            'state' => $inputData['state'],
            'zip' => $inputData['zip'],
            'country' => $inputData['country'],
            'location' => $inputData['location'],
            'nickname' => $inputData['nickname'] ?? '',
            'in_store' => 'null',
            'local_delivery' => 'null',
        ];
        $plan = $this->planInfo();
        if ($plan['planNumber'] == 3) {
            $suppressOption = ($inputData['ld_sup_rates'] === 'on') ? 1 : 0;
            //if (isset($inputData['instore_enable'])) {
            $pickupDeliveryArr = [
                'enable_store_pickup' => ($inputData['instore_enable'] === 'on') ? 1 : 0,
                'miles_store_pickup' => $inputData['is_within_miles'],
                'match_postal_store_pickup' => $inputData['is_postcode_match'],
                'checkout_desc_store_pickup' => $inputData['is_checkout_descp'],
                'suppress_other' => $suppressOption,
            ];
            $dataArr['in_store'] = json_encode($pickupDeliveryArr);

            //if ($inputData['ld_enable'] === 'on') {
            $localDeliveryArr = [
                'enable_local_delivery' => ($inputData['ld_enable'] === 'on') ? 1 : 0,
                'miles_local_delivery' => $inputData['ld_within_miles'],
                'match_postal_local_delivery' => $inputData['ld_postcode_match'],
                'checkout_desc_local_delivery' => $inputData['ld_checkout_descp'],
                'fee_local_delivery' => $inputData['ld_fee'],
                'suppress_other' => $suppressOption,
            ];
            $dataArr['local_delivery'] = json_encode($localDeliveryArr);
        }
        return $dataArr;
    }

    /**
     *
     * @param array $getWarehouse
     * @param array $validateData
     * @return string
     */
    public function checkUpdateInStorePickupDelivery($getWarehouse, $validateData)
    {
        $update = 'no';

        if (empty($getWarehouse)) {
            return $update;
        }

        $newData = [];
        $oldData = [];

        $getWarehouse = reset($getWarehouse);
        unset($getWarehouse['warehouse_id']);
        unset($getWarehouse['nickname']);
        unset($validateData['nickname']);

        foreach ($getWarehouse as $key => $value) {
            if (empty($value) || $value === null) {
                $newData[$key] = 'empty';
            } else {
                $oldData[$key] = trim($value);
            }
        }

        $whData = array_merge($newData, $oldData);
        $diff1 = array_diff($whData, $validateData);
        $diff2 = array_diff($validateData, $whData);

        if ((is_array($diff1) && !empty($diff1)) || (is_array($diff2) && !empty($diff2))) {
            $update = 'yes';
        }
        return $update;
    }

    /**
     * @param array $quotesArray
     * @param $inStoreLd
     * @return array
     */
    public function inStoreLocalDeliveryQuotes($quotesArray, $inStoreLd)
    {
        $data = $this->registry->registry('shipmentOrigin');
        if (count($data) > 1) {
            return $quotesArray;
        }

        foreach ($data as $array) {
            $warehouseData = $this->getWarehouseData($array);
            /**
             * Quotes array only to be made empty if Suppress other rates is ON and In-store
             *  Pickup or Local Delivery also carries some quotes. Else if In-store Pickup or
             *  Local Delivery does not have any quotes i.e Postal code or within miles does
             *  not match then the Quotes Array should be returned as it is.
             * */
            if (isset($warehouseData['suppress_other']) && $warehouseData['suppress_other']) {
                if ((isset($inStoreLd->inStorePickup->status) && $inStoreLd->inStorePickup->status == 1) ||
                    (isset($inStoreLd->localDelivery->status) && $inStoreLd->localDelivery->status == 1)
                ) {
                    $quotesArray = [];
                }
            }
            if (isset($inStoreLd->inStorePickup->status) && $inStoreLd->inStorePickup->status == 1) {
                $quotesArray[] = [
                    'code' => 'INSP',
                    'rate' => 0,
                    'transitTime' => '',
                    'title' => $warehouseData['inStoreTitle'],
                ];
            }

            if (isset($inStoreLd->localDelivery->status) && $inStoreLd->localDelivery->status == 1) {
                $quotesArray[] = [
                    'code' => 'LOCDEL',
                    'rate' => $warehouseData['fee_local_delivery'] ?? 0,
                    'transitTime' => '',
                    'title' => $warehouseData['locDelTitle'],
                ];
            }
        }
        return $quotesArray;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getWarehouseData($data)
    {
        $return = [];
        $whCollection = $this->fetchWarehouseWithID($data['location'], $data['locationId']);

        if(!empty($whCollection[0]['in_store']) && is_string($whCollection[0]['in_store'])){
            $inStore = json_decode($whCollection[0]['in_store'], true);
        }else{
            $inStore = [];
        }

        if(!empty($whCollection[0]['local_delivery']) && is_string($whCollection[0]['local_delivery'])){
            $locDel = json_decode($whCollection[0]['local_delivery'], true);
        }else{
            $locDel = [];
        }

        if ($inStore) {
            $inStoreTitle = $inStore['checkout_desc_store_pickup'];
            if (empty($inStoreTitle)) {
                $inStoreTitle = "In-store pick up";
            }
            $return['inStoreTitle'] = $inStoreTitle;
            $return['suppress_other'] = $inStore['suppress_other'] == '1' ? true : false;
        }
        if ($locDel) {
            $locDelTitle = $locDel['checkout_desc_local_delivery'];
            if (empty($locDelTitle)) {
                $locDelTitle = "Local delivery";
            }
            $return['locDelTitle'] = $locDelTitle;
            $return['fee_local_delivery'] = $locDel['fee_local_delivery'];
            $return['suppress_other'] = $locDel['suppress_other'] == '1' ? true : false;
        }

        return $return;
    }

    /**
     * =======================================================
     * ******************** Plans Section ********************
     * =======================================================
     * */

    /**
     * @return string
     */
    public function setPlanNotice($planRefreshUrl = '')
    {
        $planPackage = $this->planInfo();
        if ($planPackage['storeType'] == '') {
            $planPackage = [];
        }
        return $this->displayPlanMessages($planPackage, $planRefreshUrl);
    }

    /**
     * @param $planPackage
     * @return string
     */
    public function displayPlanMessages($planPackage, $planRefreshUrl = '')
    {
        $planRefreshLink = '';
        if (!empty($planRefreshUrl)) {
            $planRefreshLink = ', <a href="javascript:void(0)" id="wwe-ltl-plan-refresh-link" planRefAjaxUrl = '.$planRefreshUrl.' onclick="wweLTLPlanRefresh(this)" >click here</a> to update the license info. Afterward, sign out of Magento and then sign back in';
            $planMsg = __('The subscription to the Worldwide Express LTL Freight Quotes module is inactive. If you believe the subscription should be active and you recently changed plans (e.g. upgraded your plan), your firewall may be blocking confirmation from our licensing system. To resolve the situation, <a href="javascript:void(0)" id="plan-refresh-link" planRefAjaxUrl = '.$planRefreshUrl.' onclick="wweLTLPlanRefresh(this)" >click this link</a> and then sign in again. If this does not resolve the issue, log in to eniture.com and verify the license status.');
        }else{
            $planMsg = __('The subscription to the Worldwide Express LTL Freight Quotes module is inactive. Please log into eniture.com and update your license.');
        }

        if (isset($planPackage) && !empty($planPackage)) {
            if (isset($planPackage['planNumber']) && $planPackage['planNumber'] != '' && $planPackage['planNumber'] != '-1') {
                $planMsg = __('The Worldwide Express LTL Freight Quotes from Eniture Technology is currently on the '.$planPackage['planName'].' and will renew on '.$planPackage['expiryDate'].'. If this does not reflect changes made to the subscription plan'.$planRefreshLink.'.');
            }
        }

        return $planMsg;
    }

    /**
     * @return int
     */
    public function whPlanRestriction()
    {
        $planNumber = $this->planInfo()['planNumber'];
        $warehouses = $this->fetchWarehouseSecData('warehouse');
        if ($planNumber < '2' && count($warehouses)) {
            $this->canAddWh = 0;
        }
        return $this->canAddWh;
    }

    /**
     * =======================================================
     * ***************** Validation Section ******************
     * =======================================================
     * */

    /**
     * @param $whCollection
     * @return array
     */
    public function purifyCollectionData($whCollection)
    {
        $warehouseSecData = [];
        foreach ($whCollection as $wh) {
            $warehouseSecData[] = $wh->getData();
        }
        return $warehouseSecData;
    }

    /**
     * validate Input Post
     * @param $sPostData
     * @return mixed
     */
    public function validatedPostData($sPostData)
    {
        $dataArray = ['city', 'state', 'zip', 'country'];
        $data = [];
        foreach ($sPostData as $key => $tag) {
            $preg = '/[#$%@^&_*!()+=\-\[\]\';,.\/{}|":<>?~\\\\]/';
            $check_characters = (in_array($key, $dataArray)) ? preg_match($preg, $tag) : '';

            if ($check_characters != 1) {
                if ($key === 'city' || $key === 'nickname' || $key === 'in_store' || $key === 'local_delivery') {
                    $data[$key] = $tag;
                } else {
                    $data[$key] = preg_replace('/\s+/', '', $tag);
                }
            } else {
                $data[$key] = 'Error';
            }
        }

        return $data;
    }

    /**
     * =======================================================
     * ************ Order detail widget Section **************
     * =======================================================
     * */

    /**
     * @param array $servicesArr
     * @param $hazShipmentArr
     */
    public function setOrderDetailWidgetData(array $servicesArr, $hazShipmentArr)
    {
        $setPkgForOrderDetailReg = $this->registry->registry('setPackageDataForOrderDetail') ?? [];
        $planNumber = $this->planInfo()['planNumber'];

        if ($planNumber > 1 && $setPkgForOrderDetailReg && $hazShipmentArr) {
            foreach ($hazShipmentArr as $origin => $value) {
                foreach ($setPkgForOrderDetailReg[$origin]['item'] as $key => $data) {
                    $setPkgForOrderDetailReg[$origin]['item'][$key]['isHazmatLineItem'] = $value;
                    break;
                }
            }
        }
        $orderDetail['shipmentData'] = array_replace_recursive($setPkgForOrderDetailReg, $servicesArr);
        // set order detail widget data
        $this->coreSession->start();
        $this->coreSession->setWweLtlOrderDetailSession($orderDetail);
    }

    /**
     * =======================================================
     * ********* Settings and configuration Section **********
     * =======================================================
     * */

    /**
     * setting properties dynamically
     */
    public function quoteSettingsData()
    {
        $fields = [
            'labelAs' => 'labelAs',
            'options' => 'options',
            'ratingMethod' => 'ratingMethod',
            'dlrvyEstimates' => 'dlrvyEstimates',
            'ownArangement' => 'ownArangement',
            'ownArangementText' => 'ownArangementText',
            'residentialDlvry' => 'residentialDlvry',
            'liftGate' => 'liftGate',
            'OfferLiftgateAsAnOption' => 'OfferLiftgateAsAnOption',
            'RADforLiftgate' => 'RADforLiftgate',
            'hndlngFee' => 'hndlngFee',
            'symbolicHndlngFee' => 'symbolicHndlngFee',
        ];
        foreach ($fields as $key => $field) {
            $this->$key = $this->configSettings[$field] ?? '';
        }
        $this->resiLabel = ' with residential delivery';
        $this->lgLabel = ' with lift gate delivery';
        $this->resiLgLabel = ' with residential delivery and lift gate delivery';
    }

    /**
     * @param $confPath
     * @return mixed
     */
    public function getConfigData($confPath)
    {
        return $this->scopeConfig->getValue($confPath, ScopeInterface::SCOPE_STORE);
    }

    /**
     * This function send request and return response
     * $isAssocArray Parameter When TRUE, then returned objects will
     * be converted into associative arrays, otherwise its an object
     * @param string $url
     * @param array $postData
     * @param bool $isAssocArray
     * @return object|array
     */
    public function sendCurlRequest($url, $postData, $isAssocArray = false)
    {
        $fieldString = http_build_query($postData);
        try {
            $this->curl->post($url, $fieldString);
            $output = $this->curl->getBody();
            if(!empty($output) && is_string($output)){
                $result = json_decode($output, $isAssocArray);
            }else{
                $result = ($isAssocArray) ? [] : '';
            }

        } catch (\Throwable $e) {
            $result = [];
        }
        return $result;
    }

    /**
     * =======================================================
     * ******************** RAD Section **********************
     * =======================================================
     * */

    /**
     * @param string $resi
     * @return string
     */
    public function getAutoResidentialTitle($resi)
    {
        if ($this->moduleManager->isEnabled('Eniture_ResidentialAddressDetection')) {
            $isRadSuspend = $this->getConfigData("resaddressdetection/suspend/value");
            if ($this->residentialDlvry == "1") {
                $this->residentialDlvry = $isRadSuspend == "no" ? '0' : '1';
            } else {
                $this->residentialDlvry = $isRadSuspend == "no" ? '0' : $this->residentialDlvry;
            }

            if ($this->residentialDlvry == null || $this->residentialDlvry == '0') {
                if ($resi == 'r') {
                    $this->isResi = true;
                }
            }
        }
    }

    /**
     * =======================================================
     * ***************** Get Quotes Section ******************
     * =======================================================
     * */

    /**
     * @param object $quotes
     * @param bool $getMinimum
     * @param bool $isMultiShipmentQuantity (this param will be true when semi case will be executed)
     * @param object $scopeConfig
     * @return array
     *
     * @info: This function will compile all quotes according to the origin.
     * After getting from quotes almost all type of compilation happened in this function
     */
    public function getQuotesResults($quotes, $getMinimum, $isMultiShipmentQuantity, $scopeConfig)
    {
        $this->configSettings = $this->getConfigData('WweLtQuoteSetting/third');
        $allConfigServices = $this->getAllConfigServicesArray($scopeConfig);
        $this->quoteSettingsData();

        // Migration from Legacy to NEW API
        $quotes = $this->migrateApiIfNeeded($quotes);

        if ($isMultiShipmentQuantity) {
            return $this->getOriginsMinimumQuotes($quotes, $allConfigServices);
        }
        $allQuotes = $odwArr = $hazShipmentArr = [];
        $count = 0;
        $lgQuotes = false;
        $this->isMultiShipment = (count($quotes) > 1) ? true : false;
        
        foreach ($quotes as $origin => $quote) {
            if (isset($quote->severity)) {
                return [];
            }

            if ($count == 0) { //To be checked only once
                $isRad = $quote->autoResidentialsStatus ?? '';
                $this->getAutoResidentialTitle($isRad);
                $inStoreLdData = $quote->InstorPickupLocalDelivery ?? false;
                unset($quote->InstorPickupLocalDelivery);
                $lgQuotes = ($this->liftGate || $this->OfferLiftgateAsAnOption || ($this->isResi && $this->RADforLiftgate)) ? true : false;
            }

            $originQuotes = [];
            $arraySorting = [];
            if (isset($quote->q)) {
                if (isset($quote->hazardousStatus)) {
                    $hazShipmentArr[$origin] = $quote->hazardousStatus == 'y' ? 'Y' : 'N';
                }

                foreach ($quote->q as $key => $data) {
                    if (isset($data->serviceType) && in_array($data->serviceType, $allConfigServices)
                    && (empty($data->GuaranteedDaysToDelivery) || $data->GuaranteedDaysToDelivery != 'Y')) {
                        $access = $this->getAccessorialCode();
                        $price = $this->calculatePrice($data);
                        $title = $this->getTitle($data->serviceDesc, false, false, $data->transitTime);
                        $arraySorting['simple'][$key] = $price;
                        $originQuotes[$key]['simple']['code'] = $data->serviceType . $access;
                        $originQuotes[$key]['simple']['rate'] = $price;
                        $originQuotes[$key]['simple']['title'] = $title;
                        if ($lgQuotes) {
                            $lgAccess = $this->getAccessorialCode(true);
                            $lgPrice = $this->calculatePrice($data, true);
                            $lgTitle = $this->getTitle($data->serviceDesc, true, false, $data->transitTime);
                            $arraySorting['liftgate'][$key] = $lgPrice;
                            $originQuotes[$key]['liftgate']['code'] = $data->serviceType . $lgAccess;
                            $originQuotes[$key]['liftgate']['rate'] = $lgPrice;
                            $originQuotes[$key]['liftgate']['title'] = $lgTitle;
                        }
                    }
                }
            }

            //Todo: function naming according to the functionality
            $compiledQuotes = $this->getCompiledQuotes($originQuotes, $arraySorting, $lgQuotes);
            if (!empty($compiledQuotes)) {
                if (count($compiledQuotes) > 1) {
                    foreach ($compiledQuotes as $k => $service) {
                        $allQuotes['simple'][] = $service['simple'];
                        $lgQuotes ? $allQuotes['liftgate'][] = $service['liftgate'] : '';
                    }
                } else {
                    $service = reset($compiledQuotes);
                    $allQuotes['simple'][] = $service['simple'];
                    $lgQuotes ? $allQuotes['liftgate'][] = $service['liftgate'] : '';
                }
            }
            if ($this->isMultiShipment) {
                $odwArr[$origin]['quotes'] = $compiledQuotes;
            }
            $count++;
        }
        $this->setOrderDetailWidgetData($odwArr, $hazShipmentArr);
        if (!empty($allQuotes)) {
            $allQuotes = $this->getFinalQuotesArray($allQuotes);
        }
        if (!$this->isMultiShipment && isset($inStoreLdData) && !empty($inStoreLdData)) {
            $allQuotes = $this->inStoreLocalDeliveryQuotes($allQuotes, $inStoreLdData);
        }
        return $this->arrangeOwnFreight($allQuotes);
    }

    /**
     * Calculate Handling Fee
     * @param $cost
     * @return float
     */
    public function calculateHandlingFee($cost)
    {
        $handlingFeeMarkup = $this->hndlngFee;
        $symbolicHandlingFee = $this->symbolicHndlngFee;

        if (!empty($handlingFeeMarkup) > 0) {
            if ($symbolicHandlingFee == '%') {
                $percentVal = $handlingFeeMarkup / 100 * $cost;
                $grandTotal = $percentVal + $cost;
            } else {
                $grandTotal = $handlingFeeMarkup + $cost;
            }
        } else {
            $grandTotal = $cost;
        }
        return $grandTotal;
    }

    /**
     * =======================================================
     * ************ Extension's Native Section ***************
     * =======================================================
     * */

    /**
     * @param $quotes
     * @return array
     *
     * @info: This function will arrange array of quotes according to the accessorials.
     * This function will handle single shipment and multi shipment both for return final array.
     */
    public function getFinalQuotesArray($quotes)
    {
        $lfg = $this->liftGate == 1 || ($this->isResi && $this->RADforLiftgate);
        if ($this->isMultiShipment == false) {
            if (isset($quotes['liftgate']) && $this->OfferLiftgateAsAnOption == 1 && ($this->RADforLiftgate == 0 || $this->isResi == 0)) {
                /**
                 * Condition for lift gate as an option
                 * */
                return array_merge($quotes['simple'], $quotes['liftgate']);
            } elseif ($lfg) {
                /**
                 * Condition for Always lift gate and lift gate for residential (Single Shipment)
                 * */
                return $quotes['liftgate'];
            } else {
                return $quotes['simple'];
            }
        } elseif ($lfg) {
            /**
             * Condition for always lift gate and lift gate for residential (Multi Shipment)
             * */
            unset($quotes['simple']);
        }
        return $this->organizeQuotesArray($quotes);
    }

    public function organizeQuotesArray($quotes)
    {
        $quotesArr = [];
        foreach ($quotes as $key => $value) {
            if ($this->isMultiShipment) {
                $rate = 0;
                $code = '';
                $isLiftGate = $key == 'liftgate' ? true : false;
                foreach ($value as $key2 => $data) {
                    $rate += $data['rate'];
                    $code = $data['code'];
                }
                $quotesArr[] = [
                    'code' => $code,
                    'rate' => $rate,
                    'title' => $this->getTitle('FRT', $isLiftGate, true)
                ];
            } else {
                $quotesArr[] = reset($value);
            }
        }
        return $quotesArr;
    }

    public function checkAccessorial($code, $lg)
    {
        $return = 'CFMS';
        $lg ? $return = $return . '+LG' : '';
        $arr = empty($code) ? [] : (explode('+', $code));
        if (in_array('R', $arr)) {
            $return = $return . '+R';
        }
        return $return;
    }

    public function arraySortByColumn(&$arr, $col, $dir = SORT_ASC)
    {
        $sort_col = [];
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr);
    }

    /**
     *
     * @return string
     */
    public function getRatingMethod()
    {
        $ratingMethod = 'Cheapest';
        switch ($this->ratingMethod) {
            case 1:
                $ratingMethod = 'Cheapest';
                break;
            case 2:
                $ratingMethod = 'cheapestOptions';
                break;
            case 3:
                $ratingMethod = 'averageRate';
                break;
        }
        return $ratingMethod;
    }

    /**
     * @param bool $lgOption
     * @return string
     *
     * @info: This will return specific code according to the accessorials for appending with the service code.
     */
    public function getAccessorialCode($lgOption = false)
    {
        $access = '';
        if ($this->residentialDlvry == '1' || $this->isResi) {
            $access .= '+R';
        }
        if (($lgOption || $this->liftGate == '1') || ($this->RADforLiftgate && $this->isResi)) {
            $access .= '+LG';
        }

        return $access;
    }

    /**
     * @param object $data
     * @param bool $lgOption
     * @param bool $getCost
     * @return float
     *
     * @info: This function will calculate all prices and return price against a specific service
     */
    public function calculatePrice($data, $lgOption = false, $getCost = false)
    {
        $lgCost = $lgOption ? 0 : $this->getLiftGateCost($data, $getCost);
        $basePrice = (float)$data->totalNetCharge->Amount;
        $basePrice = $basePrice - $lgCost;
        $basePrice = $this->calculateHandlingFee($basePrice);
        return $basePrice;
    }

    /**
     * @param $quotes
     * @param bool $getCost
     * @return float
     */
    public function getLiftGateCost($quotes, $getCost = false)
    {
        $lgCost = 0;
        if (!(($this->isResi && $this->RADforLiftgate) || $this->liftGate == '1') || $getCost) {
            if (isset($quotes->surcharges) && isset($quotes->surcharges->liftgateFee)) {
                $lgCost = $quotes->surcharges->liftgateFee;
            }
        }
        return $lgCost;
    }

    /**
     * Calculate Handling Fee
     * @param $cost
     * @return float
     */
    public function calculateHandlingFeeAz($cost)
    {
        $handlingFeeMarkup = $this->hndlngFee;
        $symbolicHandlingFee = $this->symbolicHndlngFee;

        if (!empty($handlingFeeMarkup) > 0) {
            if ($symbolicHandlingFee == '%') {
                $percentVal = $handlingFeeMarkup / 100 * $cost;
                $grandTotal = $percentVal + $cost;
            } else {
                $grandTotal = $handlingFeeMarkup + $cost;
            }
        } else {
            $grandTotal = $cost;
        }
        return $grandTotal;
    }

    /**
     * @param $serviceName
     * @param bool $lgOption
     * @param bool $from
     * @param string $deliveryEstimate
     * @return string
     *
     * @info: This function will compile name of a service and return service name according to the settings enabled.
     */
    public function getTitle($serviceName, $lgOption = false, $from = false, $deliveryEstimate = '')
    {
        $serviceTitle = $this->customLabel($serviceName);
        if ($this->isMultiShipment && $from == false) {
            return $serviceTitle;
        }
        $deliveryEstimateLabel = (!empty($deliveryEstimate) && $this->configSettings['dlrvyEstimates']) ? ' (Estimated transit time of ' . $deliveryEstimate . ' business days)' : '';
        $accessTitle = '';
        if ($lgOption === true || $this->RADforLiftgate) {
            if ($lgOption && $this->liftGate == '0') {
                $accessTitle = $this->isResi ? $this->resiLgLabel : $this->lgLabel;
            }
            if ($this->liftGate == 1 && $this->isResi) {
                $accessTitle = $this->resiLabel;
            }
            if ($this->RADforLiftgate && $this->isResi) {
                $accessTitle = $this->resiLgLabel;
            }
        } elseif ($this->isResi) {
            $accessTitle = $this->resiLabel;
        }
        return $serviceTitle . $accessTitle . $deliveryEstimateLabel;
    }

    /**
     * @param string $resi
     * @return string
     */
    public function getAutoResidentialTitleAz($resi)
    {
        if ($this->moduleManager->isEnabled('Eniture_ResidentialAddressDetection')) {
            $isRadSuspend = $this->getConfigData("resaddressdetection/suspend/value");
            if ($this->residentialDlvry == "1") {
                $this->residentialDlvry = $isRadSuspend == "no" ? '0' : '1';
            } else {
                $this->residentialDlvry = $isRadSuspend == "no" ? '0' : $this->residentialDlvry;
            }

            if ($this->residentialDlvry == null || $this->residentialDlvry == '0') {
                if ($resi == 'r') {
                    $this->isResi = true;
                }
            }
        }
    }

    /**
     * @param $quotes
     * @param $allConfigServices
     * @return array
     */
    public function getOriginsMinimumQuotes($quotes, $allConfigServices)
    {
        $minIndexArr = [];
        $resiArr = ['residential' => false, 'label' => ''];
        foreach ($quotes as $key => $quote) {
            $minInQ = [];
            $counter = 0;
            $isRad = $quote->autoResidentialsStatus ?? '';
            $autoResTitle = $this->getAutoResidentialTitle($isRad);
            if ($this->residentialDlvry == "1" || $autoResTitle != '') {
                $resiArr = ['residential' => true, 'label' => $autoResTitle];
            }
            if (isset($quote->q)) {
                foreach ($quote->q as $serKey => $availSer) {
                    if (isset($availSer->serviceType) && in_array($availSer->serviceType, $allConfigServices)) {
                        //$liftGateCharge     = $this->getLiftgateCost($availSer);
                        //$totalCost          = $this->calculateHandlingFee($rate);
                        $totalCost          = $this->calculatePrice($availSer, false, true);
                        $currentArray = [
                            'code'=> str_replace('_', '', $availSer->serviceType),
                            'rate' => $totalCost,
                            'title' => $this->labelAs . $autoResTitle,
                            'resi' => $resiArr
                        ];
                        if ($counter == 0) {
                            $minInQ = $currentArray;
                        } else {
                            $minInQ = ($currentArray['rate'] < $minInQ['rate'] ? $currentArray : $minInQ);
                        }
                        $counter ++;
                    }
                }
                if ($minInQ['rate'] > 0) {
                    $minIndexArr[$key] = $minInQ;
                }
            }
            $minIndexArr[$key] = $currentArray;
        }
        return $minIndexArr;
    }

    /**
     * This function returns minimum array index from array
     * @param $servicesArr
     * @return array
     */
    public function findArrayMininum($servicesArr)
    {
        $counter = 1;
        $minIndex = [];
        foreach ($servicesArr as $value) {
            if ($counter == 1) {
                $minimum = $value['rate'];
                $minIndex = $value;
                $counter = 0;
            } else {
                if ($value['rate'] < $minimum) {
                    $minimum = $value['rate'];
                    $minIndex = $value;
                }
            }
        }
        return $minIndex;
    }

    /**
     * This Function returns all active services array from configurations
     * @param $scopeConfig
     * @return array
     */
    public function getAllConfigServicesArray($scopeConfig)
    {
        if(empty($this->configSettings['carrierList'])){
            return [];
        }else{
            return empty($this->configSettings['carrierList']) ? [] : explode(',', $this->configSettings['carrierList']);
        }
    }

    /**
     * Final quotes array
     * @param $grandTotal
     * @param $code
     * @param $title
     * @param $appendLabel
     * @return array
     */
    public function getFinalQuoteArray($grandTotal, $code, $title, $appendLabel)
    {
        $allowed = [];

        if ($grandTotal > 0) {
            $allowed = [
                'code' => $code,// or carrier name
                'title' => $title . $appendLabel,
                'rate' => $grandTotal
            ];
        }

        return $allowed;
    }

    public function checkOwnArrangement($finalArr)
    {
        if (isset($this->ownArangement) && $this->ownArangement == 1) {
            $title = (isset($this->ownArangementText) && trim($this->ownArangementText) != '') ? $this->ownArangementText :
                "I'll Arrange My Own Freight";
            $finalArr[] = ['code' => 'OWAR',// or carrier name
                'title' => $title,
                'rate' => 0
            ];
        }

        return $finalArr;
    }

    public function adminConfigData($fieldId, $scopeConfig)
    {
        return $scopeConfig->getValue("cerasisQuoteSetting/fourth/$fieldId", ScopeInterface::SCOPE_STORE);
    }

    /**
     *
     * @return AbstractCarrierInterface[]
     */
    public function getActiveCarriersForENCount()
    {
        return $this->shippingConfig->getActiveCarriers();
    }

    /**
     * function return service data
     * @param $fieldId
     * @return string
     */
    public function getTestConnConfigData($fieldId)
    {
        $sectionId = 'cerasisConnSettings';
        $groupId = 'first';

        return $this->scopeConfig->getValue("$sectionId/$groupId/$fieldId", ScopeInterface::SCOPE_STORE);
    }

    /**
     * validate Input Post
     * @param $sPostData
     * @return mixed
     */
    public function LTLValidatedPostData($sPostData)
    {
        $dataArray = ['city', 'state', 'zip', 'country'];
        $data = [];
        foreach ($sPostData as $key => $tag) {
            $preg = '/[#$%@^&_*!()+=\-\[\]\';,.\/{}|":<>?~\\\\]/';
            $check_characters = (in_array($key, $dataArray)) ? preg_match($preg, $tag) : '';

            if ($check_characters != 1) {
                if ($key === 'city' || $key === 'nickname' || $key === 'in_store' || $key === 'local_delivery') {
                    $data[$key] = $tag;
                } else {
                    $data[$key] = preg_replace('/\s+/', '', $tag);
                }
            } else {
                $data[$key] = 'Error';
            }
        }

        return $data;
    }


    /**
     * Get Plan detail
     * @return array
     */
    public function planInfo()
    {
        $planData = $this->coreSession->getPlanDetail();
        if ($planData == null) {
            $appData = $this->getConfigData("eniture/ENWweLTL");
            $plan = $appData["plan"] ?? '-1';
            $storeType = $appData["storetype"] ?? '';
            $expireDays = $appData["expireday"] ?? '';
            $expiryDate = $appData["expiredate"] ?? '';
            $planName = "";
            switch ($plan) {
                case 3:
                    $planName = "Advanced Plan";
                    break;
                case 2:
                    $planName = "Standard Plan";
                    break;
                case 1:
                    $planName = "Basic Plan";
                    break;
                case 0:
                    $planName = "Trial Plan";
                    break;
            }
            $planData = [
                'planNumber' => $plan,
                'planName' => $planName,
                'expireDays' => $expireDays,
                'expiryDate' => $expiryDate,
                'storeType' => $storeType
            ];
            $this->coreSession->setPlanDetail($planData);
        }
        return $planData;
    }

    /**
     * @return string
     */
    public function ltlSetPlanNotice()
    {
        $planPackage = $this->planInfo();
        if ($planPackage['storeType'] == '') {
            $planPackage = [];
        }
        return $this->displayPlanMessages($planPackage);
    }

    /**
     *
     */
    public function clearCache()
    {
        $types = $this->cacheManager->getAvailableTypes();
        $this->cacheManager->flush($types);
        $this->cacheManager->clean($types);
    }

    /**
     * @param null $msg
     * @param bool $type
     * @return array
     */
    public function generateResponse($msg = null, $type = true)
    {
        $defaultError = 'Empty response from API.';
        return [
            'error' => ($type == true) ? 1 : 0,
            'msg' => ($msg != null) ? $msg : $defaultError
        ];
    }

    public function unsetPlanSession()
    {
        $this->coreSession->unsPlanDetail();
    }

    /**
     * @inheritDoc
     */
    public function getLiftGateDeliveryOptions($orderDetail)
    {
        // TODO: Implement getLiftGateDeliveryOptions() method.
    }

    /**
     * @param $services
     * @param $arraySorting
     * @param $lgQuotes
     * @return array
     *
     * @info: This function will compile quotes according the selected rating method.
     */
    public function getCompiledQuotes($services, $arraySorting, $lgQuotes)
    {
        if (empty($arraySorting) || empty($services)) {
            return [];
        }
        asort($arraySorting['simple']);
        $options = ($this->configSettings['ratingMethod'] > 1 && $this->isMultiShipment == false) ? (int)$this->configSettings['options'] : 1;
        $sliced = array_slice($arraySorting['simple'], 0, $options, true);
        if ($this->configSettings['ratingMethod'] == 3) {
            return $this->averageRattingMethod($arraySorting, $options, $lgQuotes);
        }
        return array_intersect_key($services, $sliced);
    }

    /**
     * @param $ratesArray
     * @param $options
     * @param $lgQuotes
     * @return array
     */
    public function averageRattingMethod($ratesArray, $options, $lgQuotes)
    {
        $sliced = array_slice($ratesArray['simple'], 0, $options, true);
        $simplePrice = $this->getAveragePrice($sliced, $options);
        $averageRateService[0]['simple'] = [
            'title' => $this->getTitle('Freight', false, true),
            'code' => 'AVG' . $this->getAccessorialCode(),
            'rate' => $simplePrice,
        ];
        if ($lgQuotes) {
            asort($ratesArray['liftgate']);
            $sliced = array_slice($ratesArray['liftgate'], 0, $options, true);
            $lfgPrice = $this->getAveragePrice($sliced, $options);
            $averageRateService[0]['liftgate'] = [
                'title' => $this->getTitle('Freight', $lgQuotes, true),
                'code' => 'AVG' . $this->getAccessorialCode($lgQuotes),
                'rate' => $lfgPrice,
            ];
        }
        return $averageRateService;
    }

    public function getAveragePrice($arraySorting, $options)
    {
        $numOfIndexes = count($arraySorting);
        $divider = ($numOfIndexes == $options) ? $options : $numOfIndexes;
        return array_sum($arraySorting) / $divider;
    }

    public function customLabel($serviceName)
    {
        if($serviceName == 'FRT'){
            return 'Freight';
        }else{
            return (($this->configSettings['ratingMethod'] == 1 || $this->configSettings['ratingMethod'] == 3) && $this->configSettings['labelAs'] != null) ? $this->configSettings['labelAs'] : $serviceName;
        }
    }

    /**
     * @param $finalQuotes
     * @return array
     */
    public function arrangeOwnFreight($finalQuotes)
    {
        if ($this->ownArangement == 0) {
            return $finalQuotes;
        }
        $ownArrangement = [];
        $ownArrangement[] = [
            'code' => 'ownArrangement',
            'title' => (!empty($this->ownArangementText)) ? $this->ownArangementText : "I'll Arrange My Own Freight",
            'rate' => 0
        ];
        return array_merge($finalQuotes, $ownArrangement);
    }

    /**
     * @return array
     */
    public function quoteSettingFieldsToRestrict()
    {
        $restriction = [];
        $currentPlan = $this->planInfo()['planNumber'];
        $standard = [
            'enableCuttOff'
        ];
        $advance = [];
        switch ($currentPlan) {
            case 2:
            case 3:
                break;

            default:
                $restriction = [
                // 'advance' => $advance,
                    'standard' => $standard
                ];
                break;
        }
        return $restriction;
    }

    public function getBoxHelper($objectName)
    {
        if ($objectName == 'helper') {
            return $this->objectManager->get("Eniture\StandardBoxSizes\Helper\Data");
        }
        if ($objectName == 'boxFactory') {
            $boxHelper =  $this->objectManager->get("Eniture\StandardBoxSizes\Helper\Data");
            return $boxHelper->getBoxFactory();
        }
    }

    /**
     * Function to migrate API
     */
    protected function migrateApiIfNeeded($quotes)
    {
        foreach ($quotes as $key => $quote) {
            if(isset($quote->newAPICredentials) && !empty($quote->newAPICredentials->client_id) && !empty($quote->newAPICredentials->client_secret)){
                $this->configWriter->save('WweLtConnSettings/first/wweltlClientId', $quote->newAPICredentials->client_id);
                $this->configWriter->save('WweLtConnSettings/first/wweltlClientSecret', $quote->newAPICredentials->client_secret);
                $this->configWriter->save('WweLtConnSettings/first/wweltlApiEndpoint', 'new');
                $username = $this->getConfigData('WweLtConnSettings/first/WweLtUsername');
                $password = $this->getConfigData('WweLtConnSettings/first/WweLtPassword');
                $this->configWriter->save('WweLtConnSettings/first/wweLtUsernameNewAPI', $username);
                $this->configWriter->save('WweLtConnSettings/first/wweLtPasswordNewAPI', $password);
                unset($quotes[$key]->newAPICredentials);
                $this->clearCache();
            }

            if(isset($quote->oldAPICredentials)){
                $this->configWriter->save('WweLtConnSettings/first/wweltlApiEndpoint', 'legacy');
                unset($quotes[$key]->oldAPICredentials);
                $this->clearCache();
            }
        }

        return $quotes;
    }
}
