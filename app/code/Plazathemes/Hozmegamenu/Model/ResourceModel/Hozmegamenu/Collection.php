<?php
//app/code/Plazathemes/Hozmegamenu/Model/Resource/Hozmegamenu/Collection.php
namespace Plazathemes\Hozmegamenu\Model\ResourceModel\Hozmegamenu;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
 
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plazathemes\Hozmegamenu\Model\Hozmegamenu', 'Plazathemes\Hozmegamenu\Model\ResourceModel\Hozmegamenu');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
 
    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}