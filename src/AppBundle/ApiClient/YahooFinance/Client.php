<?php

namespace AppBundle\ApiClient\YahooFinance;

use Scheb\YahooFinanceApi\ApiClient;
use Scheb\YahooFinanceApi\Exception\ApiException;
use AppBundle\ApiClient\FinanceClientInterface;

class Client implements FinanceClientInterface
{
    private $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }
    
    /**
     * @param string $symbolName
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function get($symbolName, $startDate, $endDate)
    {
        $startDate          = new \DateTime($startDate);
        $endDate            = new \DateTime($endDate);
        $historicalData     = null;
        
        try {
            $historicalData = $this->apiClient->getHistoricalData($symbolName, $startDate, $endDate);
            $historicalData = $historicalData['query']['results']['quote'];

            if (!isset($historicalData[0])) {
                $historicalData = array($historicalData);
            } else {
                $historicalData = array_reverse($historicalData);
            }
            
        } catch (ApiException $exception) {
        }
        
        return $historicalData;
    }
}