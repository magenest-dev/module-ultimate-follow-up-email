<?php
namespace Magenest\UltimateFollowupEmail\Model\Aggregator;

use Magenest\UltimateFollowupEmail\Setup\UpgradeSchema;
use Magento\Framework\DB\Select;

class AbandonedCart implements AggregatorInterface
{
    const ABANDONED_CART_PERIOD = "ultimatefollowupemail/abandonedcart/time_range";

    protected $_resource;

    protected $_quotesFactory;
    protected $_ruleFactory;

    protected $_abandonedCartFactory;
    protected $guestFactory;

    /** @var  $_abandonedCartResource \Magenest\UltimateFollowupEmail\Model\ResourceModel\AbandonedCart */
    protected $_abandonedCartResource;

    protected $_scopeConfig;
    protected $_abandonedCartTime = "60";

    /**
     * @var \Magenest\UltimateFollowupEmail\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory,
        \Magenest\UltimateFollowupEmail\Model\AbandonedCartFactory $abandonedCartFactory,
        \Magenest\UltimateFollowupEmail\Model\GuestFactory $guestFactory,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\AbandonedCart $abandonedCartResource,
        \Magenest\UltimateFollowupEmail\Helper\Data $dataHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_quotesFactory = $quotesFactory;
        $this->_abandonedCartFactory = $abandonedCartFactory;
        $this->guestFactory = $guestFactory;
        $this->_ruleFactory = $ruleFactory;
        $this->_abandonedCartResource = $abandonedCartResource;
        $this->_scopeConfig = $scopeConfig;
        $this->helper = $dataHelper;
        $abandonedCartPeriod = $this->helper->getAbandonedCartPeriod();
        $this->_abandonedCartTime = $abandonedCartPeriod;
    }

    /**
     * collect abandoned cart
     */
    public function collect()
    {
        $this->updateCartProcessedStatus();

        $this->_collectAbandonedCart();

        $this->collectGuestAbandonedCart();

        //get all the abandoned carts from magenest_ultimatefollowupemail_guest_abandoned_cart
        $abandonedCarts = $this->_abandonedCartFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'is_processed', [
                ['eq' => 0],
                ['null' => true]
                ])
            ->addFieldToFilter('email', ['notnull' => true])
            ->addFieldToFilter('type', ['neq' => 'anonymous']);
        return $abandonedCarts;
    }


    /**
     * Get abandoned cart of customer
     * @return array
     */
    public function _collectAbandonedCart()
    {
        $abandonedCarts = $this->_abandonedCartResource->getAbandonedCartForInsertOperation($this->_abandonedCartTime);
        $records = [];
        $count = 0;
        $resource = $this->_abandonedCartFactory->create()->getResource();
        foreach ($abandonedCarts as $quote) {
            $records[] = [
                'quote_id' => $quote['entity_id'],
                'email' => $quote['customer_email'],
                'type' => 'customer',
                'status' => 0,
                'is_processed' => 0
            ];
            $count++;
            if ($count > 5000) {
                $resource->getConnection()
                    ->insertMultiple($resource->getMainTable(), $records);
                $records = [];
                $count = 0;
            }
        }
        if (count($records)) {
            $resource->getConnection()->insertMultiple($resource->getMainTable(), $records);
        }
        return $abandonedCarts;
    }

    /**
     * Get abandoned cart of guest
     * @return array
     */
    public function collectGuestAbandonedCart()
    {
        $abandonedCarts = $this->_abandonedCartResource->getAbandonedCartOfGuest($this->_abandonedCartTime);
        $records = [];
        $count = 0;
        $resource = $this->_abandonedCartFactory->create()->getResource();
        foreach ($abandonedCarts as $quote) {
            $records[] = [
                'quote_id' => $quote['entity_id'],
                'email' => $quote['email'],
                'type' => 'guest',
                'status' => 0,
                'is_processed' => 0
            ];
            $count++;
            if ($count > 5000) {
                $resource->getConnection()
                    ->insertMultiple($resource->getMainTable(), $records);
                $records = [];
                $count = 0;
            }
        }
        if (count($records)) {
            $resource->getConnection()
                ->insertMultiple($resource->getMainTable(), $records);
        }

        return $abandonedCarts;
    }

    public function updateCartProcessedStatus()
    {
        $resource = $this->_abandonedCartFactory->create()->getResource();
        $upperLimit = $this->_abandonedCartResource->getUpperLimit($this->_abandonedCartTime);
        $connection = $resource->getConnection();
        $quoteJoiner = $connection->select()
            ->joinInner(
                ['q' => $resource->getTable('quote')],
                'q.entity_id = abc.quote_id AND q.is_active = 1 AND abc.updated_at != q.updated_at'
            )
            ->reset(Select::COLUMNS)
            ->where('q.updated_at < ?', $upperLimit)
            ->columns(['updated_at', 'is_processed' => '0.0', 'status' => '0.0']);
        $updateQuery = $connection->updateFromSelect($quoteJoiner, ['abc' => $resource->getTable(UpgradeSchema::ABANDONED_CART_TABLE)]);
        $connection->query($updateQuery);
    }
}
