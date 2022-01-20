<?php
declare(strict_types=1);

namespace Amasty\Promo\Plugin\Bundle\Block\Catalog\Product\View\Type\Bundle;

use Amasty\Promo\ViewModel\Product\View\Type\Bundle as BundleViewModel;
use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle;

class ForceLoadOptions
{
    const TRIGGER_BLOCK_NAME = 'ampromo.bundle.prototype';

    /**
     * @var BundleViewModel
     */
    private $bundleViewModel;

    public function __construct(
        BundleViewModel $bundleViewModel
    ) {
        $this->bundleViewModel = $bundleViewModel;
    }

    /**
     * @param Bundle $subject
     * @param array $result
     * @param bool $stripSelections
     * @return array
     * @see Bundle::getOptions()
     */
    public function afterGetOptions(Bundle $subject, array $result, bool $stripSelections = false): array
    {
        if ($subject->getNameInLayout() === self::TRIGGER_BLOCK_NAME) {
            $product = $subject->getProduct();

            if ($product) {
                $result = $this->bundleViewModel->getOptions($product, $stripSelections);
            }
        }

        return $result;
    }
}
