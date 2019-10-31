<?php
/**
 * Author: Eric Quach
 * Date: 5/10/18
 */
namespace Magenest\UltimateFollowupEmail\Model\Processor;

use Magento\Email\Model\TemplateFactory as EmailTemplateModel;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;
use Magento\Wishlist\Model\Wishlist;

class WishlistOnSale extends WishlistReminder
{
    protected $wishlistOnSaleAggregator;

    protected $serialize;

    protected $type = 'wishlist_on_sale';

    const XML_PATH_FUE_WISHLIST_ON_SALE_PRODUCT = 'ultimatefollowupemail/email/fue_wishlist_on_sale_product';

    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Rule\CollectionFactory $rulesFactory,
        \Magenest\UltimateFollowupEmail\Model\MessageFactory $messageFactory,
        \Magenest\UltimateFollowupEmail\Model\SmsFactory $smsFactory,
        EmailTemplateModel $emailTemplateModel,
        \Magenest\UltimateFollowupEmail\Model\Aggregator\AbandonedCart $abandonedCart,
        \Magenest\UltimateFollowupEmail\Model\MailFactory $mailFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory, \Magento\Framework\Url $urlInterface,
        \Magento\Framework\Model\Context $context, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\SalesRule\Model\Coupon\Massgenerator $massGenerator, \Magento\Store\Model\App\Emulation $appEmulation,
        \Magenest\UltimateFollowupEmail\Helper\WishlistHelper $wishlistHelper,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer,
        \Magenest\UltimateFollowupEmail\Model\Aggregator\WishlistOnSale $wishlistOnSaleAggregator)
    {
        parent::__construct($rulesFactory, $messageFactory, $smsFactory, $emailTemplateModel, $abandonedCart, $mailFactory, $quoteFactory, $cartRepositoryInterface, $ruleFactory, $urlInterface, $context, $scopeConfig, $massGenerator, $appEmulation, $wishlistHelper, $wishlistFactory, $customerFactory, $catalogRuleFactory, $productFactory, $serializer);
        $this->wishlistOnSaleAggregator = $wishlistOnSaleAggregator;
    }

    public function run()
    {
        $wishlistItemOnSales = $this->wishlistOnSaleAggregator->collect();

        foreach ($wishlistItemOnSales as $wishlistItem) {
            if (isset($wishlistItem['wishlist_id']) && $wishlistItem['wishlist_id']) {
                /** @var Wishlist $wishlist */
                $this->wishlist = $this->_wishlistFactory->create()->load($wishlistItem['wishlist_id']);
                $this->_emailTarget = $this->wishlist;
                $this->currentWishlistItem = $wishlistItem;
                $customer = $this->_customerFactory->create()->load($this->wishlist->getCustomerId());
                $this->wishlist->setData('customer_firstname', $customer->getFirstname());
                $this->wishlist->setData('customer_lastname', $customer->getLastname());
                $this->wishlist->setData('customer_email', $customer->getEmail());
                $this->currentCustomer = $customer;
                $this->wishlistProduct = $this->_productFactory->create()->load($wishlistItem['product_id']);
                $this->createFollowUpEmail();
            }
        }
    }

    public function prepareMail()
    {
        parent::prepareMail();
        $this->_vars['wishlistOnSaleProduct'] = $this->getWishlistOnSaleProductHtml();
    }

    protected function getWishlistOnSaleProductHtml()
    {
        $product = $this->wishlistProduct;
        if (!$product->getId()) {
            return '';
        }
        $currencySymbol = '';
        $store = $product->getStore();
        if ($store instanceof Store) {
            $currencySymbol = $store->getCurrentCurrency()->getCurrencySymbol();
        }
        $var = [
            'product' => $product,
            'product_name' => $product->getName(),
            'product_url' => $product->getProductUrl(),
            'product_image_url' => $product->getMediaGalleryImages()->getFirstItem()->getUrl(),
            'product_price' => $currencySymbol . number_format($product->getFinalPrice(), 2),
            'old_product_price' => $currencySymbol . number_format($product->getPrice(), 2),
        ];
        return $this->getTemplateContent(self::XML_PATH_FUE_WISHLIST_ON_SALE_PRODUCT, $var);
    }

}