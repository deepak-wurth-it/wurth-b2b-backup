<?php
declare(strict_types=1);

namespace Mirasvit\Search\Plugin\Cms\Model\Page\DataProvider\AfterGetData;

use Magento\Cms\Model\Page\DataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Model\ImageUploader;

/**
 * Class ModifyBannerDataPlugin
 */
class ModifyDataPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ModifyBannerDataPlugin constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param DataProvider $subject
     * @param $loadedData
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterGetData(
        DataProvider $subject,
        $loadedData
    ) {
        /** @var array $loadedData */
        if (is_array($loadedData) && count($loadedData) == 1) {
            foreach ($loadedData as $key => $item) {
                if (isset($item['cms_image']) && $item['cms_image']) {
                    $imageArr = [];
                    $imageArr[0]['name'] = 'Image';
                    $imageArr[0]['url'] = $this->storeManager->getStore()
                            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) .
                        ImageUploader::IMAGE_PATH . DIRECTORY_SEPARATOR . $item['cms_image'];
                    $loadedData[$key]['cms_image'] = $imageArr;
                }
            }
        }
        return $loadedData;
    }
}
