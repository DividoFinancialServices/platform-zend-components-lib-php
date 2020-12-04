<?php

use Doctrine\ORM\QueryBuilder;

/**
 * An adapter to be used with Doctrine 2.
 * Can be plugged in Zend_Paginator to use all of its functionality.
 * TODO Update the phpdocs
 * 
 * @author Fernando Mantoan
 */
class Divido_Paginator_Adapter_Doctrine implements Zend_Paginator_Adapter_Interface
{
	protected $_queryBuilder;
	protected $_queryBuilderCount;
	
	public function __construct(QueryBuilder $queryBuilder)
	{
		$this->_queryBuilder = $queryBuilder;
		$this->_queryBuilderCount = clone $queryBuilder;
	}
	
	public function getItems($offset, $itemCountPerPage)
	{
		$this->_queryBuilder->setFirstResult($offset);
		$this->_queryBuilder->setMaxResults($itemCountPerPage);
		return $this->_queryBuilder->getQuery()->getResult();
	}

	public function count()
	{
		$queryBuilder = $this->_queryBuilderCount;
		$selectPart = $queryBuilder->getDQLPart('select');
		$fromPart = $queryBuilder->getDQLPart('from');
		$fromPart = $fromPart[0];
		
		$queryBuilder->select('COUNT(' . $fromPart->getAlias() . ') AS total')
					 ->resetDQLPart('orderBy')
					 ->resetDQLPart('groupBy');
					 
		$count = (int) $queryBuilder->getQuery()->getSingleScalarResult();
		
		return $count;
	}
}