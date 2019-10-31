<?php
namespace Magenest\UltimateFollowupEmail\Observer\Order;

use Magenest\UltimateFollowupEmail\Model\MailFactory;
use Magenest\UltimateFollowupEmail\Model\RuleFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\Observer as EventObserver;
use Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status;

class RebuyAbandonedCart implements ObserverInterface
{
    protected $mailFactory;

    protected $ruleFactory;

    public function __construct(
        MailFactory $mailFactory,
        RuleFactory $ruleFactory
    ) {
        $this->mailFactory = $mailFactory;
        $this->ruleFactory = $ruleFactory;
    }

    public function execute(EventObserver $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getState() === Order::STATE_NEW) {
            $rules = $this->ruleFactory->create()
                ->getCollection()
                ->addFieldToFilter('type', 'abandoned_cart')
                ->getAllIds();
            if (!empty($rules)) {
                $abandonedCartMails = $this->mailFactory->create()->getCollection()
                    ->addFieldToFilter('status', Status::STATUS_QUEUED)
                    ->addFieldToFilter('rule_id', ['IN' => $rules])
                    ->addFieldToFilter('duplicated_key', $order->getQuoteId());
                foreach ($abandonedCartMails as $mail) {
                    if ($mail->getId()) {
                        $mail->addData([
                            'status' => Status::STATUS_CANCELLED,
                            'log' => 'Customer Re-bought Abandoned Cart'
                        ]);
                        $mail->save();
                    }
                }
            }
        }
    }
}
