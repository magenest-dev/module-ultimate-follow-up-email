<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 01/02/2016
 * Time: 10:20
 */

namespace Magenest\UltimateFollowupEmail\Model\Aggregator;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime;

class WishlistOnSale implements AggregatorInterface
{
    protected $productFactory;

    public function __construct(
        ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
    }

    public function collect()
    {
        $productCollection = $this->productFactory->create()->getCollection();
        $now = new \DateTime();
        $now = $now->format(DateTime::DATETIME_PHP_FORMAT);
        $productCollection
            ->addAttributeToFilter('special_price', ['gt' => '0'])
            ->addAttributeToFilter('special_from_date', [['lteq' => $now], ['null' => true]], 'left')
            ->addAttributeToFilter('special_to_date', [['gteq' => $now], ['null' => true]], 'left')
            ->getSelect()
            ->reset(Select::COLUMNS)
            ->columns('e.entity_id');
        $resource = $this->productFactory->create()->getResource();
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
        )->join(
            ['on_sales_product' => $productCollection->getSelect()],
            'on_sales_product.entity_id = wishlist_item.product_id',
            ''
        );
        $rows = $connection->fetchAll($select);
        return $rows;
    }
}
