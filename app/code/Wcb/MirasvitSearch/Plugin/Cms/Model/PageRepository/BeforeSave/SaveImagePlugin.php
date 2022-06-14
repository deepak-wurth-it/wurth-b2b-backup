<?php
declare(strict_types=1);

namespace Wcb\MirasvitSearch\Plugin\Cms\Model\PageRepository\BeforeSave;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Exception\LocalizedException;
use Wcb\MirasvitSearch\Model\ImageUploader;

/**
 * Class SaveBannerImagePlugin
 */
class SaveImagePlugin
{
    /**
     * @var BannerUploader
     */
    private $uploader;

    /**
     * SaveBannerImagePlugin constructor.
     * @param ImageUploader $uploader
     */
    public function __construct(
        ImageUploader $uploader
    ) {
        $this->uploader = $uploader;
    }

    /**
     * Save
     *
     * @param PageRepository $subject
     * @param PageInterface $page
     * @return array
     * @throws LocalizedException
     */
    public function beforeSave(
        PageRepository $subject,
        PageInterface $page
    ): array {
        $data = $page->getData();
        $key = 'cms_image';
        if (isset($data[$key]) && is_array($data[$key])) {
            if (!empty($data[$key]['delete'])) {
                $data[$key] = null;
            } else {
                if (isset($data[$key][0]['name']) && isset($data[$key][0]['tmp_name'])) {
                    $image = $data[$key][0]['name'];
                    $image = $this->uploader->moveFileFromTmp($image);
                    $data[$key] = $image;
                } else {
                    if (isset($data[$key][0]['url'])) {
                        $data[$key] = basename($data[$key][0]['url']);
                    }
                }
            }
            $page->setData($data);
        } else {
            $data[$key] = null;
        }
        return [$page];
    }
}
