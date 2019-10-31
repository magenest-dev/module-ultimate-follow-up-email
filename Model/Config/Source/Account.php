<?php
namespace Magenest\UltimateFollowupEmail\Model\Config\Source;

class Account implements \Magento\Framework\Option\ArrayInterface
{
    protected $mandrillConnector;
    protected $info;

    /**
     * @param \Magenest\UltimateFollowupEmail\Helper\MandrillConnector $mandrillConnector
     */
    public function __construct(\Magenest\UltimateFollowupEmail\Helper\MandrillConnector $mandrillConnector)
    {
        $this->mandrillConnector = $mandrillConnector;
        $this->info = $this->mandrillConnector->getUserInformation();
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (is_array($this->info)) {
            return [
                    [
                     'value' => 'User Name',
                     'label' => $this->info['username'],
                    ],
                    [
                     'value' => 'Reputation',
                     'label' => $this->info['reputation'],
                    ],
                    [
                     'value' => 'Hourly Quota',
                     'label' => $this->info['hourly_quota'],
                    ],
                    [
                     'value' => 'Backlog',
                     'label' => $this->info['backlog'],
                    ],
                   ];
        } else {
            return [
                    [
                     'value' => 'Mandrill Exception:',
                     'label' => $this->info,
                    ],
                   ];
        }
    }
}
