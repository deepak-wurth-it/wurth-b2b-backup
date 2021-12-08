<?php
namespace Wcb\Demonotices\Block;
use Magento\Framework\View\Element\AbstractBlock;

class Demonotice extends \Magento\Theme\Block\Html\Notices
{
    protected $filterProvider;
    protected $scopeConfig;
    protected $timezone;
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Cms\Model\Template\FilterProvider $filterProvider,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
     array $data = []
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_filterProvider = $filterProvider;
        $this->timezone = $timezone;
        parent::__construct($context, $data);
    }

    /**
     * getConfigValue
     * @return boolean
     */
    public function getConfigValue()
    {
        return $this->_scopeConfig->getValue(
            'design/head/demonotice',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Prepare HTML content
     *
     * @return string
     */
    public function getCmsFilterContent($value='')
    {
        return $this->_filterProvider->getPageFilter()->filter($value);
    }

    /**
     * getCustomDemoMessage
     * @return mixed
     */
    public function getCustomDemoMessage()
    {
        $demoMessage = $this->_scopeConfig->getValue(
            'design/head/head_wcdemomsg',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if($demoMessage){
            return $this->getCmsFilterContent($demoMessage);   
        }
        return false;    
    }

    /**
     * getDemoMessageValidFrom
     * @return mixed
     */
    public function getDemoMessageValidFrom()
    {
        $validFrom = $this->_scopeConfig->getValue(
            'design/head/message_valid_from',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if($validFrom){
            $dateTimeZone = $this->timezone->date(new \DateTime($validFrom))->format('m/d/y H:i:s');

            return $this->getCmsFilterContent($dateTimeZone);   
        }
        return false;    
    }
    
    /**
     * getDemoMessageValidTo
     * @return mixed
     */
    public function getDemoMessageValidTo()
    {
        $validTo = $this->_scopeConfig->getValue(
            'design/head/message_valid_to',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if($validTo){
            $dateTimeZone = $this->timezone->date(new \DateTime($validTo))->format('m/d/y H:i:s');

            return $this->getCmsFilterContent($dateTimeZone);   
        }
        return false;    
    }


    /**
     * getTodayDate
     * @return mixed
     */
    public function getTodayDate()
    {
        $todaysDate = $this->timezone->date()->format('Y-m-d H:i:s');

        if($todaysDate){
            $dateTimeZone = $this->timezone->date(new \DateTime($todaysDate))->format('Y-m-d H:i:s');
            return $this->getCmsFilterContent($dateTimeZone);   
        }
        return false;    
    }
}