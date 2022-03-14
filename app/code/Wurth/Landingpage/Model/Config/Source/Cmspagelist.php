<?php
namespace Wurth\Landingpage\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Wurth\Landingpage\Model\ResourceModel\LandingPage\CollectionFactory as LandingPageCollectionFactory;

class Cmspagelist implements ArrayInterface
{
    protected $pageRepositoryInterface;
    protected $searchCriteriaBuilder;
    protected $landigPageCollection;
    protected $request;

    public function __construct(
        \Magento\Cms\Api\PageRepositoryInterface $pageRepositoryInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        LandingPageCollectionFactory $landingPageCollectionFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
        $this->pageRepositoryInterface = $pageRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->landigPageCollection = $landingPageCollectionFactory;
    }
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label,
            ];
        }
        return $result;
    }

    public function getOptions()
    {
        $pages = $this->getPages();
        $data = ["" => __("Please select CMS page")];
        $assignCmsPages = $this->getAssignCmsPage();
        foreach ($pages as $page) {
            if (!in_array($page->getId(), $assignCmsPages)) {
                $data[$page->getId()] = __($page->getTitle());
            }
        }
        return $data;
    }

    public function getPages()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->pageRepositoryInterface->getList($searchCriteria)->getItems();
    }
    public function getAssignCmsPage()
    {
        $landingPageId = $this->request->getParam("landing_page_id");
        $landingPages = $this->landigPageCollection->create();
        $assignCmsPage = [];
        foreach ($landingPages as $land) {
            if ($landingPageId != $land->getLandingPageId()) {
                $assignCmsPage[] = $land->getCmsPage();
            }
        }
        return $assignCmsPage;
    }
}
