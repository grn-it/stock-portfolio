<?php

namespace AppBundle\Cache\Redis\Model\Stock;

use AppBundle\Entity\Portfolio;
use AppBundle\Entity\Symbol;
use AppBundle\Model\Stock\Manager;

class ManagerCache extends Manager
{
    /**
     * @param Portfolio $portfolio
     * @param string    $startDate
     * @param string    $endDate
     * @return mixed
     */
    public function getStocksSum(Portfolio $portfolio, $startDate = null, $endDate = null)
    {
        $startDate  = $this->dateUtil->getDefaultStockStartDateIfNull($startDate);
        $endDate    = $this->dateUtil->getToday();
        $cacheKey   = 'stock-sum:'.$portfolio->getId().':'.$startDate.':'.$endDate;
        $cacheData  = $this->cache->get($cacheKey);

        if ($cacheData) {
            return $cacheData;
        }

        $stockSum = parent::getStocksSum($portfolio, $startDate, $endDate);

        $this->cache->set($cacheKey, $stockSum);
        
        return $stockSum;
    }
    
    /**
     * @param array $allStockData
     * @param array $newStockData
     */
    public function appendStockData(&$allStockData, $newStockData)
    {
        if (!isset($allStockData)) {
            $allStockData = $newStockData;
        } else {
            $allStockData = array_merge($allStockData, $newStockData);
        }
    }
    
    /**
     * @param string    $cacheKey
     * @param array     $stockData
     */
    public function appendStockDataIfCacheExist($cacheKey, &$stockData)
    {
        $cacheData = $this->cache->get($cacheKey);
                
        if ($cacheData !== false) {
            $this->appendStockData($stockData, $cacheData);
        }
    }
    
    /**
     * @param array $financeData
     * @param array $stockData
     * @param string $cacheKey
     */
    public function appendStockDataIfDataExist($financeData, &$stockData, $cacheKey)
    {
        $cacheValue = false;
        
        if (!empty($financeData)) {
            $financeData    = array_shift($financeData);
            $cacheValue     = $financeData;

            $this->appendStockData($stockData, $financeData);
        }
        
        $this->cache->set($cacheKey, $financeData);
    }
    
    /**
     * @param Symbol    $symbol
     * @param array     $dateRanges
     * @return array
     */
    public function getStockData(Symbol $symbol, $dateRanges = array())
    {
        $stockData  = array();
        $symbolId   = $symbol->getId();

        foreach ($dateRanges as $dateRange) {
            $startDate  = $dateRange['startDate'];
            $endDate    = $dateRange['endDate'];
            $cacheKey   = 'load-data:'.$symbolId.':'.$startDate.':'.$endDate;

            if ($this->cache->exists($cacheKey)) {
                $this->appendStockDataIfCacheExist($cacheKey, $stockData[$symbolId]);
            } else {
                $financeData = parent::getStockData($symbol, array ($dateRange));
                $this->appendStockDataIfDataExist($financeData, $stockData[$symbolId], $cacheKey);
            }
        }

        return $stockData;
    }
    
    /**
     * @param Symbol $symbol
     * @return boolean
     */
    protected function actualizeStockData(Symbol $symbol)
    {
        $cacheKey       = 'actualize:'.$symbol->getId();
        $actualizeDate  = $this->cache->get($cacheKey);
        $now            = $this->dateUtil->getToday();

        if (!is_null($actualizeDate) && $actualizeDate == $now) {
            return true;
        }
        
        $this->cache->set($cacheKey, $now);
        
        parent::actualizeStockData($symbol);
    }
}