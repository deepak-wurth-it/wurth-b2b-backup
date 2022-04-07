<?php
declare(strict_types=1);

namespace Wcb\ApiConnect\Model\Homepage;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Wcb\ApiConnect\Api\Homepage\HomepageManagementInterface;
use Wcb\Demonotices\Block\Demonotice;
use Plazathemes\Bannerslider\Model\ResourceModel\Banner\CollectionFactory as BannerCollection;

class HomepageManagement implements HomepageManagementInterface
{
    protected $scopeConfig;
    protected $demoNotice;
    protected $jsonResultFactory;
    protected $bannerCollection;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Demonotice $demoNotice,
        JsonFactory $jsonResultFactory,
        BannerCollection $bannerCollection
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->demoNotice = $demoNotice;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->bannerCollection = $bannerCollection;
    }

    /**
     * @return Json
     */
    public function getHomePageInfo()
    {
        $result = [];
        $data = [];
        try {
            $data["offer_header"] = $this->demoNotice->getCustomDemoMessage();
            $data["homepage_slider"] = [
                "https://devtestb2b.wuerth.com.hr/media/Plazathemes/bannerslider/images/g/r/group_780_1.png",
                "https://devtestb2b.wuerth.com.hr/media/Plazathemes/bannerslider/images/g/r/group_780_1.png"
            ];
            $data["promosition_banner"] = [
                "https://devtestb2b.wuerth.com.hr/media/Plazathemes/bannerslider/images/g/r/group_780_1.png",
                "https://devtestb2b.wuerth.com.hr/media/Plazathemes/bannerslider/images/g/r/group_780_1.png"
            ];
            $data["catalog_slider"] = [
                "https://devtestb2b.wuerth.com.hr/media/Plazathemes/bannerslider/images/g/r/group_780_1.png",
                "https://devtestb2b.wuerth.com.hr/media/Plazathemes/bannerslider/images/g/r/group_780_1.png"
            ];
            $data["bestseller_slider"] = [
                "https://devtestb2b.wuerth.com.hr/media/Plazathemes/bannerslider/images/g/r/group_780_1.png",
                "https://devtestb2b.wuerth.com.hr/media/Plazathemes/bannerslider/images/g/r/group_780_1.png"
            ];
            $result['success'] = true;
            $result['message'] = "Homepage data get successfully.";
            $result['data'] = $data;
        } catch (Exception $e) {
            $result['success'] = true;
            $result['message'] = $e->getMessage();
        }
        $resultData[] = $result;
        return $resultData;
        /*$resultData = $this->jsonResultFactory->create();
        $resultData->setData($result);
        return $resultData->getData();*/
    }
}
