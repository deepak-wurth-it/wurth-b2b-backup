<?php

namespace Wcb\NewFeature\Plugin\Magento\Theme\Block\Html;

use \Magento\Theme\Block\Html\Breadcrumbs as MagentoBreadCrumbs;

class Breadcrumbs
{
    public function beforeAddCrumb(MagentoBreadCrumbs $subject, $crumbName, $crumbInfo)
    {
        if ($crumbName == 'home') {
            $crumbInfo['label'] = __('Online shop');
            $crumbInfo['c_label'] = __('Home');
        }

        return [$crumbName, $crumbInfo];
    }
}
