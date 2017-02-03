<?php

namespace AppBundle\Stock;

use AppBundle\Entity\Stock;
use AppBundle\Entity\Portfolio;
use Scheb\YahooFinanceApi\ApiClient;

use Doctrine\ORM\EntityManager;

class StockManager {
    
    protected $apiClient;
    protected $em;
    
    public function __construct(EntityManager $em) {
    
        $this->em = $em;
        
        $this->apiClient = new ApiClient();
        
        $this->checkStockDataExist();
    }
    
    /**
     * Возвращает последние по дате акции
     * 
     * Нужно для страницы просмотра портфеля
     * 
     * @param Portfolio $portfolio
     * @return type
     */
    public function getLastStocks(Portfolio $portfolio) {
        
        // информация о акции - YHOO, GOOG...
        $symbolInfo = $portfolio->getSymbolid();
        
        $symbolIds = array();
        
        // получаем идентификаторы акций
        foreach ($symbolInfo as $symbolItem) {
            $symbolIds[] = $symbolItem->getId();
        }
        
        // получаем последние по дате акции, которые есть в портфеле
        $lastStocks = $this->em->getRepository('AppBundle:Stock')->getLastStocks($symbolIds);
        
        $stockIds = array();
        
        foreach ($lastStocks as $stock) {
            $stockIds[] = $stock['id'];
        }
        
        // это запрос нужен, чтобы получить название акции (YHOO, GOOG...)
        $lastStocks = $this->em->getRepository('AppBundle:Stock')->findBy(array ('id' => $stockIds));
        
        return $lastStocks;
    }
    
    /**
     * Возвращает сумму по акциям за весь период
     */
    public function getStocksSum(Portfolio $portfolio) {
        
        $symbolInfo = $portfolio->getSymbolid();
        
        $symbolIds = array();
        
        foreach ($symbolInfo as $symbolItem) {
            $symbolIds[] = $symbolItem->getId();
        }
        
        $stocksSum = $this->em->getRepository('AppBundle:Stock')->getStocksSum($symbolIds);
        
        return $stocksSum;
    }
    
    /**
     * Возвращает данные для графика в формате JSON
     * 
     * Пояснение, почему не используется стандартный json_encode()
     * Библиотека Highcharts (график) требует JSON в таком виде:
     * [[1264982400000,27.82],
     * [1265068800000,27.98],
     * [1265155200000,28.46]]
     * ...
     * можно увидеть большие числа «1264982400000» (число микросекунд)
     * чтобы правильно представить их в массиве нужно их преобразовать в тип float, 
     * иначе они будут в виде строки ["1265068800000","27.98"] (кавычки не требуются), 
     * но тип float ограничивается 14 цифрами, а у нас уже 13
     * поэтому есть некоторые опасения, что в будущем значение выйдет за границы
     * 
     * @param type $stocks
     * @return string
     */
    public function getHighchartsJson($stocks) {
        
        $data = '';
        
        foreach ($stocks as $stock) {
        
            $date = $stock['date'];

            $stockDate = $date->format('U') . '000';

            $data .= '[' . $stockDate . ',' . $stock['close'] . '],' . "\n";
        }
        
        $highchartsJson = rtrim($data, ",\n");
        
        $highchartsJson = '[' . $highchartsJson . ']';
        
        return $highchartsJson;
    }
    
    /**
     * Возвращает диапазон дат для последних 2 лет
     * 
     * Нужно потому, что у Yahoo Finance API есть ограничения по выводу данных в 1 год
     * 
     * @return type
     */
    private function getDateRanges() {
        
        $dateRanges = array();
        
        $startDateFormat = 'Y-01-01';
        $endDateFormat = 'Y-12-31';

        for ($year = 2; $year >= 0; $year--) {

            $strtotimeFormat = '-' . $year . ' year';

            if ($year === 0) {
                $endDateFormat = 'Y-m-d';
                $strtotimeFormat = 'now';
            }

            $dateRanges[] = array('startDate' => date($startDateFormat, strtotime($strtotimeFormat)), 
                                    'endDate' => date($endDateFormat, strtotime($strtotimeFormat)));
        }
        
        return $dateRanges;
    }

    /**
     * Загружает данные с Yahoo Finance
     * 
     * @return type
     */
    public function loadStockData($symbol) {
        
        $stockData = array();
        
        $dateRanges = $this->getDateRanges();
        
        $symbolId = $symbol->getId();
        $symbolName = $symbol->getName();

        foreach ($dateRanges as $dateRange) {

            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];

            // загружаем данные из Yahoo Finance
            $stockData[$symbolId][] = $this->apiClient->getHistoricalData($symbolName, new \DateTime($startDate), new \DateTime($endDate));
        }
        
        return $stockData;
    }
    
    /**
     * Сохраняет данные полученные из Yahoo Finance в базу данных
     * 
     * @param type $stockData
     */
    public function saveStockData($stockData) {
        
        $lastClose = null;
        
        foreach ($stockData as $symbolId => $stockDataByDateRanges) {

            // получаем название акции
            $symbol = $this->em->getRepository('AppBundle:Symbol')->find($symbolId);
            
            // перечисляем данные по диапазонам (годам)
            foreach ($stockDataByDateRanges as $stockDataByDateRange) {
                
                $stockDataByDateRange = $stockDataByDateRange['query']['results']['quote'];

                $stockDataByDateRange = array_reverse($stockDataByDateRange);
                
                foreach ($stockDataByDateRange as $stockItem) {

                    // change показывает, как текущая цена изменилась по отношению 
                    // к цене закрытия (close) за предыдущий торговый день
                    
                    // рассчитываем change
                    $close = $stockItem['Close'];
                    
                    $change = null;
                    
                    if (!is_null($lastClose)) {
                        $change = $close - $lastClose;
                    }
                    
                    $lastClose = $close;
                    
                    // /рассчитываем change
                    
                    $stock = new Stock();

                    $stock->setSymbolid($symbol);
                    $stock->setDate(new \DateTime($stockItem['Date']));
                    $stock->setOpen($stockItem['Open']);
                    $stock->setHigh($stockItem['High']);
                    $stock->setLow($stockItem['Low']);
                    $stock->setClose($close);
                    $stock->setChange($change);
                    $stock->setVolume($stockItem['Volume']);

                    $this->em->persist($stock);
                }
            }
        }
        
        $this->em->flush();
    }
    
    /**
     * Проверяет, есть ли данные по указанной акции в базе данных
     * 
     * @param type $symbolId
     * @return boolean
     */
    public function isStockDataExist($symbolId) {
        
        if ($this->em->getRepository('AppBundle:Stock')->isStockDataExist($symbolId)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверяет, есть ли данные всех имеющихся акций в базе данных
     */
    private function checkStockDataExist() {
        
        $symbols = $this->em->getRepository('AppBundle:Symbol')->findBy(array ());
        
        foreach ($symbols as $symbol) {
            
            $symbolId = $symbol->getId();
            
            // проверяем, есть ли данные по указанной акции в базе данных
            if ($this->isStockDataExist($symbolId)) {
                continue;
            }
            
            // загружаем данные с Yahoo Finance
            $stockData = $this->loadStockData($symbol);
            
            // сохраняем данные полученные из Yahoo Finance в базу данных
            $this->saveStockData($stockData);
        }
    }
}