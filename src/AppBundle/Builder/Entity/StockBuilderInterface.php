<?php

namespace AppBundle\Builder\Entity;

use AppBundle\Entity\Symbol;

interface StockBuilderInterface {
    public function setChange($change);
    public function setClose($close);
    public function setDate($date);
    public function setHigh($high);
    public function setLow($low);
    public function setOpen($open);
    public function setSymbol(Symbol $symbol = null);
    public function setVolume($volume);
    public function build();
}
