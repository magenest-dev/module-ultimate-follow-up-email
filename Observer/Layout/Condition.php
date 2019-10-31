<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 17/11/2015
 * Time: 15:27
 */
namespace Magenest\UltimateFollowupEmail\Observer\Layout;

use Magento\Customer\Model\Logger;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Condition implements ObserverInterface
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
        \Magenest\UltimateFollowupEmail\Helper\Data $data
    ) {
        $this->logger = $logger;
        $this->heper  = $data;
    }


    /**
     * Handler for 'customer_logout' event.
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $request \Magento\Framework\App\Request\Http\Proxy */
        $request  = $this->heper->getRequest();
        $pathInfo = $request->getPathInfo();
        $params   = $request->getParams();

        if ((strpos($pathInfo, 'ultimatefollowupemail') !== false) && isset($params['type'])) {
            $type         = $params['type'];
            $triggerGroup = $this->classifyTrigger($type);

            if ($triggerGroup == 'cart') {
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('ultimatefollowupemail_rule_new_ab');
            } elseif ($triggerGroup == 'customer') {
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('ultimatefollowupemail_rule_new_birthday');
            }
        }
    }


    /**
     * @param $type
     * @return string
     */
    public function classifyTrigger($type)
    {
        $group = $this->heper->getUltimateFollowupEmailTriggerGroup($type);
        return $group;
    }
}
