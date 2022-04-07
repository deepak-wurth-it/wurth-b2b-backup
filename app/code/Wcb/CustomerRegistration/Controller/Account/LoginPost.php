<?php
namespace Wcb\CustomerRegistration\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Post login customer action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends AbstractAccount implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;
    public $_storeManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated 100.0.10
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $resultData = [
            'status'  => "false",
            'message' => "customer login"
        ];

        $login = $this->getRequest()->getPost('login');
        if (!empty($login['username']) && !empty($login['password'])) {
            try {
                $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                $this->session->setCustomerDataAsLoggedIn($customer);
                if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                    $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                }
                $redirectUrl = $this->accountRedirect->getRedirectCookie();
                if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                    $this->accountRedirect->clearRedirectCookie();
                    $redirectUrl = $this->_storeManager->getStore()->getBaseUrl();

                }
                $resultData = [
                    'status'  => "true",
                    'message' => "customer login",
                    'redirect_url' => $redirectUrl
                ];
            } catch (EmailNotConfirmedException $e) {
                $this->messageManager->addComplexErrorMessage(
                    'confirmAccountErrorMessage',
                    ['url' => $this->customerUrl->getEmailConfirmationUrl($login['username'])]
                );
                $this->session->setUsername($login['username']);
                $resultData = [
                        'status'  => "false",
                        'message' => "",
                        'redirect_url' => ""
                    ];
            } catch (AuthenticationException $e) {
                $message = __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                );
                $resultData = [
                        'status'  => "false",
                        'message' => "",
                        'redirect_url' => ""
                    ];
            } catch (LocalizedException $e) {
                $message = $e->getMessage();
                $resultData = [
                        'status'  => "false",
                        'message' => "",
                        'redirect_url' => ""
                    ];
            } catch (\Exception $e) {
                // PA DSS violation: throwing or logging an exception here can disclose customer password
                $this->messageManager->addErrorMessage(
                    __('An unspecified error occurred. Please contact us for assistance.')
                );
                $resultData = [
                        'status'  => "false",
                        'message' => "",
                        'redirect_url' => ""
                    ];
            } finally {
                if (isset($message)) {
                    $this->messageManager->addErrorMessage($message);
                    $this->session->setUsername($login['username']);
                    $resultData = [
                            'status'  => "false",
                            'message' => "",
                            'redirect_url' => ""
                        ];
                }
            }
        } else {
            $this->messageManager->addErrorMessage(__('A login and a password are required.'));
            $resultData = [
                    'status'  => "false",
                    'message' => "",
                    'redirect_url' => ""
                ];
        }

        $response = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData($resultData);

        return $response;
    }
}
