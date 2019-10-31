<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 17/06/2016
 * Time: 13:57
 */
namespace Magenest\UltimateFollowupEmail\Block\Form\Customer;

class Mobile extends \Magento\Framework\View\Element\Template
{

    protected $helper;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magenest\UltimateFollowupEmail\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $dataHelper;
    }


    public function isMobileRequired()
    {
        return $this->helper->getIsMobileInputRequire();
    }
}
