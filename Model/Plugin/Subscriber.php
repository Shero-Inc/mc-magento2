<?php
/**
 * Ebizmarts_MailChimp Magento JS component
 *
 * @category    Ebizmarts
 * @package     Ebizmarts_MailChimp
 * @author      Ebizmarts Team <info@ebizmarts.com>
 * @copyright   Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Ebizmarts\MailChimp\Model\Plugin;

class Subscriber
{
    /**
     * @var \Ebizmarts\MailChimp\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Ebizmarts\MailChimp\Helper\Interests
     */
    protected $interestsHelper;
    /**
     * @param \Ebizmarts\MailChimp\Helper\Data $helper
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customer
     * @param \Magento\Customer\Model\Session $customerSession
     */
    protected $_api = null;

    public function __construct(
        \Ebizmarts\MailChimp\Helper\Data $helper,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\RequestInterface $request,
        \Ebizmarts\MailChimp\Helper\Interests $interestsHelper



    ) {
    
        $this->_helper          = $helper;
        $this->_customer        = $customer;
        $this->_customerSession = $customerSession;
        $this->_storeManager    = $storeManager;
        $this->_api             = $this->_helper->getApi();
        $this->redirect         = $redirect;
        $this->request          = $request;
        $this->interestsHelper  = $interestsHelper;
    }

    public function beforeUnsubscribeCustomerById(
        $subscriber,
        $customerId
    ) {
        die('work');
//        $this->_helper->log(__METHOD__);
        $subscriber->loadByCustomerId($customerId);
//        if ($subscriber->getMailchimpId() != null) {
            $api = $this->_api;
        try {
            $md5HashEmail = md5(strtolower($subscriber->getSubscriberEmail()));
            $api->lists->members->update($this->_helper->getDefaultList(), $md5HashEmail, null, 'unsubscribed');
//                $subscriber->setMailchimpId('')->save();
        } catch (\Exception $e) {
            $this->_helper->log($e->getMessage());
        }
//        }
        return [$customerId];
    }

    public function beforeSubscribeCustomerById(
        $subscriber,
        $customerId
    ) {
//        $this->_helper->log(__METHOD__);
        $subscriber->loadByCustomerId($customerId);
        $subscriber->setImportMode(true);
        $storeId = $subscriber->getStoreId();
        if ($this->_helper->isMailChimpEnabled($storeId)) {
            $customer = $this->_customer->getById($customerId);
            $email = $customer->getEmail();
            $mergeVars = $this->_helper->getMergeVars($customer, $email);
            $api = $this->_api;
            $isSubscribeOwnEmail = $this->_customerSession->isLoggedIn()
                && $this->_customerSession->getCustomerDataObject()->getEmail() == $subscriber->getSubscriberEmail();
            if ($this->_helper->isDoubleOptInEnabled($storeId) && !$isSubscribeOwnEmail) {
                $status = 'pending';
            } else {
                $status = 'subscribed';
            }
            try {
                $emailHash = md5(strtolower($customer->getEmail()));
                if (!$subscriber->getMailchimpId()) {
                    $return = $api->lists->members->addOrUpdate($this->_helper->getDefaultList(), $emailHash, null, $status, $mergeVars, null, null, null, null, $email, $status);
//                    $this->_helper->log($return);
//                    if (isset($return['id'])) {
//                        $subscriber->setMailchimpId($return['id']);
//                    }
                }
//                $subscriber->setMailchimpId($emailHash)->save();
            } catch (\Exception $e) {
                $this->_helper->log($e->getMessage());
            }
        }
        return [$customerId];
    }

    public function beforeSubscribe(
        $subscriber,
        $email
    ) {


//        $this->_helper->log(__METHOD__);
        $storeId = $this->_storeManager->getStore()->getId();
        if ($this->_helper->isMailChimpEnabled($storeId)) {
            $api = $this->_api;
            if ($this->_helper->isDoubleOptInEnabled($storeId)) {
                $status = 'pending';
            } else {
                $status = 'subscribed';
            }
            $mergeVars = $this->_helper->getMergeVars($subscriber, $email);
            try {
                $md5HashEmail = md5(strtolower($email));
                /**
                 * subscribe for groups , this will be used for newsletter form in footer, to whitelist
                 * all groups that are selected from admin
                 *
                 */
                $params = $this->request->getParams();
                if(isset($params['customsubscribe'])){ //if subscription comes from new page
                    $interestids = array();
                    $groups = $params['group'];
                    foreach ($groups as $group) {
                        $interestids[$group]= true;
                    }
                    $return = $api->lists->members->addOrUpdate($this->_helper->getDefaultList(), $md5HashEmail, null, $status, $mergeVars, $interestids, null, null, null, $email, $status);

                }else if($this->interestsHelper->getSelectedGroups($storeId) &&
                    $this->interestsHelper->allowToSubscribeAllGroupsFooter($storeId)) { //if subscription comes from footer newsletter
                    /** Subscribe to all groups that are selected from admin, for footer form **/
                    $groupSelectedToSubscribe = explode(',',$this->interestsHelper->getSelectedGroups($storeId));

                    $interestids = array();
                    foreach ($groupSelectedToSubscribe as $interestId) {
                        $interestids[$interestId]= true;
                    }
                    $return = $api->lists->members->addOrUpdate($this->_helper->getDefaultList(), $md5HashEmail, null, $status, $mergeVars, $interestids, null, null, null, $email, $status);

                } else {//subscribe for list only, no groups included
                    $return = $api->lists->members->addOrUpdate($this->_helper->getDefaultList(), $md5HashEmail, null, $status, $mergeVars, null, null, null, null, $email, $status);
                }
            } catch (\Exception $e) {

                var_dump($e->getMessage());
                die();
                $this->_helper->log($e->getMessage());
            }
        }
        return [$email];
    }

    public function beforeUnsubscribe(
        $subscriber
    ) {
//        $this->_helper->log(__METHOD__);
//        if ($subscriber->getMailchimpId()) {
//            $this->_helper->log('has id');
            $api = $this->_helper->getApi();
        try {
            $md5HashEmail = md5(strtolower($subscriber->getSubscriberEmail()));
            $api->lists->members->update($this->_helper->getDefaultList(), $md5HashEmail, null, 'unsubscribed');
//                $subscriber->setMailchimpId('');
        } catch (\Exception $e) {
            $this->_helper->log($e->getMessage());
        }
//        }
        return null;
    }
}
