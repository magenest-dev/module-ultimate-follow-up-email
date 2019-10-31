<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Mail;

use Magenest\UltimateFollowupEmail\Model\Mail;

class Preview extends \Magento\Newsletter\Block\Adminhtml\Queue\Preview
{

    protected $_mailFactory;


    /**
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param \Magento\Newsletter\Model\TemplateFactory         $templateFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory       $subscriberFactory
     * @param \Magento\Newsletter\Model\QueueFactory            $queueFactory
     * @param \Magenest\UltimateFollowupEmail\Model\MailFactory $mailFactory
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Newsletter\Model\TemplateFactory $templateFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Newsletter\Model\QueueFactory $queueFactory,
        \Magenest\UltimateFollowupEmail\Model\MailFactory $mailFactory,
        array $data = []
    ) {
        $this->_queueFactory = $queueFactory;
        $this->_mailFactory  = $mailFactory;
        parent::__construct($context, $templateFactory, $subscriberFactory, $queueFactory, $data);
    }


    /**
     * @param \Magento\Newsletter\Model\Template $template
     * @param string                             $id
     * @return $this
     */
    protected function loadTemplate(\Magento\Newsletter\Model\Template $template, $id)
    {
        /** @var Mail $mail */
        $mail = $this->_mailFactory->create()->load($id);

        // $template->setTemplateType();
        $template->setTemplateText(htmlspecialchars_decode($mail->getPreviewContent()));
        $template->setTemplateStyles($mail->getStyles());
        return $this;
    }
}
