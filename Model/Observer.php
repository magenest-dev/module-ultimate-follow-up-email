<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 29/09/2015
 * Time: 20:56
 */

namespace Magenest\UltimateFollowupEmail\Model;

class Observer
{

    protected $_logger;

    protected $_rulesFactory;

    protected $type;


    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Rule\CollectionFactory $rulesFactory
    ) {
        $this->_logger       = $logger;
        $this->_rulesFactory = $rulesFactory;
    }


    public function getType()
    {
        return $this->type;
    }


    public function getIdProduct()
    {
        $rules = $this->getMatchingRule();
        $this->_logger->addDebug('$rules');
    }


    public function getMatchingRule()
    {
        $collection = $this->_rulesFactory->create();

        $collection->getRulesByType($this->type);
        $this->_logger->addDebug($collection);
        return $collection;
    }
}
