<?php
namespace Magenest\UltimateFollowupEmail\Model\ResourceModel\Unsubscriber\Grid;

use Magenest\UltimateFollowupEmail\Setup\UpgradeSchema;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid collection
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = UpgradeSchema::UNSUBSCRIBE_TABLE,
        $resourceModel = '\Magenest\UltimateFollowupEmail\Model\ResourceModel\Unsubscriber'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                ['rule' => $this->getTable('magenest_ultimatefollowupemail_rule')],
                'main_table.rule_id = rule.id',
                'rule.name as rule_name'
            );
        return $this;
    }
}
