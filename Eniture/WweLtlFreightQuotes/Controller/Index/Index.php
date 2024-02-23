<?php

namespace Eniture\WweLtlFreightQuotes\Controller\Index;

use Eniture\WweLtlFreightQuotes\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;

class Index extends Action
{
    /**
     * @var Object
     */
    private $request;
    /**
     * @var Resource Config Object
     */
    private $resourceConfig;
    /**
     * @var Helper Object
     */
    private $helper;

    /**
     * Index constructor.
     * @param Context $context
     * @param ConfigInterface $resourceConfig
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        ConfigInterface $resourceConfig,
        Data $helper
    ) {
        $this->request = $context->getRequest();
        $this->resourceConfig = $resourceConfig;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Upgrade Plan information in Database
     */
    public function execute()
    {
        $params = $this->request->getParams();

        if (!empty($params)) {
            $plan = isset($params['pakg_group']) ? $params['pakg_group'] : '';
            $expireDay = isset($params['pakg_duration']) ? $params['pakg_duration'] : '';
            $expiryDate = isset($params['expiry_date']) ? $params['expiry_date'] : '';
            $planType = isset($params['plan_type']) ? $params['plan_type'] : '';
            $pkgPrice = isset($params['pakg_price']) ? $params['pakg_price'] : '0';
            if ($pkgPrice == '0') {
                $plan = '0';
            }
            $today = date('F d, Y');
            if (strtotime($today) > strtotime($expiryDate)) {
                $plan = '-1';
            }
            $this->saveConfigurations('plan', $plan);
            $this->saveConfigurations('expireday', $expireDay);
            $this->saveConfigurations('expiredate', $expiryDate);
            $this->saveConfigurations('storetype', $planType);
            $this->saveConfigurations('pakgprice', $pkgPrice);
            $this->helper->unsetPlanSession();
            $this->helper->clearCache();
            print_r('Done');
        }
    }

    /**
     * @param $path
     * @param $value
     */
    public function saveConfigurations($path, $value)
    {
        $this->resourceConfig->saveConfig(
            'eniture/ENWweLTL/' . $path,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }
}
