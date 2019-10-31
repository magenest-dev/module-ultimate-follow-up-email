<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 09/12/2016
 * Time: 09:46
 */

namespace Magenest\UltimateFollowupEmail\Model;

use Magenest\UltimateFollowupEmail\Model\ResourceModel\Link as ResourceModel;
use Magenest\UltimateFollowupEmail\Model\ResourceModel\Link\Collection;

class Link extends \Magento\Framework\Model\AbstractModel
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ResourceModel $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
