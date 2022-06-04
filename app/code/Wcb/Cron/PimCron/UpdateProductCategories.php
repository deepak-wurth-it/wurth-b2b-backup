<?php

/**
 * Cron for product categories
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\PimCron;



class UpdateProductCategories
{



    public function __construct(
        \Pim\Category\Model\CategoryProductProcessor $categoryProductProcessor
    ) {

        $this->categoryProductProcessor = $categoryProductProcessor;
    }

    public function execute()
    {
        try {
            $this->categoryProductProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
