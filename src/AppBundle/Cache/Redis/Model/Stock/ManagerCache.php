<?php

namespace AppBundle\Cache\Redis\Model\Stock;

use AppBundle\Entity\Portfolio;
use AppBundle\Entity\Symbol;
use AppBundle\Cache\CacheInterface;
use AppBundle\Util\Date\DateUtilInterface;
use AppBundle\Model\Stock\Manager;

class ManagerCache
{
    private $manager;
    private $cache;
    private $dateUtil;

    /**
     * @param Manager           $manager
     * @param CacheInterface    $cache
     * @param DateUtilInterface $dateUtil
     */
    public function __construct(Manager $manager, CacheInterface $cache, DateUtilInterface $dateUtil)
    {
        $this->manager  = $manager;
        $this->cache    = $cache;
        $this->dateUtil = $dateUtil;
    }
    
    /**
     * @param Portfolio $portfolio
     * @param string    $startDate
     * @param string    $endDate
     * @return mixed
     */
    public function getStocksSum(Portfolio $portfolio, $startDate, $endDate)
    {
        $cacheKey = 'stock-sum:'.$portfolio->getId().':'.$startDate.':'.$endDate;

        $cacheData = $this->cache->get($cacheKey);

        if ($cacheData) {
            return $cacheData;
        }

        $stockSum = $this->__call(__FUNCTION__, func_get_args());

        $this->cache->set($cacheKey, $stockSum);
        
        return $stockSum;
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

            $cacheKey = 'load-data:'.$symbolId.':'.$startDate.':'.$endDate;

            $cacheData = $this->cache->get($cacheKey);

            if (!is_null($cacheData)) {
                $stockData[$symbolId][] = $cacheData;

                continue;
            }

            $this->cache->set($cacheKey, false);

            $financeData = $this->__call(__FUNCTION__, array ($symbol, array ($dateRange)));
            
            if (!is_null($financeData)) {
                array_shift($financeData);
                $stockData[$symbolId][] = $financeData;
                $this->cache->set($cacheKey, $financeData);
            }
        }

        return $stockData;
    }
    
    /**
     * @param Symbol $symbol
     * @return boolean
     */
    public function actualizeStockData(Symbol $symbol)
    {
        $cacheKey       = 'actualize:'.$symbol->getId();
        $actualizeDate  = $this->cache->get($cacheKey);
        $now            = $this->dateUtil->getToday();

        if (!is_null($actualizeDate) && $actualizeDate == $now) {
            return true;
        }
        
        $this->cache->set($cacheKey, $now);
        
        $this->__call(__FUNCTION__, func_get_args());
    }
    
    public function __call($method, $arguments)
    {
        return call_user_func_array(array ($this->manager, $method), $arguments);
    }
}