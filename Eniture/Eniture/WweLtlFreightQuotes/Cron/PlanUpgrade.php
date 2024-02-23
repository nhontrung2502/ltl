<?php

namespace Eniture\WweLtlFreightQuotes\Cron;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class PlanUpgrade
{
    /**
     * @var String URL
     */
    private $curlUrl = 'https://ws011.eniture.com/web-hooks/subscription-plans/create-plugin-webhook.php';
    /**
     * @var Logger Object
     */
    protected $logger;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var ConfigInterface
     */
    private $resourceConfig;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Curl $curl
     * @param ConfigInterface $resourceConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Curl $curl,
        ConfigInterface $resourceConfig,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->curl = $curl;
        $this->resourceConfig = $resourceConfig;
        $this->scopeConfig      = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * upgrade plan information
     */
    public function execute()
    {
        $domain = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $licenseKey = $this->scopeConfig->getValue(
            'WweLtConnSettings/first/WweLtLicenseKey',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $webhookUrl = $domain . 'wweltlfreightquotes';
        $postData = http_build_query([
            'platform' => 'magento2',
            'carrier' => '11',
            'store_url' => $domain,
            'webhook_url' => $webhookUrl,
            'license_key'   => ($licenseKey) ?? '',
        ]);
        
        $this->curl->post($this->curlUrl, $postData);
        $output = $this->curl->getBody();
        $result = json_decode($output, true);

        $plan = isset($result['pakg_group']) ? $result['pakg_group'] : '';
        $expireDay = isset($result['pakg_duration']) ? $result['pakg_duration'] : '';
        $expiryDate = isset($result['expiry_date']) ? $result['expiry_date'] : '';
        $planType = isset($result['plan_type']) ? $result['plan_type'] : '';
        $pakgPrice = isset($result['pakg_price']) ? $result['pakg_price'] : 0;
        if ($pakgPrice == 0) {
            $plan = 0;
        }

        $today = date('F d, Y');
        if (strtotime($today) > strtotime($expiryDate)) {
            $plan = '-1';
        }

        $this->saveConfigurations('eniture/ENWweLTL/plan', "$plan");
        $this->saveConfigurations('eniture/ENWweLTL/expireday', "$expireDay");
        $this->saveConfigurations('eniture/ENWweLTL/expiredate', "$expiryDate");
        $this->saveConfigurations('eniture/ENWweLTL/storetype', "$planType");
        $this->saveConfigurations('eniture/ENWweLTL/pakgprice', "$pakgPrice");
        $this->saveConfigurations('eniture/ENWweLTL/label', "Eniture - Worldwide Express LTL Freight Quotes");

        $this->logger->info($output);
    }

    /**
     * @param type $path
     * @param type $value
     */
    public function saveConfigurations($path, $value)
    {
        $this->resourceConfig->saveConfig(
            $path,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }
}
