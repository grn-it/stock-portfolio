<?php

namespace AppBundle\Builder\Entity;

use AppBundle\Entity\Stock;
use AppBundle\Entity\Symbol;

class StockBuilder implements StockBuilderInterface
{
    private $change;
    private $close;
    private $date;
    private $high;
    private $low;
    private $open;
    private $symbol;
    private $volume;

    /**
     * Set change
     *
     * @param string $change
     */
    public function setChange($change)
    {
        $this->change = $change;
    }
    
    /**
     * Set close
     *
     * @param string $close
     */
    public function setClose($close)
    {
        $this->close = $close;
    }
    
    /**
     * Set date
     *
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
    
    /**
     * Set high
     *
     * @param string $high
     */
    public function setHigh($high)
    {
        $this->high = $high;
    }
    
    /**
     * Set low
     *
     * @param string $low
     */
    public function setLow($low)
    {
        $this->low = $low;
    }
    
    /**
     * Set open
     *
     * @param string $open
     */
    public function setOpen($open)
    {
        $this->open = $open;
    }
    
    /**
     * Set symbol
     *
     * @param Symbol $symbol
     */
    public function setSymbol(Symbol $symbol = null)
    {
        $this->symbol = $symbol;
    }
    
    /**
     * Set volume
     *
     * @param integer $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }
    
    /**
     * @return Stock
     */
    public function build()
    {
        $stock = new Stock();

        $stock->setSymbolid($this->symbol);
        $stock->setDate($this->date);
        $stock->setOpen($this->open);
        $stock->setHigh($this->high);
        $stock->setLow($this->low);
        $stock->setClose($this->close);
        $stock->setChange($this->change);
        $stock->setVolume($this->volume);
        
        return $stock;
    }
}