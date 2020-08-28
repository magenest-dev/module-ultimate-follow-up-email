<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 30/09/2015
 * Time: 13:56
 */

namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Rule;

use Magenest\UltimateFollowupEmail\Controller\Adminhtml\Rule;
use Magento\Framework\Controller\ResultFactory;

class Post extends Rule
{

    /**
     * @var  string
     */
    protected $_type;

    /**
     * @var  array
     */
    protected $_params;

    /**
     * @var  \Magenest\UltimateFollowupEmail\Model\MessageFactory
     */
    protected $smsFactory;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\UltimateFollowupEmail\Model\RuleFactory $ruleFactory,
        \Magenest\UltimateFollowupEmail\Helper\Data $helper,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magenest\UltimateFollowupEmail\Model\MessageFactory $messageFactory
    )
    {
        parent::__construct($context, $registry, $ruleFactory, $helper, $resultLayoutFactory);
        $this->smsFactory = $messageFactory;
    }


    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        /** @var \Magenest\UltimateFollowupEmail\Model\Serializer $serializer */
        $serializer = $this->_objectManager->get('Magenest\UltimateFollowupEmail\Model\Serializer');
        $params = $this->getRequest()->getParams();
        if (!isset($params['fue_type']) || !$params['fue_type']) {
            $this->messageManager->addError('Could not read rule type');
            return $resultRedirect->setPath('*/*');
        }
        $this->_type = $params['fue_type'];
        $this->_params = $params;

        if ($data = $this->getRequest()->getPostValue()) {
            $data['type'] = $this->_type;
            $data['website_id'] = $serializer->serialize($data['website_id']);

            $data['customer_group_id'] = $serializer->serialize($data['customer_group_id']);
            $email_chain = [];
            if (isset($data['email'])) {
                $emails = $data['email'];
                foreach ($emails as $email) {
                    if (isset($email['template']) && $email['template']) {
                        $template = $email['template'];

                        $day = isset($email['day'])?$email['day']:"";
                        $hour = isset($email['hour'])?$email['hour']:"";
                        $min = isset($email['min'])?$email['min']:"";

                        $email = [
                            'template' => $template,
                            'day' => $day,
                            'hour' => $hour,
                            'min' => $min,
                        ];

                        $email_chain[] = $email;
                    }
                }
            }

            // process the sms chain
            $smsIds = [];
            if (isset($data['sms']) && !empty($data['sms'])) {
                $smsIds = $this->processSMS($data['sms']);
            }

            // process the attached files
            $attachedData = $this->getRequest()->getParam('img');

            $refinedAttachedData = [];

            if (is_array($attachedData) && !empty($attachedData)) {
                foreach ($attachedData as $attach) {
                    $refinedAttachedData[] = $attach;
                }
            }

            $data['attached_files'] = $serializer->serialize($refinedAttachedData);

            $mediaAs = $this->getRequest()->getParam('product');

            if (isset($mediaAs['media_gallery']['images'])) {
                $images = $mediaAs['media_gallery']['images'];

                //example of $img
                /**
                 * $img=[
                 * 'position' => 1 ,
                 * 'file' => '/m/s/ms6_1.png.tmp' ,
                 * 'value_id' => '',
                 * 'label' =>' ',
                 * 'disabled' =>' ',
                 * 'media_type' => 'image',
                 * 'removed' => ''
                 * ]
                 *
                 */
                $data['attached_files'] = $serializer->serialize($images);
                $refineImages = [];

                foreach ($images as $key => $img) {
                    if ($img['removed'] != '1') {
                        $refineImages['images'][] = $img;
                    }
                }

                $data['attached_files'] = $serializer->serialize($refineImages);
            }


            // if there are old attached_files value
            try {
                if (empty($email_chain)) {
                    throw  (new \Exception('Email chain is required fields'));
                }

                $conditionsSerializedString = $this->getConditionToSave();
                $data['email_chain'] = $serializer->serialize($email_chain);

                $data['sms_chain'] = $serializer->serialize($smsIds);

                $data['conditions_serialized'] = $conditionsSerializedString;

                // incase there is not order related event we check other condition
                if (isset($data['condition'])) {
                    if (is_array($data['condition'])) {
                        $data['conditions_serialized'] = $serializer->serialize($data['condition']);
                    }
                }

                if (isset($data['coupon'])) {
                    if (!((is_numeric($data['coupon']['day']) || !$data['coupon']['day'])
                        && (is_numeric($data['coupon']['hour']) || !$data['coupon']['hour'])
                        && (is_numeric($data['coupon']['min']) || !$data['coupon']['min']))
                    ) {
                        throw new \Exception('Coupon available time is not numeric');
                    }
                    $couponTime = [
                        'day' => $data['coupon']['day'],
                        'hour' => $data['coupon']['hour'],
                        'minute' => $data['coupon']['min'],
                    ];
                    $data['coupon_time'] = $serializer->serialize($couponTime);
                }

                if (!$data['id']) {
                    unset($data['id']);
                }
                $data = $this->processAdditionalSettings($params, $data);
                $model = $this->ruleFactory->create()->setData($data);

                $model->save();

                $this->messageManager->addSuccess(__('You saved the follow up email rule.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, $e->getMessage());
            }
        }

        $resultRedirect->setPath('ultimatefollowupemail/rule/index');
        return $resultRedirect;
    }

    private function processAdditionalSettings($params, $data)
    {
        /** @var \Magenest\UltimateFollowupEmail\Model\Serializer $serializer */
        $serializer = $this->_objectManager->get('Magenest\UltimateFollowupEmail\Model\Serializer');
        if (isset($params['additional_settings'])) {
            $data['additional_settings'] = $serializer->serialize($params['additional_settings']);
        }
        return $data;
    }

    private function processWishlistReminderSetting($params, &$data)
    {
        /** @var \Magenest\UltimateFollowupEmail\Model\Serializer $serializer */
        $serializer = $this->_objectManager->get('Magenest\UltimateFollowupEmail\Model\Serializer');
        if (isset($params['wishlist_reminder_time'])) {
            $data['additional_settings'] = $serializer->serialize([
                'wishlist_reminder_time' => $params['wishlist_reminder_time']
            ]);
        }
    }

    /**
     * @param $smsData array
     * @return string
     */
    public function processSMS($smsData)
    {
        $smsOutPut = [];
        if (is_array($smsData) && !empty($smsData)) {
            foreach ($smsData as $sms) {
                $id = $sms['id'];
                // delete the deleted message
                if ($sms['is_deleted'] === '1') {
                    $sms = $this->smsFactory->create()->load($id);

                    if ($sms->getId()) {
                        $sms->delete();
                    }
                } else {
                    // edit the existing sms
                    if ($sms['is_new'] === '0') {
                        $sms = $this->smsFactory->create()->load($id);
                        if ($sms->getId()) {
                            $sms->addData(
                                [
                                    'content' => $sms['message'],
                                    'day' => $sms['day'],
                                    'hour' => $sms['hour'],
                                    'min' => $sms['min'],
                                ]
                            )->save();
                            $smsOutPut[] = $sms->getId();
                        }
                    } else {
                        $smsArr = [
                            'content' => $sms['message'],
                            'day' => $sms['day'],
                            'hour' => $sms['hour'],
                            'min' => $sms['min'],
                        ];
                        $sms = $this->smsFactory->create()->setData($smsArr)->save();
                        $smsOutPut[] = $sms->getId();
                    }
                }
            }
        }

        return $smsOutPut;
    }


    /**
     * @return string
     */
    public function getConditionToSave()
    {
        $type = $this->_type;

        $generalType = $this->heper->getUltimateFollowupEmailTriggerGroup($type);
        switch ($generalType) {
            case 'cart':
                $condition = $this->getOrderCondition();
                break;

            case 'customer':
                $condition = $this->getCustomerCondition();
                break;

            default:
                $condition = '';
        }

        return $condition;
    }


    /**
     * @return string
     */
    protected function getOrderCondition()
    {
        /*
            * @var  $catalogRule  \Magento\CatalogRule\Model\Rule
            */
        // $catalogRule =  $this->_objectManager->create('\Magento\CatalogRule\Model\Rule');
        $catalogRule = $this->_objectManager->create('\Magento\SalesRule\Model\Rule');

        $catalogRule->loadPost($this->_params['rule']);
        /** @var \Magenest\UltimateFollowupEmail\Model\Serializer $serializer */
        $serializer = $this->_objectManager->get('Magenest\UltimateFollowupEmail\Model\Serializer');
        $asArray = $catalogRule->getConditions()->asArray();
        $conditionsSerializedString = $serializer->serialize($asArray);
        return $conditionsSerializedString;
    }

    /**
     * @return string
     */
    protected function getCustomerCondition()
    {
        /** @var \Magenest\UltimateFollowupEmail\Model\Serializer $serializer */
        $serializer = $this->_objectManager->get('Magenest\UltimateFollowupEmail\Model\Serializer');
        $condition = $this->_params['condition'];
        $conditionsSerializedString = $serializer->serialize($condition);
        return $conditionsSerializedString;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_rule');
    }
}
