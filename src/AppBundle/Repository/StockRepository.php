<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Portfolio;
use AppBundle\Entity\Symbol;

class StockRepository extends EntityRepository
{
    /**
     * @param Portfolio $portfolio
     * @return array
     */
    public function getLast(Portfolio $portfolio)
    {
        $stocks =   $this-> getEntityManager()->
                            createQueryBuilder()->
                            select('stock.id, stock.date, stock.close')->
                            from('AppBundle:Stock', 'stock')->
                            where('stock.symbol IN(:symbolIds)')->
                            setParameter('symbolIds', $portfolio->getSymbolIds())->
                            orderBy('stock.date', 'DESC')->
                            setMaxResults(count($portfolio->getSymbolIds()))->
                            getQuery()->
                            getResult();

        $stockIds = array_map(function($stock) {
            return $stock['id'];
        }, $stocks);
        
        $last = $this->findBy(array ('id' => $stockIds));
        
        return $last;
    }

    /**
     * @param Portfolio $portfolio
     * @return array
     */
    public function getStocksSum(Portfolio $portfolio)
    {
        $stocks =   $this-> getEntityManager()->
                            createQueryBuilder()->
                            select('stock.date, SUM(stock.close) close')->
                            from('AppBundle:Stock', 'stock')->
                            where('stock.symbol IN(:symbolIds)')->
                            setParameter('symbolIds', $portfolio->getSymbolIds())->
                            groupBy('stock.date')->
                            orderBy('stock.date', 'ASC')->
                            getQuery()->
                            getResult();

        return $stocks;
    }

    /**
     * @param Symbol $symbol
     * @return boolean
     */
    public function isStockDataExist(Symbol $symbol)
    {
        $result =   $this-> 
                    getEntityManager()->
                    createQueryBuilder()->
                    select('stock')->
                    from('AppBundle:Stock', 'stock')->
                    where('stock.symbol = :symbol')->
                    setParameter('symbol', $symbol)->
                    setMaxResults(1)->
                    getQuery()->
                    getResult();

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * @param Symbol $symbol
     * @return mixed
     */
    public function getStockMinDate(Symbol $symbol)
    {
        $result =   $this->
                    getEntityManager()->
                    createQueryBuilder()->select('MIN(stock.date) minDate')->
                    from('AppBundle:Stock', 'stock')->
                    where('stock.symbol = :symbol')->
                    setParameter('symbol', $symbol)->
                    setMaxResults(1)->
                    getQuery()->
                    getSingleResult();

        if (!$result) {
            return false;
        }

        $minDate = $result['minDate'];

        return $minDate;
    }

    /**
     * @param type $symbol
     * @return boolean
     */
    public function getStockMaxDate(Symbol $symbol)
    {
        $result =   $this->
                    getEntityManager()->
                    createQueryBuilder()->
                    select('MAX(stock.date) maxDate')->
                    from('AppBundle:Stock', 'stock')->
                    where('stock.symbol = :symbol')->
                    setParameter('symbol', $symbol)->
                    setMaxResults(1)->
                    getQuery()->
                    getSingleResult();

        if (!$result) {
            return false;
        }

        $maxDate = $result['maxDate'];

        return $maxDate;
    }
    
    /**
     * @param mixed $entity
     */
    public function persist($entity)
    {
        $this->_em->persist($entity);
    }
    
    public function flush()
    {
        $this->_em->flush();
    }
}
