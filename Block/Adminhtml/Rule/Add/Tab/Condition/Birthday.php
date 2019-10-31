<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 03/12/2015
 * Time: 13:44
 */
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Rule\Add\Tab\Condition;

class Birthday extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Return Tab label
     *
     * @return string
     * @api
     */

    protected $serializer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer,
        array $data = [])
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->serializer = $serializer;
    }

    public function getTabLabel()
    {
        return __('Condition');
    }


    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return __('Condition');
    }


    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }


    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return false;
    }


    protected function _prepareForm()
    {
        /** @var \Magenest\UltimateFollowupEmail\Model\Serializer $serializer */
        $type = $this->_coreRegistry->registry('type');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $editData = [];

        $followupEmailRule = $this->_coreRegistry->registry('current_fue_rule');

        if ($this->getRequest()->getParam('id')) {
            $editData = $followupEmailRule->getData();

            if (isset($editData['website_id'])) {
                $editData['website_id'] = $this->serializer->unserialize($editData['website_id']);
            }

            if (isset($editData['customer_group_id'])) {
                $editData['customer_group_id'] = $this->serializer->unserialize($editData['customer_group_id']);
            }

            if (isset($editData['additional_settings'])) {
                try {
                    $editData['additional_settings'] = $this->serializer->unserialize($editData['additional_settings']);
                    foreach ($editData['additional_settings'] as $key => $value) {
                        $editData[$key] = $value;
                    }
                } catch (\Exception $e) {
                }
            }

            $gender = '';
            if ($editData['conditions_serialized']) {
                $editData['condition'] = $this->serializer->unserialize($editData['conditions_serialized']);
            }

            if (isset($editData['condition']['gender'])) {
                $gender = $editData['condition']['gender'];
            }

            $editData['id'] = $this->getRequest()->getParam('id');
        } else {
            $gender = '';
        }
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Condition')]);

//        if ($type === 'customer_no_activity') {
//            $fieldset->addField(
//                'customer_no_activity_time',
//                'text',
//                [
//                    'name' => 'additional_settings[customer_no_activity_time]',
//                    'label' => __('Customers have no activity in (x) hours'),
//                    'after_element_html' => '(Leave blank for 24 hours)'
//                ]
//            );
//        }

        $fieldset->addField(
            'condition_gender',
            'select',
            [
                'label' => __('Gender'),
                'required' => false,
                'name' => 'condition[gender]',
                'data-role' => 'attach-value',
                'data-action' => $gender,
                'values' => [
                    [
                        'label' => __('Any'),
                        'value' => '',

                    ], [
                        'label' => __('Male'),
                        'value' => '1',

                    ], [
                        'label' => __('Female'),
                        'value' => '2',
                    ],
                ],
            ]
        );

        $this->setForm($form);

        $form->setValues($editData);

        return parent::_prepareForm();
    }
}
