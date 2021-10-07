<?php

namespace Wcb\Catalog\Plugin\Catalog\Controller\Category;

class View extends \Magento\Catalog\Controller\Category\View
{

    public function afterExecute(\Magento\Catalog\Controller\Category\View $subject, $result)
    {
        $category = $this->_coreRegistry->registry('current_category');
        if(!$category->getProductCollection()->getSize()){
            $result->getConfig()->setPageLayout('1column');
        }

        return $result;
    }
}
