<?php
declare(strict_types=1);
namespace Integerbyte\MyRecaptcha\Observer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\RequestHandlerInterface;
use Magento\Framework\App\Response\RedirectInterface;

class RecapchaObserver implements ObserverInterface
{
protected $redirect;
private $url;
private $isCaptchaEnabled;
private $requestHandler;

public function __construct(UrlInterface $url, IsCaptchaEnabledInterface $isCaptchaEnabled, RequestHandlerInterface $requestHandler, RedirectInterface $redirect) {
  $this->url = $url;
  $this->isCaptchaEnabled = $isCaptchaEnabled;
  $this->requestHandler = $requestHandler;
  $this->redirect = $redirect;
}

public function execute(Observer $observer): void{
  $key = 'exregistration';
    if ($this->isCaptchaEnabled->isCaptchaEnabledFor($key)) {
        $controller = $observer->getControllerAction();
        $request = $controller->getRequest();
        $response = $controller->getResponse();
        $redirectOnFailureUrl = $this->redirect->getRedirectUrl();
        $this->requestHandler->execute($key, $request, $response, $redirectOnFailureUrl);
    }
}
}