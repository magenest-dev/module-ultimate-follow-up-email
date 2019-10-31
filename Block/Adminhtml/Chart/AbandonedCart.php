<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Chart;

use Magenest\UltimateFollowupEmail\Model\AbandonedCartFactory;
use Magenest\UltimateFollowupEmail\Model\GuestFactory;
use Magenest\UltimateFollowupEmail\Setup\UpgradeSchema;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime;
use Magento\Quote\Model\QuoteFactory;

/**
 * Class Form
 * @package Magenest\Xero\Block\Adminhtml\Request
 */
class AbandonedCart extends AbstractChart
{
    protected $quoteFactory;

    protected $abandonedCartFactory;

    protected $guestFactory;

    protected $abandonedCarts;

    protected $carts;

    protected $nonAbandonedCarts;

    protected $repurchasedAbandonedCarts;

    protected $nonRepurchasedAbandonedCarts;

    protected $guestAbandonedCarts;

    protected $customerAbandonedCarts;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        QuoteFactory $quoteFactory,
        AbandonedCartFactory $abandonedCartFactory,
        GuestFactory $guestFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->quoteFactory = $quoteFactory;
        $this->abandonedCartFactory = $abandonedCartFactory;
        $this->guestFactory = $guestFactory;
    }

    public function getAbandonedCartData()
    {
        return [
            'Abandoned' => $this->getAbandonedCarts(),
            'Not Abandoned' => $this->getNonAbadonedCarts()
        ];
    }

    public function getGuestAbandonedCartData()
    {
        return [
            'Guest' => $this->getGuestAbandonedCarts(),
            'Customer' => $this->getCustomerAbandonedCarts()
        ];
    }

    public function getRepurchasedCartData()
    {
        return [
            'Repurchased' => $this->getRepurchasedAbandonedCarts(),
            'Abandoned' => $this->getNonRepurchasedAbandonedCarts()
        ];
    }

    public function getAbandonedCartLineData()
    {
        $quote = $this->quoteFactory->create();
        $connection = $quote->getResource()->getConnection();
        $quoteCollection = $quote->getCollection();
        $followUpAbandonedCartTable = $quoteCollection->getTable('magenest_ultimatefollowupemail_guest_abandoned_cart');
        $this->applyPeriodToCollection($quoteCollection, ['main_table.created_at']);
        $select = $quoteCollection->getSelect()->reset(Select::COLUMNS)
            ->joinLeft(
                ['a' => $followUpAbandonedCartTable],
                'main_table.entity_id = a.quote_id',
                []
            )->where(
                'a.quote_id is not null'
            )->group(
                'CAST(main_table.created_at AS DATE)'
            )->order(
                'CAST(main_table.created_at AS DATE) ASC'
            )->columns([
                'COUNT(main_table.entity_id) as count',
                'created_at' => new \Zend_Db_Expr('CAST(main_table.created_at AS DATE)')
            ]);
        $rows = $quote->getResource()->getConnection()->fetchAll($select);
        return $rows;
    }

    public function getAbandonedCarts()
    {
        if ($this->abandonedCarts) {
            return $this->abandonedCarts;
        }
        $abadonedCarts = $this->getCustomerAbandonedCarts() + $this->getGuestAbandonedCarts();
        $this->abandonedCarts = $abadonedCarts;
        return $this->abandonedCarts;
    }

    public function getCustomerAbandonedCarts()
    {
        if ($this->customerAbandonedCarts) {
            return $this->customerAbandonedCarts;
        }
        $cartModel = $this->abandonedCartFactory->create();
        $collection = $cartModel->getCollection();
        $collection
            ->getSelect()
            ->joinLeft(
                ['q' => $collection->getTable('quote')],
                'main_table.quote_id = q.entity_id',
                ['created_at', 'updated_at']
            )->joinLeft(
                ['g' => $cartModel->getResource()->getTable(UpgradeSchema::CAPTURED_GUEST_TABLE)],
                'main_table.quote_id = g.quote_id',
                []
            )->where('g.email is null');
        $this->applyPeriodToCollection($collection, ['q.created_at']);
        $this->customerAbandonedCarts = $collection->getSize();
        return $this->customerAbandonedCarts;
    }

    public function getGuestAbandonedCarts()
    {
        if ($this->guestAbandonedCarts) {
            return $this->guestAbandonedCarts;
        }
        $cartModel = $this->abandonedCartFactory->create();
        $collection = $cartModel->getCollection();
        $collection
            ->getSelect()
            ->joinLeft(
                ['q' => $collection->getTable('quote')],
                'main_table.quote_id = q.entity_id',
                ['created_at', 'updated_at']
            )->join(
                ['g' => $cartModel->getResource()->getTable(UpgradeSchema::CAPTURED_GUEST_TABLE)],
                'main_table.quote_id = g.quote_id',
                []
            );
        $this->applyPeriodToCollection($collection, ['q.created_at']);
        $this->guestAbandonedCarts = $collection->getSize();
        return $this->guestAbandonedCarts;
    }

    public function getCarts()
    {
        if ($this->carts) {
            return $this->carts;
        }
        $carts = $this->quoteFactory->create()
            ->getCollection();
        $this->applyPeriodToCollection($carts, ['created_at']);
        $this->carts = $carts->getSize();
        return $this->carts;
    }

    public function getNonAbadonedCarts()
    {
        if ($this->nonAbandonedCarts) {
            return $this->nonAbandonedCarts;
        }
        $this->nonAbandonedCarts = $this->getCarts() - $this->getAbandonedCarts();
        return $this->nonAbandonedCarts;
    }

    public function getRepurchasedAbandonedCarts()
    {
        if (!$this->repurchasedAbandonedCarts) {
            $repurchasedAbandonedCarts = $this->abandonedCartFactory->create()->getCollection();
            $repurchasedAbandonedCarts->addFieldToFilter('status', \Magenest\UltimateFollowupEmail\Model\AbandonedCart::STATUS_CONVERTED);
            $this->repurchasedAbandonedCarts = $repurchasedAbandonedCarts->getSize();
        }
        return $this->repurchasedAbandonedCarts;
    }

    public function getNonRepurchasedAbandonedCarts()
    {
        if ($this->nonRepurchasedAbandonedCarts) {
            return $this->nonRepurchasedAbandonedCarts;
        }
        $this->nonRepurchasedAbandonedCarts = $this->getAbandonedCarts() - $this->getRepurchasedAbandonedCarts();
        return $this->nonRepurchasedAbandonedCarts;
    }
}
