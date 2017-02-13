<?php

namespace AppBundle\Data\Format\Highcharts;

class HighchartsFormat
{
    private $stocks;

    public function __construct($stocks)
    {
        $this->stocks = $stocks;
    }
    
    public function getStocks()
    {
        return $this->stocks;
    }
    
    public function setStocks($stocks)
    {
        $this->stocks = $stocks;
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
     * @return string
     */
    public function getJson()
    {
        $data = array_map(function($stock) {
            $date       = $stock['date'];
            $stockDate  = $date->format('U').'000';
            
            return '['.$stockDate.','.$stock['close'].']';
        }, $this->getStocks());
        
        $json = '['.implode(",\n", $data).']';
        
        return $json;
    }
}
