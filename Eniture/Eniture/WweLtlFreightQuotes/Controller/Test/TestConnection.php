<?php

namespace Eniture\WweLtlFreightQuotes\Controller\Test;

use Eniture\WweLtlFreightQuotes\Helper\Data;
use Eniture\WweLtlFreightQuotes\Helper\EnConstants;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class TestConnection extends Action
{
    /**
     * @var Helper Object
     */
    private $dataHelper;

    /**
     * @param Context $context
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * Test Connection Credentials
     */
    public function execute()
    {
        $credentials = $this->getRequest()->getParams();
        $postData = [
            'carrierName' => 'wweLTL',
            'carrier_mode' => 'test',
            'platform' => 'magento2',
            'world_wide_express_account_number' => $credentials['accountNumber'],
            'speed_freight_username' => $credentials['username'],
            'speed_freight_password' => $credentials['password'],
            'authentication_key' => $credentials['authenticationKey'],
            'plugin_licence_key' => $credentials['pluginLicenceKey'],
            'plugin_domain_name' => $this->getStoreUrl(),
        ];
        $response = $this->dataHelper->sendCurlRequest(EnConstants::TEST_CONN_URL, $postData);
        $result = $this->testConnectionResponse($response);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($result);
    }

    /**
     * @param $data
     * @return false|string
     */
    public function testConnectionResponse($data)
    {
        $response = [];
        $successMsg = 'The test resulted in a successful connection.';
        $errorMsg = 'The credentials entered did not result in a successful test. Confirm your credentials and try again.';
        if (isset($data)) {
            if (isset($data->error)) {
                $response = $this->dataHelper->generateResponse($data->error_desc, true);
                if (isset($data->error->Code)) {
                    $response = $this->dataHelper->generateResponse($data->error->Description, true);
                }
            } elseif (isset($data->success)) {
                $response['msg'] = $successMsg;
            } elseif ($data->status == 'Error') {
                $response = $this->dataHelper->generateResponse($data->error_desc, true);
            }
        } else {
            $response = $this->dataHelper->generateResponse();
        }
        return json_encode($response);
    }

    /**
     * This function returns the Current Store Url
     * @return string
     */
    public function getStoreUrl()
    {
        // It will be written to return Current Store Url in multi-store view
        return $this->getRequest()->getServer('SERVER_NAME');
    }
}
