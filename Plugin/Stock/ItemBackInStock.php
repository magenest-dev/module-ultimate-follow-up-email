<?php
namespace Magenest\UltimateFollowupEmail\Plugin\Stock;

use Magenest\UltimateFollowupEmail\Model\Processor\UltimateFollowupEmail;
use Magenest\UltimateFollowupEmail\Model\Processor\WishlistReminder;
use Magento\Wishlist\Model\Wishlist;

class ItemBackInStock extends WishlistReminder
{
    protected $type = 'wishlist_back_in_stock';

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item
     */
    protected $stockItem;

    public function run()
    {
        $wishlistBackInStockReminders = $this->_wishlistHelper->getCustomersProductWishlistById($this->stockItem->getProductId());

        foreach ($wishlistBackInStockReminders as $wishlistBackInStockReminder) {
            if (isset($wishlistBackInStockReminder['wishlist_id']) && $wishlistBackInStockReminder['wishlist_id']) {
                $this->currentWishlistItem = $wishlistBackInStockReminder;
                /** @var Wishlist $wishlist */
                $this->wishlist = $this->_wishlistFactory->create()->load($wishlistBackInStockReminder['wishlist_id']);
                $this->_emailTarget = $this->wishlist;
                $this->currentWishlistItem = $wishlistBackInStockReminder;
                $customer = $this->_customerFactory->create()->load($this->wishlist->getCustomerId());
                $this->wishlist->setData('customer_firstname', $customer->getFirstname());
                $this->wishlist->setData('customer_lastname', $customer->getLastname());
                $this->wishlist->setData('customer_email', $customer->getEmail());
                $this->currentCustomer = $customer;
                $this->wishlistProduct = $this->_productFactory->create()->load($wishlistBackInStockReminder['product_id']);
                $this->createFollowUpEmail();
            }
        }
    }

    public function beforeBeforeSave(\Magento\CatalogInventory\Model\Stock\Item $subject)
    {
        if ($subject->dataHasChangedFor('is_in_stock') && $subject->getIsInStock()==1) {
            $this->stockItem = $subject;
            $this->run();
        }
    }
}