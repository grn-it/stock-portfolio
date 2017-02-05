<?php

namespace AppBundle\Stock;

use AppBundle\Entity\Stock;
use AppBundle\Entity\Portfolio;
use Scheb\YahooFinanceApi\ApiClient;

use Doctrine\ORM\EntityManager;

use Scheb\YahooFinanceApi\Exception\ApiException;

use \Predis\Client;

/**
 * Сервис управления портфелями акций
 */
class StockManager
{
    protected $apiClient;
    protected $em;
    protected $redis;

    /**
     *
     * @param EntityManager $em
     * @param Client        $redis
     */
    public function __construct(EntityManager $em, \Predis\Client $redis)
    {
        $this->em = $em;

        $this->redis = $redis;

        $this->apiClient = new ApiClient();
    }

    /**
     * Возвращает информацию о самых актуальных
     * по дате акциях в указанном портфеле.
     *
     * @param Portfolio $portfolio
     * @return type
     */
    public function getLastStocks(Portfolio $portfolio)
    {
        $symbols = $portfolio->getSymbolid();

        $symbolIds = array();

        // перечисляем все акции в портфеле
        foreach ($symbols as $symbol) {
            $symbolIds[] = $symbol->getId();

            // возможно данные устарели,
            // пробуем актуализировать
            $this->actualizeStockData($symbol);

            // диапазон - 1 неделя
            $startDate = date('Y-m-d', strtotime('-7 day'));
            $endDate = date('Y-m-d', strtotime('-1 day'));

            // возможно данных нет совсем,
            // нужно проверить и нет, то загрузить на 1 неделю
            $this->checkStockDataExist($symbol, $startDate, $endDate);
        }

        // получаем последние по дате акции, которые есть в портфеле
        $lastStocks = $this->em->getRepository('AppBundle:Stock')->getLastStocks($symbolIds);

        $stockIds = array();

        foreach ($lastStocks as $stock) {
            $stockIds[] = $stock['id'];
        }

        // это запрос нужен, чтобы получить название акции (YHOO, GOOG...)
        $lastStocks = $this->em->getRepository('AppBundle:Stock')->findBy(array ('id' => $stockIds));

        // возвращается список по одной акции
        // в текущем портфеле с самыми актуальными данными

        return $lastStocks;
    }

    /**
     * Возвращает сумму цен акций за указанный период
     *
     * @param Portfolio $portfolio
     * @param type      $startDate
     * @param type      $endDate
     * @return type
     */
    public function getStocksSum(Portfolio $portfolio, $startDate, $endDate)
    {
        $symbols = $portfolio->getSymbolid();

        $redisKey = 'stock-sum:'.$portfolio->getId().':'.$startDate.':'.$endDate;

        $redisData = $this->redis->get($redisKey);

        if (!is_null($redisData)) {
            return unserialize($redisData);
        }

        $symbolIds = array();

        foreach ($symbols as $symbol) {
            $symbolId = $symbol->getId();

            $symbolIds[] = $symbolId;

            $this->actualizeStockData($symbol);

            $this->checkStockDataExist($symbol, $startDate, $endDate);
        }

        $stocksSum = $this->em->getRepository('AppBundle:Stock')->getStocksSum($symbolIds);

        $this->redis->set($redisKey, serialize($stocksSum));

        return $stocksSum;
    }

    /**
     * Возвращает данные для графика в формате JSON
     *
     * Пояснение, почему не используется стандартный json_encode()
     *
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
    public function getHighchartsJson($stocks)
    {
        $data = '';

        foreach ($stocks as $stock) {
            $date = $stock['date'];

            $stockDate = $date->format('U').'000';

            $data .= '['.$stockDate.','.$stock['close'].'],'."\n";
        }

        $highchartsJson = rtrim($data, ",\n");

        $highchartsJson = '['.$highchartsJson.']';

        return $highchartsJson;
    }

    /**
     * Загружает данные с Yahoo Finance
     *
     * @param type $symbol
     * @param type $dateRanges
     * @return boolean
     */
    public function loadStockData($symbol, $dateRanges)
    {
        if (!$dateRanges) {
            return false;
        }

        $stockData = array();

        $symbolId = $symbol->getId();
        $symbolName = $symbol->getName();

        foreach ($dateRanges as $dateRange) {
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];

            $redisKey = 'load-data:'.$symbolId.':'.$startDate.':'.$endDate;

            $redisData = $this->redis->get($redisKey);

            if (!is_null($redisData)) {
                $stockData[$symbolId][] = unserialize($redisData);

                continue;
            }

            $this->redis->set($redisKey, serialize(false));

            try {
                $historicalData = $this->apiClient->getHistoricalData($symbolName, new \DateTime($startDate), new \DateTime($endDate));

                // загружаем данные из Yahoo Finance
                $stockData[$symbolId][] = $historicalData;

                $this->redis->set($redisKey, serialize($historicalData));
            } catch (ApiException $exception) {
                // иногда бывает, что данных просто нет,
                // если например запросить данные попадающие на выходные
            }
        }

        return $stockData;
    }

    /**
     * Сохраняет данные полученные из Yahoo Finance в базу данных
     *
     * @param type $stockData
     * @return boolean
     */
    public function saveStockData($stockData)
    {
        if (!$stockData) {
            return false;
        }

        $lastClose = null;

        foreach ($stockData as $symbolId => $stockDataByDateRanges) {
            // получаем название акции
            $symbol = $this->em->getRepository('AppBundle:Symbol')->find($symbolId);

            // перечисляем данные по диапазонам
            foreach ($stockDataByDateRanges as $stockDataByDateRange) {
                $stockDataByDateRange = $stockDataByDateRange['query']['results']['quote'];

                // если выдан результат не в виде коллекции массивов
                if (!isset($stockDataByDateRange[0])) {
                    $stockDataByDateRange = array($stockDataByDateRange);
                } else {
                    $stockDataByDateRange = array_reverse($stockDataByDateRange);
                }

                foreach ($stockDataByDateRange as $stockItem) {
                    // change показывает, как текущая цена изменилась по отношению
                    // к цене закрытия (close) за предыдущий торговый день

                    // рассчитываем change
                    $close = $stockItem['Close'];

                    $change = null;

                    if (!is_null($lastClose)) {
                        $change = $close - $lastClose;
                    } else {
                        $lastClose = $close;

                        continue;
                    }

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
    public function isStockDataExist($symbolId)
    {
        if ($this->em->getRepository('AppBundle:Stock')->isStockDataExist($symbolId)) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает диапазон дат рапределенный по годам
     *
     * Пример:
     * ["startDate"]=>
     * string(10) "2015-05-01"
     * ["endDate"]=>
     * string(10) "2015-12-31"
     * ["startDate"]=>
     * string(10) "2016-01-01"
     * ["endDate"]=>
     * string(10) "2016-12-31"
     * ["startDate"]=>
     * string(10) "2017-01-01"
     * ["endDate"]=>
     * string(10) "2017-02-04"
     *
     * Нужно потому, что у Yahoo Finance API есть ограничения по выводу данных в 1 год
     *
     * @return type
     */
    private function getDateRanges($startDate, $endDate)
    {
        $dateRanges = array();

        $startDate = date('Y-m-d', strtotime($startDate.' -1 day'));

        if (strtotime($startDate) > strtotime($endDate)) {
            return false;
        }

        $startDateYear  = date('Y', strtotime($startDate));
        $endDateYear    = date('Y', strtotime($endDate));

        if ($startDateYear == $endDateYear) {
            $startDateFormat = 'Y-m-d';
            $endDateFormat = 'Y-m-d';

            $currentStartDate = $startDate;
            $currentEndDate = $endDate;


            $dateRanges[] = array('startDate' => date($startDateFormat, strtotime($currentStartDate)),
                                  'endDate' => date($endDateFormat, strtotime($currentEndDate)), );
        } else {
            for ($currentYear = $startDateYear; $currentYear <= $endDateYear; $currentYear++) {
                $startDateFormat    = 'Y-01-01';
                $endDateFormat      = 'Y-12-31';

                $currentStartDate   = $currentYear.'-01-01';
                $currentEndDate     = $currentYear.'-12-31';

                if ($currentYear == $startDateYear) {
                    $startDateFormat = 'Y-m-d';

                    $currentStartDate = $startDate;
                } elseif ($currentYear == $endDateYear) {
                    $endDateFormat = 'Y-m-d';

                    $currentEndDate = $endDate;
                }

                $dateRanges[] = array('startDate' => date($startDateFormat, strtotime($currentStartDate)),
                                      'endDate' => date($endDateFormat, strtotime($currentEndDate)), );
            }
        }

        return $dateRanges;
    }

    /**
     * Проверяет, есть ли данные акции за указанный диапазон
     *
     * @param type $symbolId
     */
    private function checkStockDataExist($symbol, $startDate, $endDate)
    {
        $symbolId = $symbol->getId();

        // получаем диапазон дат рапределенный по годам
        $dateRanges = $this->getDateRanges($startDate, $endDate);

        // проверяем, есть ли данные по этой акции
        $stockMinDate = $this->em->getRepository('AppBundle:Stock')->getStockMinDate($symbolId);

        // данных для данной акции нет совсем
        if (!$stockMinDate) {
            // загружаем с Yahoo Finance
            $stockData = $this->loadStockData($symbol, $dateRanges);

            // сохраняем
            $this->saveStockData($stockData);

            $this->actualizeStockData($symbol);
        } elseif (strtotime($startDate) < strtotime($stockMinDate)) {
            // данных нехватает, нужна дозагрузка

            $stockMinDate = date('Y-m-d', strtotime($stockMinDate.' -1 day'));

            $dateRangeUntilMinDate = $this->getDateRanges($startDate, $stockMinDate);

            $stockData = $this->loadStockData($symbol, $dateRangeUntilMinDate);

            $this->saveStockData($stockData);
        }
    }

    /**
     * Возвращает актуальные данные по акции
     *
     * Берем самую последнюю по дате запись акции в таблице и
     *
     * @param type $symbol
     */
    private function actualizeStockData($symbol)
    {
        $symbolId = $symbol->getId();

        $redisKey = 'actualize:'.$symbolId;

        $actualizeDate = $this->redis->get($redisKey);

        $now = date('Y-m-d');

        if (is_null($actualizeDate)) {
            $this->redis->set($redisKey, serialize($now));
        } else {
            if (unserialize($actualizeDate) == $now) {
                return true;
            } else {
                $this->redis->set($redisKey, serialize($now));
            }
        }

        $stockMaxDate = $this->em->getRepository('AppBundle:Stock')->getStockMaxDate($symbolId);

        $startDate = date('Y-m-d', strtotime($stockMaxDate.' +1 day'));
        $endDate = date('Y-m-d', strtotime('-1 day'));

        $dateRanges = $this->getDateRanges($startDate, $endDate);

        $stockData = $this->loadStockData($symbol, $dateRanges);

        $this->saveStockData($stockData);
    }
}
