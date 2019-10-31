<?php
/**
 * Created by PhpStorm.
 * User: hoanbk
 * Date: 28/06/2019
 * Time: 09:47
 */

namespace Magenest\UltimateFollowupEmail\Model;


class Serializer
{
    public function __construct(
        \Magento\Framework\Unserialize\Unserialize $unserial,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface
    ){
        $this->unserial = $unserial;
        if (interface_exists(\Magento\Framework\Serialize\SerializerInterface::class)) {
            $this->serialize = $objectManagerInterface->get(\Magento\Framework\Serialize\SerializerInterface::class);
        }

    }

    private $serialize;
    private $unserial;

    public function serialize($val) {
        if ($this->serialize === null) {
            //@codingStandardsIgnoreStart
            return serialize($val);
            //@codingStandardsIgnoreEnd
        }
        return $this->serialize->serialize($val);
    }

    public function unserialize($val) {
        if ($this->serialize === null) {
            return $this->unserial->unserialize($val);
        }
        try {
            return $this->serialize->unserialize($val);
        } catch (\InvalidArgumentException $e) {
            //@codingStandardsIgnoreStart
            return unserialize($val);
            //@codingStandardsIgnoreEnd
        }
    }
}