<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 22/10/2015
 * Time: 15:04
 */

namespace Magenest\UltimateFollowupEmail\Model\System\Email;

use Magento\Config\Model\Config\Source\Email\Template as EmailTemplate;

class Template extends \Magento\Config\Model\Config\Source\Email\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $_emailConfig;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $_templatesFactory;


    /**
     * @param \Magento\Framework\Registry                                   $coreRegistry
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory
     * @param \Magento\Email\Model\Template\Config                          $emailConfig
     * @param array                                                         $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    ) {
        parent::__construct($coreRegistry, $templatesFactory, $emailConfig);
        $this->_coreRegistry     = $coreRegistry;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig      = $emailConfig;
    }


    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        /*
            * @var $collection \Magento\Email\Model\ResourceModel\Template\Collection
        */
        if (!($collection = $this->_coreRegistry->registry('config_system_email_template'))) {
            $collection = $this->_templatesFactory->create();
            $collection->load();
            $this->_coreRegistry->register('config_system_email_template', $collection);
        }

        $options    = $collection->toOptionArray();
        $templateId = str_replace('/', '_', $this->getPath());

        if ($templateId) {
            $templateLabel = $this->_emailConfig->getTemplateLabel($templateId);
            $templateLabel = __('%1 (Default)', $templateLabel);
            array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);
        }

        return $options;
    }
}
