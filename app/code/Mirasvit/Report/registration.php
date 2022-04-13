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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


$path = __DIR__;
$ds = DIRECTORY_SEPARATOR;
if (strpos($path, 'app'.$ds.'code'.$ds.'Mirasvit') === false) {
    $basePath = dirname(dirname(dirname(__DIR__)));
} else {
    $basePath = dirname(dirname(dirname(dirname(__DIR__))));
}
$registration = $basePath.$ds.'vendor'.$ds.'mirasvit'.$ds.'module-report'.$ds.'src'.$ds.'Report'.$ds.
    'registration.php';
if (file_exists($registration)) {
    # module was already installed via composer
    return;
}

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Mirasvit_Report',
    __DIR__
);