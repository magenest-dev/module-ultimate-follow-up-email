<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 17/06/2016
 * Time: 14:53
 */

namespace Magenest\UltimateFollowupEmail\Observer\Layout;

use Magento\Customer\Model\Logger;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Mobile implements ObserverInterface
{

    /**
     * Logger of customer's log data.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * helper
     *
     * @var Logger
     */
    protected $heper;


    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger,
        \Magenest\UltimateFollowupEmail\Helper\Data $helperData
    ) {
        $this->logger = $logger;
        $this->heper  = $helperData;
    }


    /**
     * Handler for 'customer_logout' event.
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /*
            * @var $request \Magento\Framework\App\Request\Http\Proxy
        */
        $request  = $this->heper->getRequest();
        $pathInfo = $request->getPathInfo();
        $params   = $request->getParams();

        if ($pathInfo == '/customer/account/create/') {
            $layout   = $observer->getEvent()->getLayout();
            $is_allow = $this->heper->getIsEnableMobileInput();

            if ($is_allow) {
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('customer_account_create_mobile_input');
            }
        }
    }
}
