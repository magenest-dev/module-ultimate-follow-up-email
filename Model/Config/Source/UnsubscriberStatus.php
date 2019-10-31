<?php
namespace Magenest\UltimateFollowupEmail\Model\Config\Source;

class UnsubscriberStatus implements \Magento\Framework\Option\ArrayInterface
{
    const UNSUBSCRIBED = '0';
    const SUBSCRIBED = '1';

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::UNSUBSCRIBED,
                'label' => __('Un-subscribed'),
            ],
            [
                'value' => self::SUBSCRIBED,
                'label' => __('Re-subscribed'),
            ]
        ];
    }
}
