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
            'plugin_licence_key' => $credentials['pluginLicenceKey'],
            'plugin_domain_name' => $this->getStoreUrl(),
        ];

        if(isset($credentials['apiEndpoint']) && $credentials['apiEndpoint'] == 'new'){
            $postData['ApiVersion'] = '2.0';
            $postData['clientId'] = $credentials['clientId'];
            $postData['clientSecret'] = $credentials['clientSecret'];
        }else{
            $postData['authentication_key'] = $credentials['authenticationKey'];
        }

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
        if (isset($data) && !empty($data)) {
            if(isset($data->severity)){
                if($data->severity == 'SUCCESS'){
                    $response['msg'] = $successMsg;
                }else{
                    $response = $this->dataHelper->generateResponse($data->Message, true);
                }
            }elseif (isset($data->error)) {
                $response = $this->dataHelper->generateResponse($data->error_desc, true);
                if (isset($data->error->Code)) {
                    $response = $this->dataHelper->generateResponse($data->error->Description, true);
                }
            } elseif (isset($data->success)) {
                $response['msg'] = $successMsg;
            } elseif ($data->status == 'Error') {
                $response = $this->dataHelper->generateResponse($data->error_desc, true);
            }else{
                $response = $this->dataHelper->generateResponse('An empty or unknown response format, therefore we are unable to determine whether it was successful or an error', true);
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
