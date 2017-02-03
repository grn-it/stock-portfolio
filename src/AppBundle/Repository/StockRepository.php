<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class StockRepository extends EntityRepository {
    
    /**
     * Возвращает последние по дате акции
     * 
     * @param type $symbolIds идентификаторы акций
     * @return type
     */
    public function getLastStocks($symbolIds) {
        
        $em = $this->getEntityManager();
        
        $qb = $em->createQueryBuilder();
        
        $qb->select('stock.id, stock.date, stock.close')->
             from('AppBundle:Stock', 'stock')->
             where('stock.symbolid IN(:symbolsIds)')->
             setParameter('symbolsIds', $symbolIds)->
             orderBy('stock.date', 'DESC')->
             setMaxResults(count($symbolIds));
               
        $query = $qb->getQuery();

        $stocks = $query->getResult();
        
        return $stocks;
    }
    
    /**
     * Возвращает сумму указанных акций за весь период
     * 
     * @param type $symbolIds
     * @return type
     */
    public function getStocksSum($symbolIds) {
    
        $em = $this->getEntityManager();
        
        $qb = $em->createQueryBuilder();
        
        $qb->select('stock.date, SUM(stock.close) close')->
             from('AppBundle:Stock', 'stock')->
             where('stock.symbolid IN(:symbolsIds)')->
             setParameter('symbolsIds', $symbolIds)->
             groupBy('stock.date')->
             orderBy('stock.date', 'ASC');
               
        $query = $qb->getQuery();

        $stocks = $query->getResult();
        
        return $stocks;
    }
    
    /**
     * Проверяет, есть ли данные по указанной акции
     * 
     * @param type $symbolId
     * @return boolean
     */
    public function isStockDataExist($symbolId) {
    
        $em = $this->getEntityManager();
        
        $qb = $em->createQueryBuilder();
        
        $qb->select('stock')->
             from('AppBundle:Stock', 'stock')->
             where('stock.symbolid = :symbolId')->
             setParameter('symbolId', $symbolId)->
             setMaxResults(1);
        
        $query = $qb->getQuery();

        $result = $query->getResult();
        
        if (!$result) {
            return false;
        }
        
        return true;
    }
}