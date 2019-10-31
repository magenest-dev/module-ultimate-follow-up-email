<?php
namespace Magenest\UltimateFollowupEmail\Observer\Order;

use Magenest\UltimateFollowupEmail\Model\Processor\OrderProcessor;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderProductReview extends StatusChange
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();

            if ($order->getState() === Order::STATE_COMPLETE) {
                /** @var OrderProcessor $orderProcessor */
                $orderProcessor = $this->orderProcessorFactory->create();
                $orderProcessor->setEmailTarget($order);
                $orderProcessor->setType('order_product_review');
                $orderProcessor->run();
            }
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($e->getMessage());
        }
    }
}
