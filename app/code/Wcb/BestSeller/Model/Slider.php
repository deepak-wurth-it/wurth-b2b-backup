<?php

namespace Wcb\BestSeller\Model;

use DateTime;
use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;

/**
 * @method Slider setName($name)
 * @method Slider setStoreViews($storeViews)
 * @method Slider setActiveFrom($activeFrom)
 * @method Slider setActiveTo($activeTo)
 * @method Slider setStatus($status)
 * @method Slider setSerializedData($serializedData)
 * @method mixed getName()
 * @method mixed getStoreViews()
 * @method mixed getActiveFrom()
 * @method mixed getActiveTo()
 * @method mixed getStatus()
 * @method mixed getSerializedData()
 * @method Slider setCreatedAt(string $createdAt)
 * @method string getCreatedAt()
 * @method Slider setUpdatedAt(string $updatedAt)
 * @method string getUpdatedAt()
 */
class Slider extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'wcb_bestseller_slider';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'wcb_bestseller_slider';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'wcb_bestseller_slider';

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param DataObject $dataObject
     *
     * @return array|bool
     * @throws Exception
     */
    public function validateData(DataObject $dataObject)
    {
        $result = [];
        $fromDate = $toDate = null;

        if ($dataObject->hasFromDate() && $dataObject->hasToDate()) {
            $fromDate = $dataObject->getFromDate();
            $toDate = $dataObject->getToDate();
        }

        if ($fromDate && $toDate) {
            $fromDate = new DateTime($fromDate);
            $toDate = new DateTime($toDate);

            if ($fromDate > $toDate) {
                $result[] = __('End Date must follow Start Date.');
            }
        }

        return !empty($result) ? $result : true;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wcb\BestSeller\Model\ResourceModel\Slider');
    }
}
