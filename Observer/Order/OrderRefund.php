<?php
namespace Magenest\UltimateFollowupEmail\Observer\Order;

use Magenest\UltimateFollowupEmail\Model\MailFactory;
use Magenest\UltimateFollowupEmail\Model\RuleFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Email\Model\Template as EmailTemplateModel;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\Observer as EventObserver;
use Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status;

class OrderRefund implements ObserverInterface
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
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getCreditmemo();
        $order = $creditmemo->getOrder();
        if ($order->getId()) {
            $rules = $this->ruleFactory->create()
                ->getCollection()
                ->addFieldToFilter('type', 'order_product_review')
                ->getAllIds();
            if (!empty($rules)) {
                $mail = $this->mailFactory->create()->getCollection()
                    ->addFieldToFilter('status', Status::STATUS_QUEUED)
                    ->addFieldToFilter('rule_id', ['IN' => $rules])
                    ->addFieldToFilter('duplicated_key', $order->getIncrementId())
                    ->getFirstItem();
                if ($mail->getId()) {
                    $mail->setData('status', Status::STATUS_CANCELLED);
                    $mail->save();
                }
            }
        }
    }
}
