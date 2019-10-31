<?php
namespace Magenest\UltimateFollowupEmail\Model;

class Status
{
    const IS_ACTIVE = 1;

    const IS_INACTIVE = 0;


    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::IS_ACTIVE => __('Active'),
            self::IS_INACTIVE => __('Inactive'),
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
}
