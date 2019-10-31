<?php

namespace Magenest\UltimateFollowupEmail\Observer\Creditmemo;

use Magenest\UltimateFollowupEmail\Model\Processor\OrderProcessor;
use Magenest\UltimateFollowupEmail\Model\Processor\OrderProcessorFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use \Magento\Sales\Model\Order as Order;

class ChangeStatus implements ObserverInterface
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
            $order_id = (int)$observer->getEvent()->getData('creditmemo')->getData('order_id');

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $order  = $objectManager->create(\Magento\Sales\Model\Order::class)->load($order_id);

            /** @var Order $order */

            $order->setState(Order::STATE_CLOSED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
            $order->save();

            /** @var OrderProcessor $orderProcessor */
            $orderProcessor = $this->orderProcessorFactory->create();
            $orderProcessor->setEmailTarget($order);
            $orderProcessor->setType('order_status_' . $order->getState());

            $orderProcessor->run();
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->critical($e);
        }
    }
}