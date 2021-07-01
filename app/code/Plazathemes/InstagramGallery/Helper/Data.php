<?php
/**
* Copyright © 2016 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\InstagramGallery\Helper;

use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Plazathemes\InstagramGallery\Block\InstagramGallery;

class Data extends AbstractHelper
{
	protected $_objectManager;
	protected $_storeManager;
	protected $_directory;
	protected $_dir;
	protected $_filesystem;
	protected $_imageFactory;
	protected $_block;

	public function __construct(
		Filesystem $filesystem,
		ObjectManagerInterface $objectManager,
		StoreManagerInterface $storeManagerInterface,
		\Magento\Framework\Filesystem\DirectoryList $directoryList,
		\Magento\Framework\Image\AdapterFactory $imageFactory,
		\Plazathemes\InstagramGallery\Block\InstagramGallery $block
	){
		$this->_objectManager = $objectManager;
		$this->_storeManager = $storeManagerInterface;
		$this->_filesystem = $filesystem;
		$this->_directory = $directoryList;
		$this->_imageFactory = $imageFactory;
		$this->_block = $block;
	}

	protected function _getBaseDir()
	{
		$dir = $this->_directory->getDirectoryWrite(DirectoryList::ROOT);
		return $dir->getAbsolutePath();
	}

	protected function _getBaseDirPub()
	{
		$dir = $this->_directory->getDirectoryWrite(DirectoryList::PUB);
		return $dir->getAbsolutePath();
	}

	protected function _getBaseDirMedia()
	{
		$dir = $this->_directory->getDirectoryWrite(DirectoryList::MEDIA);
		return $dir->getAbsolutePath();
	}
}