<?php
namespace Magenest\UltimateFollowupEmail\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Nexmo extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_NEXMO_CONFIG_API_KEY    = 'ultimatefollowupemail/nexmo/api_key';
    const XML_PATH_NEXMO_CONFIG_API_SECRET = 'ultimatefollowupemail/nexmo/api_secret';
    const XML_PATH_NEXMO_CONFIG_FROM = 'ultimatefollowupemail/nexmo/from';

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $zendClientFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * Nexmo constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\ZendClientFactory $zendClientFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\ZendClientFactory $zendClientFactory
    ) {
        $this->zendClientFactory = $zendClientFactory;
        $this->scopeConfig       = $context->getScopeConfig();
    }


    /**
     * @param $sms
     * @return string
     * @throws \Exception
     */
    public function send($sms)
    {
        // "https://rest.nexmo.com/sms/json?api_key=6451aa3c&api_secret=b3127193aa9b4ad3&from=NEXMO&to=84985986898&text=Thank+you+for+buying"
        $apiKey    = $this->scopeConfig->getValue(self::XML_PATH_NEXMO_CONFIG_API_KEY, ScopeInterface::SCOPE_STORE);
        $apiSecret = $this->scopeConfig->getValue(self::XML_PATH_NEXMO_CONFIG_API_SECRET, ScopeInterface::SCOPE_STORE);
        $from = $this->scopeConfig->getValue(self::XML_PATH_NEXMO_CONFIG_FROM, ScopeInterface::SCOPE_STORE);

        $client = $this->zendClientFactory->create();

        $url     = 'https://rest.nexmo.com/sms/json';

        $content = $sms->getData('content');
        $to = $sms->getData('recipient_mobile');

        if (!$content || !$to) {
            throw new \Exception('no content or no recipient number');
        }

        $client->setUri($url);
        $client->setConfig([ 'timeout' => 300]);


        $client->setParameterPost('api_key', $apiKey);
        $client->setParameterPost('api_secret', $apiSecret);
        $client->setParameterPost('from', $from);
        $client->setParameterPost('api_key', $apiKey);
        $client->setParameterPost('to', $to);
        $client->setParameterPost('text', $content);


        $method   = \Zend_Http_Client::POST;
        $response = $client->request($method)->getBody();
        $response = json_decode($response, true);
        if (isset($response['messages'][0]['status'])) {
            if ($response['messages'][0]['status'] !== "0") {
                throw new \Exception('failed message from nexmo');
            }
        } else {
            throw new \Exception('could not read response from nexmo');
        }
        return $response;
    }
}
