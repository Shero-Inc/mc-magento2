<?php
/**
 * Created by PhpStorm.
 * User: ledian
 * Author: Ledian Hymetllari <ledian@sherocommerce.com>
 * Date: 17-11-21
 * Time: 6.08.MD
 */

namespace Ebizmarts\MailChimp\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckCustomerForCustomSubscribePage implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    )
    {
        $this->_customerSession = $customerSession;
        $this->redirect = $redirect;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        $controller = $observer->getControllerAction();
        if ($this->_customerSession->isLoggedIn() && $actionName == 'mailchimp_customsubscribe_index') {
            $this->redirect->redirect($controller->getResponse(), 'newsletter/manage');
        }


    }
}