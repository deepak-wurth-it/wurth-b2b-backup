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
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Model;

use Magento\Framework\DataObject;

class Package extends DataObject
{
    public function getModuleList()
    {
        $modules = $this->getData('modules');

        return is_array($modules) ? $modules : [];
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function addModuleName($value)
    {
        $modules   = $this->getModuleList();
        $modules[] = $value;

        return $this->setData('modules', $modules);
    }

    public function getPackage()
    {
        return (string)$this->getData('package');
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setPackage($value)
    {
        return $this->setData('package', $value);
    }

    public function getVersion()
    {
        return (string)$this->getData('version');
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setVersion($value)
    {
        return $this->setData('version', $value);
    }

    public function getVersionTxt()
    {
        return (string)$this->getData('version_txt');
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setVersionTxt($value)
    {
        return $this->setData('version_txt', $value);
    }

    public function getLatestVersion()
    {
        return (string)$this->getData('latest_version');
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setLatestVersion($value)
    {
        return $this->setData('latest_version', $value);
    }

    public function getLabel()
    {
        return (string)$this->getData('label');
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setLabel($value)
    {
        return $this->setData('label', $value);
    }

    public function getUrl()
    {
        return (string)$this->getData('url');
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setUrl($value)
    {
        return $this->setData('url', $value);
    }

    public function getChangelogUrl()
    {
        return (string)$this->getData('changelog_url');
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setChangelogUrl($value)
    {
        return $this->setData('changelog_url', $value);
    }

    public function getSku()
    {
        return (string)$this->getData('sku');
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setSku($value)
    {
        return $this->setData('sku', $value);
    }

    public function getRequire()
    {
        return (array)$this->getData('require');
    }

    public function setRequire(array $value)
    {
        return $this->setData('require', $value);
    }

    public function isOutdated()
    {
        return version_compare($this->getVersion(), $this->getLatestVersion()) == -1;
    }

    public function isOld()
    {
        $version       = $this->getVersionNumber($this->getVersion());
        $latestVersion = $this->getVersionNumber($this->getLatestVersion());

        $diff = $latestVersion - $version;

        return $diff > 5000000 ? true : false;
    }

    /**
     * @param string $version
     *
     * @return int
     */
    private function getVersionNumber($version)
    {
        $number  = 0;
        $matches = [];
        preg_match_all('/([0-9]*).([0-9]*).([0-9]*)?(\-[a-z]*([0-9]*))?/', $version, $matches);

        if (isset($matches[1][0])) {
            $major     = (int)$matches[1][0];
            $minor     = (int)$matches[2][0];
            $path      = (int)$matches[3][0];
            $stability = (int)$matches[5][0];

            $number = $major * pow(10, 12)
                + $minor * pow(10, 9)
                + $path * pow(10, 6)
                + $stability * pow(10, 3);
        }

        return $number;
    }
}
