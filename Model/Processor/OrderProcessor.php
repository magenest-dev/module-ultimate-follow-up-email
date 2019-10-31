<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 19/01/2016
 * Time: 11:28
 */
namespace Magenest\UltimateFollowupEmail\Model\Processor;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Email\Model\TemplateFactory as EmailTemplateFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderProcessor extends \Magenest\UltimateFollowupEmail\Model\Processor\UltimateFollowupEmail
{
    const XML_PATH_FUE_ORDER_ITEM = 'ultimatefollowupemail/email/fue_order_item';
    const XML_PATH_FUE_ORDER_ITEMS = 'ultimatefollowupemail/email/fue_order_items';

    protected $_customerFactory;

    /**
     * @var   \Magento\SalesRule\Model\RuleFactory
     */
    protected $_saleRuleFactory;
    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $_totalsCollector;

    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Rule\CollectionFactory $rulesFactory,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory,
        \Magenest\UltimateFollowupEmail\Model\MessageFactory $messageFactory,
        \Magenest\UltimateFollowupEmail\Model\SmsFactory $smsFactory,
        EmailTemplateFactory $emailTemplateModel,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magenest\UltimateFollowupEmail\Model\Aggregator\AbandonedCart $abandonedCart,
        \Magenest\UltimateFollowupEmail\Model\MailFactory $mailFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Url $urlInterface,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\SalesRule\Model\Coupon\Massgenerator $massGenerator,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_saleRuleFactory = $ruleFactory;
        $this->_totalsCollector = $totalsCollector;
        parent::__construct($context, $rulesFactory, $mailFactory, $messageFactory, $smsFactory, $quoteFactory, $cartRepositoryInterface, $emailTemplateModel, $scopeConfig, $urlInterface, $massGenerator, $appEmulation);
    }

    public function run()
    {
        $this->prepareOrderInfo($this->getEmailTarget());
        $this->createFollowUpEmail();
    }

    public function isValidate($rule)
    {
        $objectManager = ObjectManager::getInstance();
        /** @var \Magenest\UltimateFollowupEmail\Model\Serializer $serializer */
        $serializer = $objectManager->get('Magenest\UltimateFollowupEmail\Model\Serializer');
        /** @var Order $order */
        $order = $this->_emailTarget;
        $isValidate = $this->validateCustomerGroupAndWebsite($order->getCustomerGroupId(), $order->getStore()->getWebsiteId());
        if (!$isValidate) {
            return $isValidate;
        }
        $saleRuleModel = $this->_saleRuleFactory->create();

        $saleRuleModel->setData('conditions_serialized', $rule->getData('conditions_serialized'));
        try {
            $saleRuleModel->getConditions();
        } catch (\InvalidArgumentException $e) {
            $saleRuleModel->setData('conditions_serialized', json_encode($serializer->unserialize($rule->getData('conditions_serialized'))));
        }
        /** @var Quote $quote */
        $quote = $this->_quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
        $quote->collectTotals();
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        if (!$address->getTotalQty()) {
            $address->setTotalQty($quote->getItemsQty());
        }
        $isValidate = $saleRuleModel->validate($address);

        return $isValidate;
    }


    public function prepareMail()
    {
        $this->_vars = array_merge($this->_vars, [
            'order' => $this->_emailTarget,
            'customerFistName' => $this->_emailTarget->getCustomerFirstname(),
            'customerLastName' => $this->_emailTarget->getCustomerLastname(),
            'customerName' => $this->_emailTarget->getCustomerFirstname() . ' ' . $this->_emailTarget->getCustomerLastname(),
            'orderProductsGrid' => htmlspecialchars_decode($this->getOrderProductGridHtml()),
            'relatedProductsGrid' => htmlspecialchars_decode($this->getAllRelatedProductsGridHtml())
        ]);
    }

    public function getItemHtml(\Magento\Sales\Model\Order\Item $item)
    {
        if ($item->getParentItemId()) {
            return '';
        }
        $currencyCode = $item->getOrder()->getOrderCurrencyCode();
        $productId = $item->getProduct() ? $item->getProduct()->getId() : null;
        $product = ObjectManager::getInstance()->create(Product::class)->load($productId);
        $productImageUrl = $product->getMediaGalleryImages() ? $product->getMediaGalleryImages()->getFirstItem()->getUrl() : null;
        if ($children = $item->getChildrenItems()) {
            if ($childItem = reset($children)) {
                $childProduct = ObjectManager::getInstance()->create(Product::class)->load($childItem->getProductId());
                $productImageUrl = $childProduct->getMediaGalleryImages() ? $childProduct->getMediaGalleryImages()->getFirstItem()->getUrl() : null;
            }
        }
        $var = [
            'item' => $item,
            'itemProduct' => $product,
            'order' => $item->getOrder(),
            'item_price' => number_format($item->getPrice(), 2) . ' ' . $currencyCode,
            'product_image_url' => $productImageUrl
        ];
        return $this->getTemplateContent(self::XML_PATH_FUE_ORDER_ITEM, $var);
    }

    protected function getOrderProductGridHtml()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_emailTarget;
        $items = $order->getAllItems();
        $orderItemHtml = '';
        foreach ($items as $item) {
            $orderItemHtml .= $this->getItemHtml($item);
        }
        $orderItemsHtml = $this->getTemplateContent(self::XML_PATH_FUE_ORDER_ITEMS, [
            'order_items' => $orderItemHtml,
            'order' => $order
        ]);
        return $orderItemsHtml;
    }

    protected function getAllRelatedProductsGridHtml()
    {
        $items = $this->_emailTarget->getAllItems();
        $relatedProductGridHtml = '';
        foreach ($items as $item) {
            $relatedProductGridHtml .= $this->getRelatedProductsGridHtml($item->getProduct());
        }
        return $relatedProductGridHtml;
    }

    public function postCreateMail()
    {
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    protected function prepareOrderInfo($order)
    {
        $this->_emailTarget = $order;
        $customerEmail = $order->getCustomerEmail();

        /** @var $address Order\Address */
        if ($order->getIsVirtual()) {
            $address = $order->getBillingAddress();
        } else {
            $address = $order->getShippingAddress();
        }

        if ($order->getCustomerIsGuest()) {
            $firstName = $address->getFirstname();
            $lastName = $address->getLastname();
        } else {
            $firstName = $order->getCustomerFirstname();
            $lastName = $order->getCustomerLastname();
        }

        $mobileNumber = $address->getTelephone();

        $this->storeId = $order->getStoreId();
        $this->_emailTarget = $order;
        $this->_emailTarget->setData('customer_email', $customerEmail);
        $this->_emailTarget->setData('mobile_number', $mobileNumber);
        $this->_emailTarget->setData('recipient_name', $firstName . ' ' . $lastName);
        $this->_emailTarget->setData('customer_firstname', $firstName);
        $this->_emailTarget->setData('customer_lastname', $lastName);
    }
}
