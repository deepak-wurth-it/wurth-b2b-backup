<?php
/**
 *
 * @category  Wcb
 * @package   Wcb_Store
 * @author    Deepak Kumar <deepak.kumar.rai@wuerth-it.com>
 * @copyright 2019 Wcb technologies (I) Pvt. Ltd
 */
namespace Wcb\Store\Block\Adminhtml\Store;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label' => __('Delete Store'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Store will be deleted permanently'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['entity_id' => $this->getId()]);
    }
}
