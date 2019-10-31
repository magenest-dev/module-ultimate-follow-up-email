<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 15/01/2016
 * Time: 16:40
 */

namespace Magenest\UltimateFollowupEmail\Observer\Layout;

use Magento\Customer\Model\Logger;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Cancel implements ObserverInterface
{

    /**
     * Logger of customer's log data.
     *
     * @var Logger
     */
    protected $logger;


    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }


    /**
     * Handler for 'customer_logout' event.
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $type        = $observer->getEvent()->getType();
        $cancelValue = $observer->getEvent()->getCancelValue();

        if ($type == 'abandoned_cart') {
            $cancelValue[0]['value'][] = [
                                          'value' => 'test',
                                          'label' => __('Customer placed order'),
                                         ];
        }
    }
}
