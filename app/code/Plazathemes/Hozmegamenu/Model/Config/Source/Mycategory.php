<?php

namespace Plazathemes\Hozmegamenu\Model\Config\Source;
use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Context;
class Mycategory implements \Magento\Framework\Option\ArrayInterface
{
   
	 protected $_catalogCategory;
     protected $_categoryInstance;
	 protected $httpContext;
     public function __construct(
		  \Magento\Framework\View\Element\Template\Context $context,
		  \Magento\Catalog\Model\CategoryFactory $categoryFactory,
		       \Magento\Framework\App\Http\Context $httpContext,
		  array $data = []
	 )  {
			  $this->httpContext = $httpContext;
		      $this->_categoryInstance = $categoryFactory->create();
	 }

    /**
     * Options array
     *
     * @var array
     */
    protected $_options;
	
	
	 public function toOptionArray()
    {
		$collection = $this->_categoryInstance->getCollection()
							   -> addAttributeToFilter('level',2)
							   -> addAttributeToFilter('is_active',1);
		$arr = array();					   
		$i=0;
		foreach($collection as $cate) {
				$category = $this->_categoryInstance ->load($cate->getId());
				$arr[$i]=array('value'=>$category->getId(), 'label'=> $category->getName());
						$i++;
	
		}

		return $arr;

    }

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @return array
     */
    public function toOptionArray123($isMultiselect = false, $foregroundCountries = '')
    {
		
        if (!$this->_options) {
            $this->_options = $this->_countryCollection->loadData()->setForegroundCountries(
                $foregroundCountries
            )->toOptionArray(
                false
            );
        }

        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }
		echo "<Pre>"; print_r($options); die; 
        return $options;
    }
}
