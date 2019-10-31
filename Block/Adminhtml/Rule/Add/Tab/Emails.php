<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 30/09/2015
 * Time: 10:17
 */

namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Rule\Add\Tab;

use Magento\Config\Model\Config\Source\Email\Identity as EmailIdentity;

// use Magento\Config\Model\Config\Source\Email\Template as EmailTemplate;
use Magenest\UltimateFollowupEmail\Model\System\Email\Template as EmailTemplate;
use Symfony\Component\Config\Definition\Exception\Exception;

class Emails extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    protected $type;

    protected $_emailIdentity;

    protected $_emailTemplate;

    protected $__nameInLayout = "ultimatefollowupemail_rule_new_tab_emails";

    protected $serializer;

    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        EmailIdentity $identity,
        EmailTemplate $emailTemplate,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;

        $this->_rendererFieldset->setData('papaClass', $this);

        $this->_emailIdentity = $identity;

        $this->_emailTemplate = $emailTemplate;
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Email Chain');
    }


    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Emails');
    }


    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }


    /**
     * @return array
     */
    public function getEmailTemplates()
    {
        $options = $this->_emailTemplate->toOptionArray();

        return $options;
    }


    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }


    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model      = $this->_coreRegistry->registry('current_fue_rule');
        $this->type = $this->_coreRegistry->registry('type');

        $attachFiles = $this->serializer->unserialize($model->getData('attached_files'));

        $followupEmailRule = $this->_coreRegistry->registry('current_fue_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $uploadAttachUrl = $this->getUrl('ultimatefollowupemail/rule/upload');

        $renderer = $this->_rendererFieldset->setTemplate(
            'Magenest_UltimateFollowupEmail::rule/emailChain.phtml'
        )->setNewChildUrl(
            $this->getUrl('sales_rule/promo_quote/newConditionHtml/form/rule_conditions_fieldset')
        );
        $renderer->setData('uploadUrl', $uploadAttachUrl);

        $form->addFieldset(
            'email_chains',
            [
             'legend' => ''
            ]
        )->setRenderer(
            $renderer
        );

        $form->addField(
            'required-email-chain',
            'hidden',
            [
                'name'=> 'required-email-chain',
                'class' => 'email-chain-required-entry',
            ]
        );

        $renderer->setData('type', $this->type);

        $renderer->setData('attached_files', $attachFiles);

        $renderer->setData('options', $this->getEmailTemplates());


        if ($this->getRequest()->getParam('id')) {
            $editData = $followupEmailRule->getData();
            $renderer->setData('emails', $this->serializer->unserialize($followupEmailRule->getData('email_chain')));

            if ($editData['website_id']) {
                $editData['website_id'] = $this->serializer->unserialize($editData['website_id']);
            }

            if ($editData['customer_group_id']) {
                $editData['customer_group_id'] = $this->serializer->unserialize($editData['customer_group_id']);
            }

            $editData['id'] = $this->getRequest()->getParam('id');
            $form->setValues($editData);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
