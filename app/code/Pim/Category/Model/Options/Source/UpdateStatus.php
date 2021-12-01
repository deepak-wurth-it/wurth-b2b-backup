<?php
/**
 *
 * @category  Pim
 * @package   Pim_Attribute
 * @author    Deepak Kumar <deepak.kumr.rai@wurth-it.in>
 */

namespace Pim\Category\Model\Options\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class UpdateStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = ['Update Done' => '1', 'Update Pending' => '0'];
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
