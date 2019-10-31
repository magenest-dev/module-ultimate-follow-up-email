<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 19/01/2016
 * Time: 11:28
 */
namespace Magenest\UltimateFollowupEmail\Observer\Order;

use Magenest\UltimateFollowupEmail\Model\Processor\OrderProcessor;
use Magenest\UltimateFollowupEmail\Model\Processor\OrderProcessorFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class StatusChange implements ObserverInterface
{
    protected $orderProcessorFactory;

    public function __construct(
        OrderProcessorFactory $orderProcessorFactory
    ) {
        $this->orderProcessorFactory = $orderProcessorFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            /** @var  $order \Magento\Sales\Model\Order */
            $order = $observer->getEvent()->getOrder();
            /** @var OrderProcessor $orderProcessor */
            $orderProcessor = $this->orderProcessorFactory->create();
            $orderProcessor->setEmailTarget($order);
            $orderProcessor->setType('order_status_' . $order->getStatus());
            $orderProcessor->run();
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->critical($e);
        }
    }
}
