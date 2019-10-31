<?php
namespace Magenest\UltimateFollowupEmail\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\OrderFactory;
use Magento\Wishlist\Model\WishlistFactory;

class WishlistHelper extends AbstractHelper
{
    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    public function __construct(
        Context $context,
        WishlistFactory $wishlistFactory,
        OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->wishlistFactory = $wishlistFactory;
        $this->orderFactory = $orderFactory;
    }

    public function getCustomersProductWishlist()
    {
        $resource = $this->orderFactory->create()->getResource();
        $connection = $resource->getConnection();
        $select = $connection->select();
        $wishlistTable = $resource->getTable('wishlist');
        $wishlistItemTable = $resource->getTable('wishlist_item');
        $select->from(
            ['wishlist' => $wishlistTable],
            ['customer_id' ,'wishlist_id', 'updated_at']
        )->join(
            ['wishlist_item' => $wishlistItemTable],
            'wishlist.wishlist_id = wishlist_item.wishlist_id',
            ['product_id', 'added_at', 'wishlist_item_id']
        );
        $rows = $connection->fetchAll($select);
        return $rows;
    }

    public function getCustomersProductWishlistById($productId)
    {
        $resource = $this->orderFactory->create()->getResource();
        $connection = $resource->getConnection();
        $select = $connection->select();
        $wishlistTable = $resource->getTable('wishlist');
        $wishlistItemTable = $resource->getTable('wishlist_item');
        $select->from(
            ['wishlist' => $wishlistTable],
            ['customer_id' ,'wishlist_id', 'updated_at']
        )->join(
            ['wishlist_item' => $wishlistItemTable],
            'wishlist.wishlist_id = wishlist_item.wishlist_id',
            ['product_id', 'added_at', 'wishlist_item_id']
        )->where('wishlist_item.product_id = ?', $productId);
        $rows = $connection->fetchAll($select);
        return $rows;
    }
}
