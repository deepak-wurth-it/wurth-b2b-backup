<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


$path = __DIR__;
$ds   = DIRECTORY_SEPARATOR;
if (strpos($path, 'app' . $ds . 'code' . $ds . 'Mirasvit') === false) {
    $basePath = dirname(dirname(dirname(__DIR__)));
} else {
    $basePath = dirname(dirname(dirname(dirname(__DIR__))));
}
$registration = $basePath . $ds . 'vendor' . $ds . 'mirasvit' . $ds . 'module-report' . $ds . 'src' . $ds . 'Report' . $ds .
    'registration.php';
if (file_exists($registration)) {
    # module was already installed
    return;
}

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::LIBRARY,
    'Mirasvit_ReportApi',
    __DIR__
);
