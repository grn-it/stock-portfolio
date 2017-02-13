<?php

namespace AppBundle\Model\Stock;

use AppBundle\Entity\Portfolio;
use AppBundle\Entity\Symbol;
use AppBundle\ApiClient\FinanceClientInterface;
use AppBundle\Util\Date\DateUtilInterface;
use AppBundle\Builder\Entity\StockBuilder;
use AppBundle\Repository\StockRepository;
use AppBundle\Repository\SymbolRepository;

/**
 * Сервис управления портфелями акций
 */
class Manager
{
    private $stockRepository;
    private $symbolRepository;
    private $financeClient;
    private $dateUtil;
    private $lastClose;

    /**
     * @param StockRepository           $stockRepository
     * @param SymbolRepository          $symbolRepository
     * @param FinanceClientInterface    $financeClient
     * @param DateUtilInterface         $dateUtil
     */
    public function __construct(StockRepository $stockRepository, SymbolRepository $symbolRepository, FinanceClientInterface $financeClient, DateUtilInterface $dateUtil)
    { 
        $this->stockRepository  = $stockRepository;
        $this->symbolRepository = $symbolRepository;
        $this->financeClient    = $financeClient;
        $this->dateUtil         = $dateUtil;
    }
    
    /**
     * @param Portfolio $portfolio
     * @return array
     */
    public function getLast(Portfolio $portfolio)
    {
        $this->checkAndRefreshStockData($portfolio);

        $last = $this->stockRepository->getLast($portfolio);

        return $last;
    }

    /**
     * @param Portfolio $portfolio
     * @param string    $startDate
     * @param string    $endDate
     * @return array
     */
    public function getStocksSum(Portfolio $portfolio, $startDate = null, $endDate = null)
    {
        $startDate  = $this->dateUtil->getDefaultStockStartDateIfNull($startDate);
        $endDate    = $this->dateUtil->getToday();
        
        $this->checkAndRefreshStockData($portfolio, $startDate, $endDate);
        
        $stocksSum = $this->stockRepository->getStocksSum($portfolio);

        return $stocksSum;
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
        $symbolName = $symbol->getName();

        foreach ($dateRanges as $dateRange) {
            $startDate  = $dateRange['startDate'];
            $endDate    = $dateRange['endDate'];

            $financeData = $this->financeClient->get($symbolName, $startDate, $endDate);
            
            if (!is_null($financeData)) {
                $stockData[$symbolId][] = $financeData;
            }
        }

        return $stockData;
    }

    /**
     * @param array $stockData
     */
    public function saveStockData($stockData = array())
    {
        foreach ($stockData as $symbolId => $stockDataByDateRanges) {
            $symbol = $this->symbolRepository->find($symbolId);

            foreach ($stockDataByDateRanges as $stockDataByDateRange) {
                $this->saveStockDataByDateRange($symbol, $stockDataByDateRange);
            }
        }
    }

    /**
     * @param string $close
     * @return string
     */
    public function calculateChange($close)
    {
        $change = null;

        if (!is_null($this->lastClose)) {
            $change = $close - $this->lastClose;
        } else {
            $this->lastClose = $close;
        }

        return $change;
    }
    
    /**
     * @param Symbol    $symbol
     * @param array     $stockDataByDateRange
     */
    public function saveStockDataByDateRange($symbol, $stockDataByDateRange)
    {
        foreach ($stockDataByDateRange as $stockDataItem) {
            $change = $this->calculateChange($stockDataItem['Close']);
            
            if (is_null($change)) {
                continue;
            }
            
            $stockBuilder = new StockBuilder();

            $stockBuilder->setSymbolid($symbol);
            $stockBuilder->setDate(new \DateTime($stockDataItem['Date']));
            $stockBuilder->setOpen($stockDataItem['Open']);
            $stockBuilder->setHigh($stockDataItem['High']);
            $stockBuilder->setLow($stockDataItem['Low']);
            $stockBuilder->setClose($stockDataItem['Close']);
            $stockBuilder->setChange($change);
            $stockBuilder->setVolume($stockDataItem['Volume']);

            $stock = $stockBuilder->build();

            $this->stockRepository->persist($stock);
        }
        
        $this->stockRepository->flush();
    }
    
    /**
     * @param integer $symbolId
     * @return boolean
     */
    public function isStockDataExist($symbolId)
    {
        if ($this->stockRepository->isStockDataExist($symbolId)) {
            return true;
        }

        return false;
    }

    /**
     * @param Symbol $symbol
     * @param string $startDate
     * @param string $endDate
     */
    private function checkStockDataExist($symbol, $startDate = null, $endDate = null)
    {
        $dateRanges     = $this->dateUtil->getDateRanges($startDate, $endDate);
        $stockMinDate   = $this->stockRepository->getStockMinDate($symbol);

        if (!$stockMinDate) {
            $stockData = $this->getStockData($symbol, $dateRanges);
            
            $this->saveStockData($stockData);
            $this->actualizeStockData($symbol);
            
        } elseif ($this->dateUtil->isPastDataMissing($startDate, $stockMinDate)) {
            $stockMinDate           = $this->dateUtil->getYesterday($stockMinDate);
            $dateRangeUntilMinDate  = $this->dateUtil->getDateRanges($startDate, $stockMinDate);
            $stockData              = $this->getStockData($symbol, $dateRangeUntilMinDate);

            $this->saveStockData($stockData);
        }
    }

    /**
     * @param Symbol $symbol
     */
    private function actualizeStockData($symbol)
    {
        $stockMaxDate   = $this->stockRepository->getStockMaxDate($symbol);
        
        if (!$stockMaxDate) {
            return true;
        }
        
        $startDate      = $this->dateUtil->getTomorrow($stockMaxDate);
        $endDate        = $this->dateUtil->getYesterday();
        $dateRanges     = $this->dateUtil->getDateRanges($startDate, $endDate);
        $stockData      = $this->getStockData($symbol, $dateRanges);

        $this->saveStockData($stockData);
    }
    
    /**
     * @param Portfolio $portfolio
     * @param string    $startDate
     * @param string    $endDate
     */
    private function checkAndRefreshStockData(Portfolio $portfolio, $startDate = null, $endDate = null)
    {
        if (is_null($startDate)) {
            $startDate = $this->dateUtil->getLastWeek();
        }
        
        if (is_null($endDate)) {
            $endDate = $this->dateUtil->getYesterday();
        }
        
        $portfolio->getSymbols()->map(function($symbol) use ($startDate, $endDate)  {
            $this->actualizeStockData($symbol);
            $this->checkStockDataExist($symbol, $startDate, $endDate);
        });
    }
}
