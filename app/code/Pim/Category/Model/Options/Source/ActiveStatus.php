<?php
/**
 *
 * @category  Embitel
 * @package   Embitel_Adds
 * @author    Deepak Kumar <deepak.kumar@embitel.com>
 * @copyright 2019 Embitel technologies (I) Pvt. Ltd
 */

namespace Pim\Category\Model\Options\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class ActiveStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = ['0' => '0', '1' => '1'];
        $options = [];
        foreach ($availableOptions as $key => $label) {
            $options[] = [
                'label' => $key,
                'value' => $label,
            ];
        }
        return $options;
    }
}
