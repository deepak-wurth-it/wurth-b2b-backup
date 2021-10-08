<?php
namespace Wcb\Demonotices\Block;
use Magento\Framework\View\Element\AbstractBlock;

class Demonotice extends \Magento\Theme\Block\Html\Notices
{
    protected $filterProvider;
    protected $scopeConfig;
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Cms\Model\Template\FilterProvider $filterProvider,
     \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
     array $data = []
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_filterProvider = $filterProvider;
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
}