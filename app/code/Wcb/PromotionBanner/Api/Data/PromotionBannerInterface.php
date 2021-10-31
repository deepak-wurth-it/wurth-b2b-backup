<?php

namespace Wcb\PromotionBanner\Api\Data;

interface PromotionBannerInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const TITLE = 'title';
    const IMAGE = 'image';
    const STATUS = 'status';
    const POSITION = 'position';
    const URL = 'url';
    const TARGET = 'target';
    const SORT_ORDER = 'sort_order';
    const CUSTOMER_GROUP = 'customer_group';
    const VALID_FROM = 'valid_from';
    const VALID_TO = 'valid_to';
    const CREATED_AT = 'created_at';
    const UPDATE_AT = 'updated_at';

   /**
    * Get EntityId.
    *
    * @return int
    */
    public function getEntityId();

   /**
    * Set EntityId.
    */
    public function setEntityId($entityId);

   /**
    * Get Title.
    *
    * @return varchar
    */
    public function getTitle();

   /**
    * Set Title.
    */
    public function setTitle($title);

   /**
    * Get Image.
    *
    * @return varchar
    */
    public function getImage();

   /**
    * Set Image.
    */
    public function setImage($image);

    /**
    * Get Position.
    *
    * @return varchar
    */
    public function getPosition();

   /**
    * Set Position.
    */
    public function setPosition($position);
    
    /**
    * Get Status.
    *
    * @return varchar
    */
    public function getStatus();

   /**
    * Set Status.
    */
    public function setStatus($status);
    
    
   /**
    * Get Url.
    *
    * @return varchar
    */
    public function getUrl();

   /**
    * Set Url.
    */
    public function setUrl($url);

   /**
    * Get Target.
    *
    * @return varchar
    */
    public function getTarget();

   /**
    * Set Target.
    */
    public function setTarget($target);
  
    /**
    * Get ValidFrom.
    *
    * @return varchar
    */
    public function getValidFrom();

   /**
    * Set ValidFrom.
    */
    public function setValidFrom($validFrom);

    /**
    * Get ValidTo.
    *
    * @return varchar
    */
    public function getValidTo();

   /**
    * Set ValidTo.
    */
    public function setValidTo($validTo);

    /**
    * Get CustomerGroup.
    *
    * @return varchar
    */
    public function getCustomerGroup();

   /**
    * Set CustomerGroup.
    */
    public function setCustomerGroup($customerGroup);

    /**
    * Get SortOrder.
    *
    * @return varchar
    */
    public function getSortOrder();

   /**
    * Set SortOrder.
    */
    public function setSortOrder($sortOrder);

    /**
    * Get CreatedAt.
    *
    * @return varchar
    */
    public function getCreatedAt();

   /**
    * Set CreatedAt.
    */
    public function setCreatedAt($createdAt);

    /**
    * Get UpdatedAt.
    *
    * @return varchar
    */
    public function getUpdatedAt();

   /**
    * Set UpdatedAt.
    */
    public function setUpdatedAt($updatedAt);
}
