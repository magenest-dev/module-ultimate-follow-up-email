<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 15/02/2017
 * Time: 18:34
 */

namespace Magenest\UltimateFollowupEmail\Model;

class Guest extends \Magento\Framework\Model\AbstractModel
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Guest $resource,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Guest\Collection $resourceCollection,
        array $data = []
    ) {
    

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
