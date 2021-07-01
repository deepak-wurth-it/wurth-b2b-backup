<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Brandslider\Model\Config\Backend;

/**
 * Backend model for domain config value
 */
class Validate extends \Magento\Framework\App\Config\Value
{
    /** @var  \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator */
    protected $configValidator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator $configValidator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator $configValidator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValidator = $configValidator;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate a domain name value
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
	$data = $this->getData();
	
	if(isset($data['groups']['general']['fields']['speed']['value']))
	{
		$value = $data['groups']['general']['fields']['speed']['value'];
		if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Slideshow Speed: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
	}
	
	if(isset($data['groups']['general']['fields']['pagination']['value']))
	{
		$value = $data['groups']['general']['fields']['pagination']['value'];
		if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Pause Speed: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
	}
	
	if(isset($data['groups']['general']['fields']['qty']['value']))
	{
		$value = $data['groups']['general']['fields']['qty']['value'];
		if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Qty of Items: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
	}
	
	if(isset($data['groups']['general']['fields']['default']['value']))
	{
		$value = $data['groups']['general']['fields']['default']['value'];
		if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Items Default: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
	}
	
	if(isset($data['groups']['general']['fields']['desktop']['value']))
	{
		$value = $data['groups']['general']['fields']['desktop']['value'];
		if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Items On Desktop: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
	}
	
	if(isset($data['groups']['general']['fields']['desktop_small']['value']))
	{
		$value = $data['groups']['general']['fields']['desktop_small']['value'];
		if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Items On Desktop Small: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
	}
	
	if(isset($data['groups']['general']['fields']['tablet']['value']))
	{
		$value = $data['groups']['general']['fields']['tablet']['value'];
		if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Items On Tablet: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
	}
	
	if(isset($data['groups']['general']['fields']['mobile']['value']))
	{
		$value = $data['groups']['general']['fields']['mobile']['value'];
		if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('items On Mobile: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
	}
	
	if(isset($data['groups']['general']['fields']['rows']['value']))
	{
		$value = $data['groups']['general']['fields']['rows']['value'];
		if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Number Rows Show: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
	}
	
        $value = $this->getValue();

        if (!empty($value) && !$this->configValidator->isValid($value)) {
            $msg = __('Slideshow Speed: ' . join('; ', $this->configValidator->getMessages()));
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
        return parent::beforeSave();
    }
}
