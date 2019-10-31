<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 30/09/2015
 * Time: 15:38
 */
namespace Magenest\UltimateFollowupEmail\Model\Processor;

interface ProcessorInterface
{


    public function run();


    public function generateUltimateFollowupEmail();


    public function getType();


    public function getMatchingRule();
}
