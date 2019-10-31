<?php
namespace Magenest\UltimateFollowupEmail\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_AB_PERIOD = 'ultimatefollowupemail/abandoned_cart/period';

    const XML_PATH_CONFIG_MOBILE_ENABLE = 'ultimatefollowupemail/mobile/enable';

    const XML_PATH_CONFIG_MOBILE_REQUIRED = 'ultimatefollowupemail/mobile/is_required';

    protected $_type;

    protected $scopeConfig;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
    }


    /**
     * determine whether admin allow mobile input field in customer register form
     *
     * @return boolean
     */
    public function getIsEnableMobileInput()
    {
        $enable = $this->scopeConfig->getValue(self::XML_PATH_CONFIG_MOBILE_ENABLE);

        if ($enable == '1') {
            return true;
        } else {
            return false;
        }
    }


    /**
     * determine whether mobile is required field
     *
     * @return boolean
     */
    public function getIsMobileInputRequire()
    {
        $required = $this->scopeConfig->getValue(self::XML_PATH_CONFIG_MOBILE_REQUIRED);

        if ($required == '1') {
            return true;
        } else {
            return false;
        }
    }


    public function getAbandonedCartPeriod()
    {
        $timePeriod = $this->scopeConfig->getValue(self::XML_PATH_AB_PERIOD);

        if ($timePeriod == '' || $timePeriod == null) {
            $timePeriod = "60";
        }
        return $timePeriod;
    }


    public function getRequest()
    {
        return $this->_request;
    }


    public function getUltimateFollowupEmailTriggerGroup($trigger)
    {
        switch ($trigger) {
            case 'abandoned_cart':
            case 'order_is_placed':
            case 'order_status_pending_payment':
            case 'order_status_processing':
            case 'order_status_closed':
            case 'order_status_complete':
            case 'order_status_canceled':
            case 'order_status_holded':
            case 'order_status_payment_review':
            case 'order_updated_item':
            case 'order_product_review':
            case 'wishlist_reminder':
            case 'wishlist_back_in_stock':
            case 'wishlist_on_sale':
            case 'wishlist_shared':
            case 'wishlist_is_abandoned':
                return 'cart';
                break;
            case 'customer_registration':
            case 'customer_registration_no_purchase':
            case 'customer_birthday':
            case 'newsletter_subscribe':
            case 'newsletter_unsubscribe':
            case 'customer_no_activity':
                return 'customer';
                break;
            default:
                return 'cart';
        }
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }


    public function getCancelTrigger()
    {
        $cancel_trigger = [
            [
                'label' => __('Newsletter'),
                'optgroup-name' => __('Newsletter'),
                'value' => [
                    [
                        'value' => 'newsletter_subcribe',
                        'label' => __('Customer un-subscribe newsletter '),
                    ],

                ],
            ],

        ];

        if ($this->_type) {
            switch ($this->_type) {
                case 'order_status_new':
                case 'customer_status_complete':
                    $cancel_trigger[0]['value'][] = [
                        'value' => 'customer_status_complete',
                        'label' => __('Order obtained  status Complete'),
                    ];
                    break;
            }
        }

        return $cancel_trigger;
    }
}
