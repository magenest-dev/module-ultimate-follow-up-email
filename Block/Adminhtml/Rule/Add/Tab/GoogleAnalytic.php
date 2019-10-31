<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 01/02/2016
 * Time: 16:15
 */

namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Rule\Add\Tab;

use Magento\Framework\App\ObjectManager;

class GoogleAnalytic extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{


    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('Google Analytics');
    }


    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return __('Google Analytics');
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
        $objectManager = ObjectManager::getInstance();
        $serializer = $objectManager->get('Magenest\UltimateFollowupEmail\Model\Serializer');
        $type = $this->_coreRegistry->registry('type');
        /*
            * @var \Magento\Framework\Data\Form $form
        */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $type              = $this->_coreRegistry->registry('type');
        $followupEmailRule = $this->_coreRegistry->registry('current_fue_rule');
        $fieldset          = $form->addFieldset('google_analytics_fieldset', ['legend' => __('Google Analytics Campaign')]);

        $fieldset->addField(
            'ga_source',
            'text',
            [
             'name'  => 'ga_source',
             'label' => __('Campaign Source'),
             'title' => __('Campaign Source'),
             'note'  => __('Google analytics Source'),
            ]
        );

        $fieldset->addField(
            'ga_medium',
            'text',
            [
             'name'  => 'ga_medium',
             'label' => __('Campaign Medium'),
             'title' => __('Campaign Medium'),
             'note'  => __('Google analytics medium'),
            ]
        );

        $fieldset->addField(
            'ga_name',
            'text',
            [
             'name'  => 'ga_name',
             'label' => __('Campaign Name'),
             'title' => __('Campaign Name'),
             'note'  => __('Google analytics name'),
            ]
        );

        $fieldset->addField(
            'ga_term',
            'text',
            [
             'name'  => 'ga_term',
             'label' => __('Campaign Term'),
             'title' => __('Campaign Term'),
             'note'  => __('Google analytics name'),
            ]
        );

        $fieldset->addField(
            'ga_content',
            'text',
            [
             'name'  => 'ga_content',
             'label' => __('Campaign Content'),
             'title' => __('Campaign Content'),
             'note'  => __('Google analytics name'),
            ]
        );
        $this->setForm($form);
        if ($this->getRequest()->getParam('id')) {
            $editData = $followupEmailRule->getData();

            if ($editData['website_id']) {
                $editData['website_id'] = $serializer->unserialize($editData['website_id']);
            }

            if ($editData['customer_group_id']) {
                $editData['customer_group_id'] = $serializer->unserialize($editData['customer_group_id']);
            }

            $editData['type'] = $type;

            $editData['id'] = $this->getRequest()->getParam('id');
            $form->setValues($editData);
        }

        return parent::_prepareForm();
    }
}
