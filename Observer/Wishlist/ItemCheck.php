<?php
namespace Magenest\UltimateFollowupEmail\Observer\Wishlist;

use Magenest\UltimateFollowupEmail\Model\Mail;
use Magenest\UltimateFollowupEmail\Model\MailFactory;
use Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order;

class ItemCheck implements ObserverInterface
{
    protected $mailFactory;

    public function __construct(
        MailFactory $mailFactory
    ) {
    
        $this->mailFactory = $mailFactory;
    }

    public function execute(EventObserver $observer)
    {
        try {
            /** @var  \Magento\Sales\Model\Order\Item $orderItem */
            $orderItem = $observer->getEvent()->getItem();

            /** @var Order $order */
            $order = $orderItem->getOrder();

            if (!$order->getCustomerId()) {
                return $this;
            }
            $customerId = $order->getCustomerId();
            $key = implode(",", [$customerId, $orderItem->getProductId()]);
            /** @var Mail $mail */
            $mail = $this->mailFactory->create()
                ->getCollection()
                ->addFieldToFilter('duplicated_key', $key)
                ->addFieldToFilter('status', Status::STATUS_QUEUED)
                ->getFirstItem();
            if ($mail->getId()) {
                $mail->setData('status', Status::STATUS_CANCELLED);
                $mail->save();
            }
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('ItemCheck Observer Error: ' . $e->getMessage());
        }
    }
}
