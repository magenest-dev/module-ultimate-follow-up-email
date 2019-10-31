<?php
namespace Magenest\UltimateFollowupEmail\Controller\Capture;

class Guest extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;

    protected $guestFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magenest\UltimateFollowupEmail\Model\GuestFactory $guestFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->guestFactory = $guestFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (isset($params['email'])) {
            $email = $params['email'];
            $quoteId = $this->checkoutSession->getQuoteId();
            $guest = $this->guestFactory->create()->load($quoteId, 'quote_id');
            $guest->addData(['email' => $email, 'quote_id' => $quoteId])->save();
        }
    }
}
