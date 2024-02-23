<?php

namespace Eniture\WweLtlFreightQuotes\Model\Carrier;

use Eniture\WweLtlFreightQuotes\Helper\Data;
use Eniture\WweLtlFreightQuotes\Helper\EnConstants;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * category   Shipping
 * package    Eniture_WweLtlFreightQuotes
 * author     Eniture Technologies
 * website    https://eniture.com
 * license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WweLTLShipping extends AbstractCarrier implements
    CarrierInterface
{
    public $_code = 'ENWweLTL';

    private $isFixed = true;

    private $rateResultFactory;

    private $rateMethodFactory;

    private $scopeConfig;

    private $dataHelper;

    private $registry;

    private $moduleManager;

    private $session;

    private $productLoader;

    private $objectManager;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var UrlInterface
     */
    private $urlInterface;
    /**
     * @var Cart
     */
    private $cart;
    /**
     * @var WweLTLGenerateRequestData
     */
    private $generateReqData;
    /**
     * @var WweLTLManageAllQuotes
     */
    private $manageAllQuotes;
    /**
     * @var WweLTLShipmentPackage
     */
    private $shipmentPkg;
    /**
     * @var WweLTLAdminConfiguration
     */
    private $adminConfig;
    /**
     * @var WweLTLSetCarriersGlobally
     */
    private $setGlobalCarrier;
    /**
     * @var isHazmat
     */
    private $isHazmat = 'N';
    /**
     * @var isInsure
     */
    private $isInsure = false;

    /**
     * @var qty
     */
    public $qty = 0;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param Cart $cart
     * @param Data $dataHelper
     * @param Registry $registry
     * @param Manager $moduleManager
     * @param UrlInterface $urlInterface
     * @param SessionManagerInterface $session
     * @param ProductFactory $productLoader
     * @param ObjectManagerInterface $objectManager
     * @param WweLTLGenerateRequestData $generateReqData
     * @param WweLTLManageAllQuotes $manAllQuotes
     * @param WweLTLShipmentPackage $shipmentPkg
     * @param WweLTLAdminConfiguration $adminConfig
     * @param WweLTLSetCarriersGlobally $setGlobalCarrier
     * @param RequestInterface $httpRequest
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Cart $cart,
        Data $dataHelper,
        Registry $registry,
        Manager $moduleManager,
        UrlInterface $urlInterface,
        SessionManagerInterface $session,
        ProductFactory $productLoader,
        ObjectManagerInterface $objectManager,
        WweLTLGenerateRequestData $generateReqData,
        WweLTLManageAllQuotes $manAllQuotes,
        WweLTLShipmentPackage $shipmentPkg,
        WweLTLAdminConfiguration $adminConfig,
        WweLTLSetCarriersGlobally $setGlobalCarrier,
        RequestInterface $httpRequest,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->scopeConfig = $scopeConfig;
        $this->cart = $cart;
        $this->dataHelper = $dataHelper;
        $this->registry = $registry;
        $this->moduleManager = $moduleManager;
        $this->urlInterface = $urlInterface;
        $this->session = $session;
        $this->productLoader = $productLoader;
        $this->objectManager = $objectManager;
        $this->generateReqData = $generateReqData;
        $this->manageAllQuotes = $manAllQuotes;
        $this->shipmentPkg = $shipmentPkg;
        $this->adminConfig = $adminConfig;
        $this->setGlobalCarrier = $setGlobalCarrier;
        $this->request = $httpRequest;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->initClasses();
    }

    /**
     *
     */
    public function initClasses()
    {
        $this->adminConfig->_init($this->scopeConfig, $this->registry);

        $this->generateReqData->_init($this->scopeConfig, $this->registry, $this->moduleManager, $this->request, $this->dataHelper);

        $this->manageAllQuotes->_init($this->scopeConfig, $this->registry, $this->session, $this->objectManager);

        $this->shipmentPkg->_init($this->scopeConfig, $this->dataHelper, $this->productLoader, $this->request);

        $this->setGlobalCarrier->_init($this->dataHelper);
    }

    /**
     * @param RateRequest $request
     * @return boolean|object
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        if (empty($request->getDestPostcode()) || empty($request->getDestCountryId()) ||
            empty($request->getDestCity()) || empty($request->getDestRegionId())) {
            return false;
        }

        /// Commented out due to GT-199 issue.
        /// The quotes from session don't include the selected carier if customers switch to another address on checkout page
        /// causing issue "Carrier with such method not found"

        // $getQuotesFromSession = $this->quotesFromSession();
        // if (null !== $getQuotesFromSession) {
        //     return $getQuotesFromSession;
        // }

        $ItemsList = $request->getAllItems();
        $receiverZipCode = $request->getDestPostcode();
        $planInfo = $this->dataHelper->planInfo();
        $package = $this->getShipmentPackageRequest($ItemsList, $receiverZipCode, $request, $planInfo);
        $wweLtlArr = $this->generateReqData->generateEnitureArray();

        if ($planInfo['planNumber'] >= 2 && $this->isHazmat == 'Y') {
            $wweLtlArr['api']['lineItemHazmatInfo'] = [
                [
                    'isHazmatLineItem' => 'Y',
                    'lineItemHazmatUNNumberHeader' => 'UN #',
                    'lineItemHazmatUNNumber' => 'UN 1139',
                    'lineItemHazmatClass' => '1.1',
                    'lineItemHazmatEmContactPhone' => '4043308699',
                    'lineItemHazmatPackagingGroup' => 'I',
                ],
            ];
        }

        if ($planInfo['planNumber'] >= 2 && $this->isInsure) {
            $wweLtlArr['api']['insureShipment'] = '1';
            $wweLtlArr['api']['insuranceCategory'] = $this->generateReqData->getInsuranceCategory();
        }

        $wweLtlArr['originAddress'] = $package['origin'];
        $resp = $this->setGlobalCarrier->manageCarriersGlobally($wweLtlArr, $this->registry);
        if (!$resp) {
            return false;
        }

        $requestArr = $this->generateReqData->generateRequestArray($request, $wweLtlArr, $package['items'], $this->cart);
        if (empty($requestArr)) {
            return false;
        }
        $url = EnConstants::QUOTES_URL;
        $quotes = $this->dataHelper->sendCurlRequest($url, $requestArr);

        $quotesResult = $this->manageAllQuotes->getQuotesResultArr($quotes);
        $this->session->setEnShippingQuotes($quotesResult);

        return (!empty($quotesResult)) ? $this->setCarrierRates($quotesResult) : '';
    }

    /**
     * @return object | null
     */
    public function quotesFromSession()
    {
        $currentAction = $this->urlInterface->getCurrentUrl();
        $currentAction = strtolower($currentAction);

        if (strpos($currentAction, 'shipping-information') !== false || strpos($currentAction, 'payment-information') !== false) {
            $availableSessionQuotes = $this->session->getEnShippingQuotes(); // FROM SESSION
            $availableQuotes = (!empty($availableSessionQuotes)) ? $this->setCarrierRates($availableSessionQuotes) : null;
        } else {
            $availableQuotes = null;
        }
        return $availableQuotes;
    }

    /**
     * This function returns package array
     * @param $items
     * @param $receiverZipCode
     * @param $request
     * @return array
     */
    public function getShipmentPackageRequest($items, $receiverZipCode, $request, $planInfo)
    {
        $package = [];
        foreach ($items as $key => $item) {
            $_product = $this->productLoader->create()->load($item->getProductId());
            $productType = $item->getRealProductType() ?? $_product->getTypeId();

            if ($productType == 'configurable') {
                $this->qty = $item->getQty();
            }
            if ($productType == 'simple') {
                $productQty = ($this->qty > 0) ? $this->qty : $item->getQty();
                $this->qty = 0;

                $originAddress = $this->shipmentPkg->wweLTLOriginAddress($request, $_product, $receiverZipCode);

                $package['origin'][$_product->getId()] = $originAddress;

                $orderWidget[$originAddress['senderZip']]['origin'] = $originAddress;

                $weight = $_product->getWeight();
                if($key==0)
                {
                    $palletWeight = $this->scopeConfig->getValue('WweLtQuoteSetting/third/palletWeight', ScopeInterface::SCOPE_STORE);
                    if($palletWeight>0)
                        $weight += $palletWeight/$productQty;
                }
                $length = $this->getDims($_product, 'length') ? $this->getDims($_product, 'length') : 0.0;
                $width = $this->getDims($_product, 'width') ? $this->getDims($_product, 'width') : 0.0;
                $height = $this->getDims($_product, 'height') ? $this->getDims($_product, 'height') : 0.0;

                $setHzAndIns = $this->setHzAndIns($_product, $planInfo);
                $lineItemClass = $this->getLineItemClass($_product);
                $lineItem = [
                    'lineItemClass' => $lineItemClass,
                    'freightClass' => $this->isLTL($_product),
                    'lineItemId' => $_product->getId(),
                    'lineItemName' => $_product->getName(),
                    'piecesOfLineItem' => $productQty,
                    'lineItemPrice' => $_product->getPrice(),
                    'lineItemWeight' => number_format($weight, 2, '.', ''),
                    'lineItemLength' => number_format($length, 2, '.', ''),
                    'lineItemWidth' => number_format($width, 2, '.', ''),
                    'lineItemHeight' => number_format($height, 2, '.', ''),
                    'isHazmatLineItem' => $setHzAndIns['hazmat'],
                    'product_insurance_active' => ($setHzAndIns['insurance'])?'1':'0',
                    'shipBinAlone' => $_product->getData('en_own_package'),
                    'vertical_rotation' => $_product->getData('en_vertical_rotation'),
                ];

                $package['items'][$_product->getId()] = $lineItem;
                $orderWidget[$originAddress['senderZip']]['item'][] = $package['items'][$_product->getId()];
            }
        }

        if (isset($orderWidget) && !empty($orderWidget)) {
            foreach ($orderWidget as $data) {
                $uniqueOrigins [] = $data['origin'];
            }
            $this->setDataInRegistry($uniqueOrigins, $orderWidget);
        }

        return $package;
    }

    /**
     * @param $origin
     * @param $orderWidget
     */
    public function setDataInRegistry($origin, $orderWidget)
    {
        // set order detail widget data
        if ($this->registry->registry('setPackageDataForOrderDetail') === null) {
            $this->registry->register('setPackageDataForOrderDetail', $orderWidget);
        }

        // set shipment origin globally for in-store pickup and local delivery
        if ($this->registry->registry('shipmentOrigin') === null) {
            $this->registry->register('shipmentOrigin', $origin);
        }
    }

    /**
     * @param $_product
     * @return string
     */
    private function isLTL($_product)
    {
        $path = 'WweLtQuoteSetting/third/weightExeeds';
        $weightConfigExceedOpt = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);

        if ($this->registry->registry('weightConfigExceedOpt') === null) {
            $this->registry->register('weightConfigExceedOpt', $weightConfigExceedOpt);
        }

        $isEnableLtl = $_product->getData('en_ltl_check');
        if(is_null($isEnableLtl)) $isEnableLtl = 1;
        if (($isEnableLtl) || ($_product->getWeight() > 150 && $weightConfigExceedOpt)) {
            $freightClass = 'ltl';
        } else {
            $freightClass = '';
        }

        return $freightClass;
    }

    /**
     * @param $_product
     * @return float|int|string
     */
    private function getLineItemClass($_product)
    {
        $lineItemClass = $_product->getData('en_freight_class');
        $lineItemClass = (isset($lineItemClass)&$lineItemClass>0)?$lineItemClass:70; //add default value
        switch ($lineItemClass) {
            case 77:
                $lineItemClass = 77.5;
                break;
            case 92:
                $lineItemClass = 92.5;
                break;
            case 1:
                $lineItemClass = 'DensityBased';
                break;
            default:
                break;
        }
        return $lineItemClass;
    }

    /**
     * @param $_product
     * @param $dimOf
     * @return float
     */
    private function getDims($_product, $dimOf)
    {
        $dimValue = $_product->getData('ts_dimensions_'.$dimOf);
        if ($dimValue != null) {
            return $dimValue;
        }

        return $_product->getData('en_'.$dimOf);
    }

    /**
     * @param object $_product
     * @return array
     */
    private function setHzAndIns($_product, $planInfo)
    {
        if ($planInfo['planNumber'] >= 2 && $_product->getData('en_hazmat')) {
            $hazmat = 'Y';
        } else {
            $hazmat = 'N';
        }

        if ($planInfo['planNumber'] >= 2 && $_product->getData('en_insurance')) {
            $insurance = true;
        } else {
            $insurance = false;
        }

        if ($hazmat == 'Y' && $this->isHazmat != 'Y') {
            $this->isHazmat = 'Y';
        }

        if ($insurance && !$this->isInsure) {
            $this->isInsure = true;
        }

        return ['hazmat' => $hazmat,
            'insurance' => $insurance
        ];
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @param array $quotes
     * @return Object
     */
    public function setCarrierRates($quotes)
    {
        if (empty($quotes)) {
            //To show error
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            return $error;
        } else {
            $carriersArray = $this->registry->registry('enitureCarrierCodes');
            $carriersTitle = $this->registry->registry('enitureCarrierTitle');
            $result = $this->rateResultFactory->create();

            foreach ($quotes as $carrierKey => $quote) {
                foreach ($quote as $key => $carrier) {
                    if (isset($carrier['code'])) {
                        $carrierCode = $carriersArray[$carrierKey] ?? $this->_code;
                        $carrierTitle = $carriersTitle[$carrierKey] ?? $this->getConfigData('title');
                        $method = $this->rateMethodFactory->create();
                        $method->setCarrier($carrierCode);
                        $method->setCarrierTitle($carrierTitle);
                        $method->setMethod($carrier['code']);
                        $method->setMethodTitle($carrier['title']);
                        $method->setPrice($carrier['rate']);
                        $result->append($method);
                    }
                }
            }
            return $result;
        }
    }
}
