<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 15/10/2015
 * Time: 10:19
 */

namespace Magenest\UltimateFollowupEmail\Model;

use Magenest\UltimateFollowupEmail\Model\ResourceModel\Mail as ResourceModel;
use Magenest\UltimateFollowupEmail\Model\ResourceModel\Mail\Collection;
use Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status as MailStatus;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\ObjectManager;

class Mail extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'ultimatefollowupemail_mail';

    const XML_PATH_EMAIL_SENDER = 'ultimatefollowupemail/general/email_identity';


    /**
     * Types of template
     */
    const TYPE_TEXT = 1;

    const TYPE_HTML = 2;

    protected $_vars = [];

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Newsletter\Model\Queue\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magenest\UltimateFollowupEmail\Model\Mail\TransportBuilder
     */
    protected $_magenestTransportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_file;

    protected $_serializer;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ResourceModel $resource,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        Collection $resourceCollection,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Newsletter\Model\Queue\TransportBuilder $transportBuilder,
        \Magenest\UltimateFollowupEmail\Model\Mail\TransportBuilder $mntransportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer,
        array $data = []
    ) {

        $this->_encryptor = $encryptor;

        $this->_transportBuilder = $transportBuilder;

        $this->_magenestTransportBuilder = $mntransportBuilder;

        $this->_scopeConfig = $scopeConfig;


        $this->_storeManager = $storeManager;

        $this->_file = $file;

        $this->_serializer = $serializer;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    public function getIsRuleProcessed($rule_id, $key)
    {
        return $this->getResource()->getIsRuleProcessed($rule_id, $key);
    }


    public function getStoreId()
    {
        if ($this->getData('store_id')) {
            return $this->getData('store_id');
        } else {
            return 1;
        }
    }


    public function afterLoad()
    {
        // set var for the email to prepare for send
        parent::afterLoad();
        return $this;
    }


    public function getVars()
    {
        return $this->_vars;
    }


    public function setVars(array $var)
    {
        $this->_vars = $var;
    }


    /**
     * Send email
     */
    public function send()
    {
        try {
            $this->sendMail();
            $this->setStatus(MailStatus::STATUS_SENT);
            $this->setLog('Ok');
            $this->save();
        } catch (\Exception $e) {
            $this->setStatus(MailStatus::STATUS_FAILED);
            $this->setLog($e->getMessage());
            $this->save();
        }
    }


    protected function sendMail()
    {
        $attachments = [];

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $attachedFiles = $this->_serializer->unserialize($this->getData('attachments'));

        if (is_array($attachedFiles) && !empty($attachedFiles)) {
            $objectManager = ObjectManager::getInstance();
            /** @var \Magento\Framework\App\Filesystem\DirectoryList $dir */
            $dir = $objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');
            /** @var \Magento\Catalog\Model\Product\Media\Config $mediaConfig */
            $mediaConfig = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');
            $mediaPath = $dir->getPath('media');
            foreach ($attachedFiles as $attachFileTypes) {
                if (!is_array($attachFileTypes)) {
                    break;
                }
                foreach ($attachFileTypes as $file) {
                    if (!isset($file['file'])) {
                        continue;
                    }
                    $filepath = $mediaPath . '/' . $mediaConfig->getTmpMediaPath($file['file']);
                    $body = $this->_file->read($filepath);
                    if (!$body) {
                        \Magento\Framework\App\ObjectManager::getInstance()->create('Psr\Log\LoggerInterface')->debug('Could not read attachment file for mail '. $this->getId());
                        continue;
                    }
                    $attachments[] = [
                        'body' => $body,
                        'name' => $file['file'],
                        'label' => $file['label']
                    ];
                }
            }
        }

        $contextVar = $this->_serializer->unserialize($this->getData('context_vars'));

        if(isset($contextVar['customer_id'])){

            $customerId = $contextVar['customer_id'];

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $customer  = $objectManager->create(\Magento\Customer\Model\Customer::class)->load($customerId);

            $customerRegistry = $objectManager->create(\Magento\Customer\Model\CustomerRegistry::class);

            $dataProcessor = $objectManager->create(\Magento\Framework\Reflection\DataObjectProcessor::class);

            $mergedCustomerData = $customerRegistry->retrieveSecureData($customerId);
            $customerData = $dataProcessor->buildOutputDataArray($customer, \Magento\Customer\Api\Data\CustomerInterface::class);
            $mergedCustomerData->addData($customerData);
            $mergedCustomerData->setData('name', ucfirst($customer->getData('firstname')).' '.ucfirst($customer->getData('lastname')));
            $customerAddtionals = $mergedCustomerData;

            $contextVar['customer'] = $customerAddtionals;

            $this->getVars()['customer'] = $customerAddtionals;
        }

        $contextVar['store'] = $this->_storeManager->getStore((String)$this->getStoreId());

        $this->setVars($contextVar);

        $templateId = $this->getData('template_id');

        $this->_magenestTransportBuilder->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => (String)$this->getStoreId(),
            ]
        )->setTemplateVars(
            $this->getVars()
        )->setFrom(
            $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope)
        )->addTo(
            $this->getRecipientEmail(),
            $this->getRecipientName()
        );

        if ($bccMail = $this->getData('bcc_email')) {
            $this->_magenestTransportBuilder->addBcc($bccMail);
        }

        $this->_magenestTransportBuilder->setTemplateIdentifier((int)$templateId);

        /** @var  $transport \Magento\Framework\Mail\TransportInterface */
        if(method_exists($this->_magenestTransportBuilder->getMessage(),'createAttachment')) {
            if ($attachments) {
                foreach ($attachments as $attachment) {
                    if ($attachment) {
                        $this->_magenestTransportBuilder->createAttachment($attachment);
                    }
                }
            }
            $transport = $this->_magenestTransportBuilder->getTransport();
        }else{
            $transport = $this->_magenestTransportBuilder->getTransport();
            if ($attachments) {
                foreach ($attachments as $attachment) {
                    if ($attachment) {
                        $this->_magenestTransportBuilder->createAttachment($attachment,$transport);
                    }
                }
            }
        }

        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->_logger->critical('Email error '.$e);
        };

    }


    /**
     * cancel the email in the queue
     */
    public function cancel()
    {
        return $this->setStatus(MailStatus::STATUS_CANCELLED)->save();
    }


}
