<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 29/09/2015
 * Time: 23:46
 */
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Rule\Add\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;

use Magenest\UltimateFollowupEmail\Model\RuleFactory;
use Magento\Store\Model\System\Store;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Customer\Api\GroupRepositoryInterface;

class Main extends Generic implements TabInterface
{

    /**
     * @var \Magenest\UltimateFollowupEmail\Model\RuleFactory
     */
    protected $_salesRule;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\Convert\Object
     */
    protected $_objectConverter;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var  \Magenest\UltimateFollowupEmail\Helper\Data
     */
    protected $_helper;

    protected $serializer;

    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleFactory $salesRule,
        ObjectConverter $objectConverter,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magenest\UltimateFollowupEmail\Helper\Data $helper,
        Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_salesRule   = $salesRule;

        $this->_objectConverter       = $objectConverter;
        $this->groupRepository        = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serializer = $serializer;
        $this->_helper = $helper;

        parent::__construct($context, $registry, $formFactory, $data);
    }


    public function getTabLabel()
    {
        return __('Rule Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Rule Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $type = $this->_coreRegistry->registry('type');
        $followupEmailRule = $this->_coreRegistry->registry('current_fue_rule');
        $fieldset          = $form->addFieldset('base_fieldset', ['legend' => __('Basic Information')]);

        $fieldset->addField(
            'id',
            'hidden',
            [
             'name'  => 'id',
             'label' => __('Rule Id'),
            ]
        );

        $fieldset->addField(
            'name',
            'text',
            [
             'name'     => 'name',
             'label'    => __('Rule Name'),
             'title'    => __('Rule Name'),
             'required' => true,
            ]
        );

        $fieldset->addField(
            'description',
            'text',
            [
             'name'  => 'description',
             'label' => __('Description'),
             'title' => __('Description'),
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
             'label'    => __('Status'),
             'required' => true,
             'name'     => 'status',
             'values'   => [
                            [
                             'label' => __('Select status'),
                             'value' => '',

                            ],                            [
                                                           'label' => __('Inactive'),
                                                           'value' => '0',

                                                          ],                            [
                                                                                         'label' => __('Active'),
                                                                                         'value' => '1',
                                                                                        ],
                           ],
            ]
        );

        // add the cancel event to the rule
        $event = $this->_coreRegistry->registry('type') ? $this->_coreRegistry->registry('type') : 'abandoned_cart';

        $cancel_trigger = [
                           [
                            'label'         => __('None'),
                            'optgroup-name' => __('None'),
                            'value'         => [
                                                [
                                                 'value' => 'not-cancel',
                                                 'label' => __('Do not cancel event automatically'),
                                                ],

                                               ],
                           ],
                           [
                            'label'         => __('Newsletter'),
                            'optgroup-name' => __('Newsletter'),
                            'value'         => [
                                                [
                                                 'value' => 'newsletter_subcribe',
                                                 'label' => __('Customer un-subscribe newsletter'),
                                                ],

                                               ],
                           ],

                          ];

        $this->_eventManager->dispatch('followup_email_cancel_trigger', ['cancel_value' => $cancel_trigger, 'type' => $event]);

//        $fieldset->addField(
//            'cancel_serialized',
//            'select',
//            [
//             'label'  => __('Cancel when following event occurs'),
//                'name'     => 'cancel_serialized',
//             'values' => &$cancel_trigger,
//
//            ]
//        );

        if ($this->_storeManager->isSingleStoreMode()) {
            $websiteId = $this->_storeManager->getStore(true)->getWebsiteId();
            $fieldset->addField('website_id', 'hidden', ['name' => 'website_id[]', 'value' => $websiteId]);
            // $model->setWebsiteIds($websiteId);
        } else {
            $field    = $fieldset->addField(
                'website_id',
                'multiselect',
                [
                 'name'     => 'website_id[]',
                 'label'    => __('Websites'),
                 'title'    => __('Websites'),
                 'required' => true,
                 'values'   => $this->_systemStore->getWebsiteValuesForForm(),
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        }

        $groups = $this->groupRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
        $fieldset->addField(
            'customer_group_id',
            'multiselect',
            [
             'name'     => 'customer_group_id[]',
             'label'    => __('Customer Groups'),
             'title'    => __('Customer Groups'),
             'required' => true,
             'values'   => $this->_objectConverter->toOptionArray($groups, 'id', 'code'),
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldset->addField(
            'from_date',
            'date',
            [
             'name'         => 'from_date',
             'label'        => __('From'),
             'title'        => __('From'),
                'readonly'=>true,
             'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
             'date_format'  => $dateFormat,
            ]
        );

        $fieldset->addField(
            'to_date',
            'date',
            [
             'name'         => 'to_date',
             'label'        => __('To'),
                'readonly'=>true,
             'title'        => __('To'),
             'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
             'date_format'  => $dateFormat,
            ]
        );
        $this->setForm($form);
        if ($this->getRequest()->getParam('id')) {
            $editData = $followupEmailRule->getData();

            if ($editData['website_id']) {
                $editData['website_id'] = $this->serializer->unserialize($editData['website_id']);
            }

            if ($editData['customer_group_id']) {
                $editData['customer_group_id'] = $this->serializer->unserialize($editData['customer_group_id']);
            }

            $editData['type'] = $type;

            $editData['id'] = $this->getRequest()->getParam('id');
            $form->setValues($editData);
        }

        return parent::_prepareForm();
    }

    protected function getCancelTrigger($type)
    {
    }
}
