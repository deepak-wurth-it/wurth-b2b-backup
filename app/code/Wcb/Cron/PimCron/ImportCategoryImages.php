<?php

/**
 * Cron for images
 * @category  Wuerth
 * @package   Wcb_Cron
 * @author    Deepak Kumar <dkumar@Redstage.com>
 * @copyright 2022 - 2023 Wuerth IT
 */

namespace Wcb\Cron\PimCron;



class ImportCategoryImages
{



    public function __construct(
        \Pim\Category\Model\CategoryImageProcessor $categoryImageProcessor
    ) {

        $this->categoryImageProcessor = $categoryImageProcessor;
    }

    public function execute()
    {
        try {
            $this->categoryImageProcessor->install();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
