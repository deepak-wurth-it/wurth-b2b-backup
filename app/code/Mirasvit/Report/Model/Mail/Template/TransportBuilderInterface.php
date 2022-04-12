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



namespace Mirasvit\Report\Model\Mail\Template;

use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\Mail\Template\FactoryInterface;
use \Magento\Framework\Mail\Template\SenderResolverInterface;
/**
 * Extended core class for implement attachments functionality
 */
interface TransportBuilderInterface
{
	/**
	 * Creates a \Zend_Mime_Part attachment
	 * @param  string $body
	 * @param  string $mimeType
	 * @param  string $disposition
	 * @param  string $encoding
	 * @param  string $filename OPTIONAL A filename for the attachment
	 * @return $this
	 */
	public function addAttachment(
		$body,
		$mimeType = \Zend_Mime::TYPE_OCTETSTREAM,
		$disposition = \Zend_Mime::DISPOSITION_ATTACHMENT,
		$encoding = \Zend_Mime::ENCODING_BASE64,
		$filename = null
	);
}