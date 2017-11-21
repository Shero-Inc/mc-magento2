<?php


namespace Ebizmarts\MailChimp\Helper;

use Magento\Framework\Exception\ValidatorException;
use Magento\Store\Model\Store;
use Symfony\Component\Config\Definition\Exception\Exception;

class Interests extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_ENABLEINTERESTGROUPCATEGORY          = 'mailchimp/general/activeinterests';
    const XML_PATH_INTEREST                              = 'mailchimp/general/monkeyinterestcategory';
    const XML_PATH_INTERESTGROUPS                        = 'mailchimp/general/monkeyinterestcategorygroups';
    const XML_PATH_SUBSCRIBE_ALL_GROUPS_FOOTER          =   'mailchimp/general/subscribe_group_footer';

    protected $_storeManager;

    /**
     * Data helper
     * @var \Ebizmarts\MailChimp\Helper\Data
     */
    protected $mailchimpDataHelper;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    protected $subscriber;


    public function __construct(
        \Ebizmarts\MailChimp\Helper\Data $mailchimpDataHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $session,
        \Magento\Newsletter\Model\Subscriber $subscriber

    )
    {
        $this->_storeManager        = $storeManager;
        $this->mailchimpDataHelper  = $mailchimpDataHelper;
        $this->request              = $request;
        $this->customerSession      = $session;
        $this->subscriber           = $subscriber;
    }


    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Check if group interests are enabled
     * @param $store
     * @return mixed
     */
    public function isGroupInterestEnabled($store)
    {
        return $this->mailchimpDataHelper->getConfigValue(self::XML_PATH_ENABLEINTERESTGROUPCATEGORY, $store);
    }

    public function allowToSubscribeAllGroupsFooter($store)
    {
        return $this->mailchimpDataHelper->getConfigValue(self::XML_PATH_SUBSCRIBE_ALL_GROUPS_FOOTER, $store);
    }

    /**
     * Get selected interest category from admin, to be allowed for subscribe on frontend
     * @param $store
     * @return mixed
     */
    public function getSelectedInterestCategory($store)
    {
        return $this->mailchimpDataHelper->getConfigValue(self::XML_PATH_INTEREST, $store);
    }


    /**
     * Get all groups based on Category selected from admin, not selected groups from admin, but all groups that
     * are in admin, by interest category selected
     * @return array
     */
    public function getAllGroups()
    {
        $scopeData = $this->getScope();

        $storeId =$scopeData[0]['storeId'];
        $scope = $scopeData[0]['scope'];

        if ($this->mailchimpDataHelper->getApiKey($storeId)) {
            try {

                $interestsDataGroups = $this->mailchimpDataHelper->getApi()->lists->interestCategory->interests
                    ->getAll($this->mailchimpDataHelper->getConfigValue(\Ebizmarts\MailChimp\Helper\Data::XML_PATH_LIST, $storeId,$scope),
                        $this->mailchimpDataHelper->getConfigValue(\Ebizmarts\MailChimp\Helper\Data::XML_PATH_INTEREST));


                $groups = array();
                foreach($interestsDataGroups['interests'] as $group){

                    $groups[] = array('id' => $group['id'], 'label' => $group['name']);

                }

                return $groups;

            } catch (\Exception $e) {
                $this->mailchimpDataHelper->log($e->getMessage());
            }
        }
    }

    /**
     * Get selected groups by admin, groups that are selected after list is selected
     * @param $store
     * @param bool $showLabels this shows labels of the groups if true, if no, show only group id
     * @return array|mixed
     */
    public function getSelectedGroups($store, $showLabels = false)
    {

        if($this->canShowGroups($store)){
            if($showLabels){
                $adminSelectedGroups = array();

                $groupsConfig = $this->mailchimpDataHelper->getConfigValue(self::XML_PATH_INTERESTGROUPS, $store);

                $selectedGroups = explode(',',$groupsConfig);
                foreach ($this->getAllGroups() as $allgroup) {
                    if(in_array($allgroup['id'], $selectedGroups)) {
                        $adminSelectedGroups[] = array('id' => $allgroup['id'], 'label' =>$allgroup['label']);
                    }
                }

                return $adminSelectedGroups;
            }
            return $this->mailchimpDataHelper->getConfigValue(self::XML_PATH_INTERESTGROUPS, $store);
        }

        return false;
    }

    /**
     * helper function to check if groups are able to show in frontend
     * @param $store
     * @return bool
     */
    public function canShowGroups($store)
    {
        if($this->getSelectedInterestCategory($store) != '-1' && $this->isGroupInterestEnabled($store)){
            return true;
        }

        return false;
    }

    /**
     * Get scope for site
     * @return array
     */
    public function getScope()
    {
        $data = array();
        $storeId = (int) $this->request->getParam("store", 0);
        if($this->request->getParam('website',0)) {
            $scope = 'websites';
            $storeId = $this->request->getParam('website',0);

        }
        elseif($this->request->getParam('store',0)) {
            $scope = 'stores';
            $storeId = $this->request->getParam('store',0);

        }
        else {
            $scope = 'default';

        }

        $data[] = array('storeId' =>$storeId, 'scope' =>$scope);
        return $data;
    }

    /**
     * If customer is subscribed, we are returning all interests for this customer.
     * @param $listId
     * @param $customerEmail
     */
    public function getCustomerSubscribedInterests($listId, $subscriberEmail)
    {
        if($this->subcriberExists($subscriberEmail)){
            $subscriberHashEmail = $this->getMd5HashEmail($subscriberEmail);
            $scopeData = $this->getScope();

            $storeId =$scopeData[0]['storeId'];
            $scope = $scopeData[0]['scope'];

            if($this->mailchimpDataHelper->getApiKey($storeId)) {
                $interestsDataGroups = $this->mailchimpDataHelper->getApi()->lists->members->get($listId, $subscriberHashEmail);
                return $interestsDataGroups['interests'];
            }
        }
        return false;
    }

    /**
     * @param $listId
     * @param $subscriberHashEmail
     */
//    public function isInterestSubscribed($listId, $subscriberHashEmail)
//    {
//        $scopeData = $this->getScope();
//        $subscribedInterests = $this->getCustomerSubscribedInterests($listId, $subscriberHashEmail);
//        $storeId =$scopeData[0]['storeId'];
//        $selectedGroups = explode(",",$this->getSelectedGroups($storeId));
//
//        foreach ($subscribedInterests as $interestId => $interest) {
//            if(in_array($interestId, $selectedGroups)) {
//                return true;
//            }
//        }
//    }


    public function getCustomerSession()
    {

        if($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer();
//            echo   $customerSession->getCustomer()->getName()."<br/>";  // get  Full Name
//            echo   $customerSession->getCustomer()->getFirstname()."<br/>";  // get Customer First name
//            echo   $customerSession->getCustomer()->getLastname()."<br/>";  // get  Last Name
        }
        return false;
    }

    /**
     *
     * @param $customerEmail
     * @return bool
     */
    public function isCustomerSubscribed($customerEmail)
    {
        $checkSubscriber = $this->subscriber->loadByEmail($customerEmail);
        if ($checkSubscriber->isSubscribed()) {
            // Customer is subscribed
            return true;
        }
        return false;
    }

    /**
     * Check if customer exists as subscriber in Magento.
     * @param $customerEmail
     * @return bool
     */
    public function subcriberExists($customerEmail)
    {
        $checkSubscriber = $this->subscriber->loadByEmail($customerEmail);
        if ($checkSubscriber->getId()) {
            // subscriber exits
            return true;
        }
        return false;
        
    }

    /**
     * Get email as md5 hashed and lowercase
     * @param $customerEmail
     * @return string
     */
    public function getMd5HashEmail($customerEmail)
    {
        return md5(strtolower($customerEmail));
    }

    /**
     * Get all array keys as array for specific array
     * @param $array
     * @return array
     */
    public function getArrayKeyAsArray($array)
    {
        $keys = [];
        foreach ($array as $key => $value) {
            $keys[] = $key;
        }

        return $keys;
    }
}