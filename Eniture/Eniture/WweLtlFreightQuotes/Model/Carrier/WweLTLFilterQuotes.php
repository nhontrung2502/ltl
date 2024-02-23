<?php

namespace Eniture\WweLtlFreightQuotes\Model\Carrier;

class WweLTLFilterQuotes
{
    /**
     * rating method from quote settings
     * @var string type
     */
    public $ratingMethod;

    /**
     * rates from web service
     * @var array type
     */
    public $quotes;

    /**
     * wwe settings
     * @var array type
     */
    public $quoteSettings;

    /**
     * label from quote settings
     * @var string type
     */
    public $wweLabel;

    /**
     * @var int type
     */
    public $totalCarriers;
    

    /**
     * set values in class attributes and return quotes
     * @param array $quotes
     * @param $carrierNumbers
     * @param $ratingMethod
     * @return array
     */
    public function calculateQuotes($quotes, $carrierNumbers, $ratingMethod)
    {
        $this->quotes         = $quotes;
        $this->totalCarriers = $carrierNumbers;
        $ratingMethod = $ratingMethod;
        $ratingMethod = $this->setRatingMethod($ratingMethod);
        return $this->$ratingMethod();
    }

    /**
     * @param $ratingMethod
     * @return string
     */
    public function setRatingMethod($ratingMethod)
    {
        $method = '';
        switch ($ratingMethod) {
            case 1:
                $method = 'cheapest';
                break;
            case 2:
                $method = 'cheapestOptions';
                break;
            case 3:
                $method = 'averageRate';
                break;
        }
        return $method;
    }
    /**
     * @return Hash
     */
    public function randString()
    {
        return rand(10, 100);
    }

    /**
     * calculate average for quotes
     * @return array
     */
    public function averageRate()
    {
        $this->quotes = (isset($this->quotes) && (is_array($this->quotes))) ?
            array_slice($this->quotes, 0, $this->totalCarriers) : [];
        $rateList = $this->enArrayColumn($this->quotes, 'cost');
        $rateSum = array_sum($rateList) / count($this->quotes);
        $quotesReset = reset($this->quotes);
        $rate[] = [
            'id'            => $this->randString(),
            'cost'          => $rateSum,
            'labelSufex'    => (isset($quotesReset['labelSufex'])) ? $quotesReset['labelSufex'] : [],
        ];

        return $rate;
    }

    /**
     * calculate cheapest rate
     * @return array
     */
    public function cheapest()
    {
        return (isset($this->quotes) && (is_array($this->quotes))) ? array_slice($this->quotes, 0, 1) : [];
    }

    /**
     * calculate cheapest rate numbers
     * @return array
     */
    public function cheapestOptions()
    {
        return (isset($this->quotes) && (is_array($this->quotes))) ? array_slice($this->quotes, 0, $this->totalCarriers) : [];
    }

    /**
     * @param $data
     * @param $key
     * @return array
     */
    public function enArrayColumn($data, $key)
    {
        $phpVersion = PHP_VERSION;
        $oldVersion = $phpVersion <= 5.4;
        $columns = (!$oldVersion && function_exists("array_column")) ? array_column($data, $key) : [];
        $arrLength = count($data);
        if (empty($arrLength) || !$oldVersion) {
            return $columns;
        }
        $indexArr = array_fill(0, $arrLength, $key);
        $columns = array_map(function ($data, $index) {
            return is_object($data) ? $data->$index : $data[$index];
        }, $data, $indexArr);
        return $columns;
    }
}
