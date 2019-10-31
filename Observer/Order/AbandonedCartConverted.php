<?php
namespace Magenest\UltimateFollowupEmail\Observer\Order;

use Magenest\UltimateFollowupEmail\Model\AbandonedCartFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Event\Observer as EventObserver;
use Psr\Log\LoggerInterface;

class AbandonedCartConverted implements ObserverInterface
{
    protected $abandonedCartFactory;

    public function __construct(
        AbandonedCartFactory $abandonedCartFactory
    ) {
        $this->abandonedCartFactory = $abandonedCartFactory;
    }

    public function execute(EventObserver $observer)
    {
        try {
            /** @var Quote $quote */
            $quote = $observer->getEvent()->getQuote();
            if ($quote) {
                $abandonedCart = $this->abandonedCartFactory->create();
                $abandonedCart->getResource()->load($abandonedCart, $quote->getId(), 'quote_id');
                if ($abandonedCart->getId()) {
                    $abandonedCart->addData([
                        'status' => $abandonedCart::STATUS_CONVERTED
                    ])->save();
                }
            }
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->critical($e);
        }
    }
}
