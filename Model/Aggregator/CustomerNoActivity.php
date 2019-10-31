<?php
/**
 * Author: Eric Quach
 * Date: 5/7/18
 */
namespace Magenest\UltimateFollowupEmail\Model\Aggregator;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Select;

class CustomerNoActivity implements AggregatorInterface
{
    protected $customerFactory;
    protected $scopeConfig;

    public function __construct(
        CustomerFactory $customerFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerFactory = $customerFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function collect()
    {
        $collection = $this->getCustomerLogCollection();
        $collection->getSelect()
            ->where('log.last_login_at < ?', $this->getUpperLimit())
            ->where('visitor.last_visit_at < ?', $this->getUpperLimit());
        return $collection;
    }

    public function getUpperLimit()
    {
        $upperLimit = $this->scopeConfig->getValue('ultimatefollowupemail/customer_no_activity/period');
        $upperLimit = $upperLimit?:24;
        $modify = '-' . $upperLimit . ' hours';
        $now = new \DateTime();
        $now->modify($modify);
        $upperLimit = $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        return $upperLimit;
    }

    protected function getCustomerLogCollection()
    {
        $collection = $this->customerFactory->create()->getCollection();
        $visitSelector = $collection->getConnection()->select()
            ->from($collection->getTable('customer_visitor'))
            ->reset(Select::COLUMNS)
            ->columns('MAX(visitor_id)')
            ->group('customer_id');
        $collection->getSelect()
            ->joinLeft(
                ['log' => $collection->getTable('customer_log')],
                'e.entity_id = log.customer_id'
            )->joinLeft(
                ['visitor' => $collection->getTable('customer_visitor')],
                'e.entity_id = visitor.customer_id AND visitor.visitor_id IN ('.$visitSelector->assemble().')'
            );
        return $collection;
    }
}