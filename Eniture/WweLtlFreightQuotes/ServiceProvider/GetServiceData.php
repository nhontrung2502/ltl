<?php


namespace Eniture\WweLtlFreightQuotes\ServiceProvider;

use Magento\Checkout\Model\Session;

class GetServiceData
{
    public $data;
    public function __construct(Session $session)
    {
        $this->data = $session;
    }

    public function getData()
    {
        return $this->data->getData('current_product');
    }
}
