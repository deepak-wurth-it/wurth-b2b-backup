<?php
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Controller\Account;

use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlFactory;
use Magento\Store\Model\StoreManagerInterface;

class CreatePost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;
    protected $companyFactory;
    protected $customerInterfaceFactory;
    protected $encryptorInterface;
    protected $customerRepositoryInterface;
    protected $customerRepository;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param CompanyManagementInterface $companyMngRepository
     * @param CompanyRepositoryInterface $companyRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     * @param UrlFactory $urlFactory
     * @param Session $session
     * @param CompanyCustomerInterfaceFactory $companyFactory
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param EncryptorInterface $encryptorInterface
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CustomerRepositoryInterface $customerRepository
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        CompanyManagementInterface $companyMngRepository,
        CompanyRepositoryInterface $companyRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ManagerInterface $messageManager,
        Escaper $escaper,
        UrlFactory $urlFactory,
        Session $session,
        CompanyCustomerInterfaceFactory $companyFactory,
        CustomerInterfaceFactory $customerInterfaceFactory,
        EncryptorInterface $encryptorInterface,
        CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Validator $formKeyValidator = null
    ) {
        $this->storeManager     = $storeManager;
        $this->customerFactory  = $customerFactory;
        $this->addressFactory   = $addressFactory;
        $this->companyMngRepository = $companyMngRepository;
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->messageManager   = $messageManager;
        $this->escaper          = $escaper;
        $this->urlModel         = $urlFactory->create();
        $this->session          = $session;
        $this->formKeyValidator = $formKeyValidator ?: ObjectManager::getInstance()->get(Validator::class);
        $this->companyFactory = $companyFactory;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->encryptorInterface = $encryptorInterface;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerRepository = $customerRepository;
        // messageManager can also be set via $context
        // $this->messageManager   = $context->getMessageManager();

        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPost();
        $resultRedirect = $this->resultRedirectFactory->create();

        // check if the form is actually posted and has the proper form key
        if (!$this->getRequest()->isPost() || !$this->formKeyValidator->validate($this->getRequest())) {
            $url = $this->urlModel->getUrl('excustomer/account/create', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->error($url));
            return $resultRedirect;
        }
        $websiteId  = $this->storeManager->getWebsite()->getWebsiteId();

        $firstName = $postData['firstname'];
        $lastName = $postData['lastname'];
        $email = $postData['email'];
        $password = $postData['password'];
        $position = $postData['position'];
        $telephone = $postData['telephone'];
        $companyCode = $postData['company']['customer_code'];

        // instantiate customer object
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);

        // check if customer is already present
        // if customer is already present, then show error message
        // else create new customer
        if ($customer->loadByEmail($email)->getId()) {
            //echo 'Customer with the email ' . $email . ' is already registered.';
            $message = __(
                'There is already an account with this email address "%1".',
                $email
            );
            // @codingStandardsIgnoreEnd
            $this->messageManager->addError($message);
        } else {
            try {
                $url = $this->urlModel->getUrl('excustomer/account/create', ['_secure' => true]);

                $companyData = $this->getCompanyId($postData['company']['vat_tax_id']);
                $companyId = isset($companyData['company_id']) ? $companyData['company_id'] : '';
                $companyGroupId = isset($companyData['company_group_id']) ? $companyData['company_group_id'] : 1;
                if (!$companyId) {
                    $message = __(
                        'Company OIB does not exists. Please enter valid company OIB.'
                    );
                    $this->messageManager->addError($message);
                    $resultRedirect->setUrl($this->_redirect->success($url));
                    return $resultRedirect;
                }
                //Save customer using repo
                $customer = $this->customerInterfaceFactory->create();
                $customer->setWebsiteId($websiteId);
                $customer->setEmail($email);
                $customer->setFirstname($firstName);
                $customer->setLastname($lastName);

                $hashedPassword = $this->encryptorInterface->getHash($password, true);
                $this->customerRepositoryInterface->save($customer, $hashedPassword);

                //After create customer save other attributes values
                $customer = $this->customerFactory->create()
                    ->setWebsiteId($websiteId)
                    ->loadByEmail($email);

                if ($customer->getId()) {
                    $vatTaxId = isset($postData['company']['vat_tax_id']) ? $postData['company']['vat_tax_id'] : '';
                    $customer->setPosition($position);
                    $customer->setCustomerCode($companyCode);
                    $customer->setPhone("+385" . $telephone);
                    $customer->setTaxvat($vatTaxId);
                    $customer->setConfirmation(AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED);
                    $customer->save();

                    // subscribe user
                    $this->subscribeUserAndSetGroup($customer->getId(), $companyGroupId, $position);
                }

                // prepare customer data
                /*$customer->setEmail($email);
                $customer->setFirstname($firstName);
                $customer->setLastname($lastName);
                $customer->setPosition($position);
                $customer->setCompanyId($companyId);
                $customer->setCustomerCode($companyCode);
                $customer->setPhone($telephone);
                $customer->setPassword($password);
                $customer->save();*/

                $this->assignCompany($companyId, $customer->getId());
                $this->messageManager->addSuccess(
                    __(
                        'Customer account with email %1 created successfully.',
                        $email
                    )
                );

                $resultRedirect->setPath("excustomer/account/success/", ['email' => $email]);
                return $resultRedirect;
            } catch (StateException $e) {
                $url = $this->urlModel->getUrl('customer/account/forgotpassword');
                // @codingStandardsIgnoreStart
                $message = __(
                    'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                    $url
                );
                // @codingStandardsIgnoreEnd
                $this->messageManager->addError($message);
            } catch (InputException $e) {
                $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addError($this->escaper->escapeHtml($error->getMessage()));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the customer.'));
            }
        }

        $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        $defaultUrl = $this->urlModel->getUrl('excustomer/account/create', ['_secure' => true]);
        $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
        return $resultRedirect;
    }

    public function assignCompany($companyId, $customerId)
    {
        $company = null;
        if ($companyId && $customerId) {
            $company = $this->companyMngRepository->assignCustomer($companyId, $customerId);
        }
        return $company;
    }

    /**
     * @param string $companyTax
     * @return int|null
     * @throws LocalizedException
     */
    public function getCompanyId($companyTax)
    {
        $this->searchCriteriaBuilder->addFilter(
            'vat_tax_id',
            trim($companyTax)
        );
        $companyData = $this->companyRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();
        $companyId = null;
        $returnData = [
            'company_id' => null,
            'company_group_id' => null
        ];
        if ($companyData) {
            foreach ($companyData as $company) {
                $returnData = [
                    'company_id' => (int)$company->getId(),
                    'company_group_id' => $company->getCustomerGroupId(),
                ];
            }
        }
        return $returnData;
    }
    public function subscribeUserAndSetGroup($customerId, $groupId, $position)
    {
        $customer = $this->getCustomerById($customerId);

        if ($customer) {
            try {
                $customer->setGroupId($groupId);
                $customer->setCustomAttribute('position', $position);
                // set User subscribe or not
                $extensionAttributes = $customer->getExtensionAttributes();
                $extensionAttributes->setIsSubscribed($this->getRequest()->getParam('is_subscribed', false));
                $customer->setExtensionAttributes($extensionAttributes);

                $this->customerRepository->save($customer);
            } catch (LocalizedException $exception) {

            }
        }
    }
    public function getCustomerById($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (LocalizedException $exception) {
            $customer = null;
        }

        return $customer;
    }
}
