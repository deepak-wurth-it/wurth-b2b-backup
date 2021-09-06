<?php
   
namespace Pim\Category\Model;

use Magento\Catalog\Model\Category as MagentoCategory;
use Pim\Category\Api\Data\CategoryInterface;

class Category extends MagentoCategory implements CategoryInterface{

    /**#@+
     * Constants
     */
   const PIM_CATEGORY_ACTIVE_STATUS = 'pim_category_active_status';
   const PIM_CATEGORY_CHANNEL_ID = 'pim_category_channel_id';
   const PIM_CATEGORY_EXTERNAL_ID = 'pim_category_external_id';
   const PIM_CATEGORY_CODE = 'pim_category_code';
   const PIM_CATEGORY_ID = 'pim_category_id';
   const PIM_CATEGORY_PARENT_ID = 'pim_category_parent_id';
   
   /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'pim_category_records';

    /**
     * @var string
     */
    protected $_cacheTag = 'pim_category_records';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'pim_category_records';

    /**
     * Initialize resource model.
     */
    // protected function _construct()
    // {
    //     $this->_init(\Pim\Category\Model\ResourceModel\Category::class);
    // }

   /**
     * Get pim parent category identifier
     *
     * @return int
     */
    public function getPimCategoryParentId()
    {
        return $this->getData(self::PIM_CATEGORY_PARENT_ID);
        
    }

    /**
     * Set pim parent category ID
     *
     * @param int $parentId
     * @return $this
     */
    public function setPimCategoryParentId($parentId)
    {
        return $this->setData(self::PIM_CATEGORY_PARENT_ID, $parentId);
    }




    /**
     * @return int|null
     */
    public function getPimCategoryId(){
      
      return $this->getData(self::PIM_CATEGORY_ID);

    }

    /**
     * @param int $id
     * @return $this
     */
    public function setPimCategoryId($id){
      
      return $this->setData(self::PIM_CATEGORY_ID, $id);

    }

    

    /**
     * Check whether Pim category is active
     *
     * @return bool|null
     */
    public function getPimCategoryActiveStatus(){

      return $this->getData(self::PIM_CATEGORY_ACTIVE_STATUS);
    }

    /**
     * Set whether category Pim is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setPimCategoryActiveStatus($isActive){

      return $this->setData(self::PIM_CATEGORY_ACTIVE_STATUS);
    }

  /**
     * Get Pim Category Code
     *
     * @return int|null
     */
    public function getPimCategoryCode(){

      return $this->getData(self::PIM_CATEGORY_CODE);
    }

    /**
     * Set Pim Category Code
     *
     * @param bool $code
     * @return $this
     */
    public function setPimCategoryCode($code){

      return $this->setData(self::PIM_CATEGORY_CODE, $code);
    }



    /**
     * Get Pim Category Channel Id
     *
     * @return int|null
     */
    public function getPimCategoryChannelId(){

      return $this->getData(self::PIM_CATEGORY_CHANNEL_ID);

    }

    /**
     * Set Pim Category Channel Id
     *
     * @param bool $channelId
     * @return $this
     */
    public function setPimCategoryChannelId($channelId){

      return $this->setData(self::PIM_CATEGORY_CHANNEL_ID, $channelId);

    }


    /**
     * Get Pim Category External Id
     *
     * @return int|null
     */
    public function getPimCategoryExternalId(){

      return $this->getData(self::PIM_CATEGORY_EXTERNAL_ID);

    }

    /**
     * Set Pim Category External Id
     *
     * @param bool $externalId
     * @return $this
     */
    public function setPimCategoryExternalId($externalId){

      return $this->setData(self::PIM_CATEGORY_EXTERNAL_ID,$externalId);
    }
    
    public function creatingCategory($row)
    {
       

        $name = $row['Name'] ? $row['Name'] : '';
        $active = $row['Active'] ? $row['Active'] : '0';
        $parentId = $row['ParentId'];
        if ($parentId) {
            $parentId = $this->categoryRepositoryInterface->getByPimParentId($parentId);
        } else {
            $parentId =  2;
        }
        $category = $this->categoryFactory->create();

        $catCollection = $this->getCategoriesExistsOrNot($category, $name);

        if ($catCollection->getId()) {
            $category =  $catCollection;
        }
       
            $category->setName($name);
            $category->setParentId($parentId);
            $category->setIsActive($active);
            $category->setCustomAttributes([
                'description' => 'category example',
                'meta_title' => 'category example',
                'meta_keywords' => '',
                'meta_description' => '',
                'pim_category_id' => $row['Id'],
                'pim_category_active_status' => $row['Active'],
                'pim_category_channel_id' => $row['ChannelId'],
                'pim_category_code' => $row['Code'],
                'pim_category_external_id' => $row['ExternalId'],
                'pim_category_parent_id' => $row['ParentId']

            ]);

            $id = $this->categoryRepositoryInterface->save($category);
            echo 'Done for category =>>>>> ' . $name . PHP_EOL;
      
    }
}

