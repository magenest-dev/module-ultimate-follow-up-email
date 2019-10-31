<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 05/11/2015
 * Time: 14:30
 */

namespace Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail;

class Status
{
    const STATUS_QUEUED = 0;
    const STATUS_SENT = 2;
    const STATUS_FAILED = 3;
    const STATUS_CANCELLED = 4;


    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::STATUS_QUEUED => __('Queued'),
            self::STATUS_SENT => __('Sent'),
            self::STATUS_FAILED => __('Failed'),
            self::STATUS_CANCELLED => __('Cancelled'),
        ];
    }


    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = [
                'value' => $index,
                'label' => $value,
            ];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param  string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
