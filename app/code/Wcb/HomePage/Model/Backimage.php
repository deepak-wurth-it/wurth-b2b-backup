<?php
namespace Wcb\HomePage\Model\Model;
class Backimage extends \Magento\Config\Model\Config\Backend\Image
{
    const UPLOAD_DIR = 'extension/backimage/';
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }
    protected function _addWhetherScopeInfo()
    {
        return true;
    }
    public function getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'png'];
    }
}