<?php

namespace Wcb\PromotionBanner\Model;

use Wcb\PromotionBanner\Api\Data\PromotionBannerInterface;

class PromotionBanner extends \Magento\Framework\Model\AbstractModel implements PromotionBannerInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wurth_promotionbanners';

    /**
     * @var string
     */
    protected $_cacheTag = 'wurth_promotionbanners';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wurth_promotionbanners';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Wcb\PromotionBanner\Model\ResourceModel\PromotionBanner');
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set EntityId.
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get Title.
     *
     * @return varchar
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set Title.
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }


    /**
     * Get UpdateTime.
     *
     * @return varchar
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set UpdateTime.
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }


   /**
    * Get Image.
    *
    * @return varchar
    */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

   /**
    * Set Image.
    */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
    * Get Position.
    *
    * @return varchar
    */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

   /**
    * Set Position.
    */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }
    
    /**
    * Get Status.
    *
    * @return varchar
    */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

   /**
    * Set Status.
    */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
    
   /**
    * Get Url.
    *
    * @return varchar
    */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

   /**
    * Set Url.
    */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

   /**
    * Get Target.
    *
    * @return varchar
    */
    public function getTarget()
    {
        return $this->getData(self::TARGET);
    }

   /**
    * Set Target.
    */
    public function setTarget($target)
    {
        return $this->setData(self::TARGET, $target);
    }
  
    /**
    * Get ValidFrom.
    *
    * @return varchar
    */
    public function getValidFrom()
    {
        return $this->getData(self::VALID_FROM);
    }

    /**
    * Set ValidFrom.
    */
    public function setValidFrom($validFrom)
    {
        return $this->setData(self::VALID_FROM, $validFrom);
    }

    /**
    * Get ValidTo.
    *
    * @return varchar
    */
    public function getValidTo()
    {
        return $this->getData(self::VALID_TO);
    }

   /**
    * Set ValidTo.
    */
    public function setValidTo($validTo)
    {
        return $this->setData(self::VALID_TO, $validTo);
    }

    /**
    * Get CustomerGroup.
    *
    * @return varchar
    */
    public function getCustomerGroup()
    {
        return $this->setData(self::CUSTOMER_GROUP);
    }

   /**
    * Set CustomerGroup.
    */
    public function setCustomerGroup($customerGroup)
    {
        return $this->setData(self::CUSTOMER_GROUP, $customerGroup);
    }

    /**
    * Get SortOrder.
    *
    * @return varchar
    */
    public function getSortOrder()
    {
        return $this->setData(self::SORT_ORDER);
    }
    
   /**
    * Set SortOrder.
    */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

}
