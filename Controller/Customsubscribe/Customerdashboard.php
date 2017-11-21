<?php
/**
 * Created by PhpStorm.
 * User: sherodesigns
 * Date: 20/11/2017
 * Time: 14:40
 * Author: Ledian Hymetllari <ledian@sherocommerce.com>
 */

namespace Ebizmarts\MailChimp\Controller\Customsubscribe;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Customerdashboard extends Action
{
    /**
     * @var Ebizmarts\MailChimp\Helper\Interests
     */
    protected $interestsHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Ebizmarts\MailChimp\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Mailchimp
     */
    protected $api;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ebizmarts\MailChimp\Helper\Interests $interestsHelper,
        \Ebizmarts\MailChimp\Helper\Data $dataHelper,
        \Mailchimp $api,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager

    ) {
        $this->interestsHelper      = $interestsHelper;
        $this->dataHelper           = $dataHelper;
        $this->messageManager       = $messageManager;
        $this->_resultPageFactory   = $resultPageFactory;
        $this->api                  = $this->dataHelper->getApi();
        $this->storeManager         = $storeManager;
        parent::__construct($context);
    }


    public function execute()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $defaultList = $this->dataHelper->getDefaultList();
        $params = $this->getRequest()->getParams();
        $email = $params['email'];
        $groupsPost = isset($params['group']) ? $params['group'] : [];

        try{
            $storeId = $this->interestsHelper->getStoreId();

            /**
             * These are groups that customer is subscribed on mailchimp.
             * Since mailchimp response all groups for every list, below do a compare with groups
             * that are enabled for specific list on admin
             * @var  $customerSubscribedGroups
             */
            $customerSubscribedGroups = $this->interestsHelper->getArrayKeyAsArray(
                                        $this->interestsHelper->getCustomerSubscribedInterests($defaultList, $email));
            /**
             * Groups that are enabled in admin
             * @var  $adminAllowedGroupsForSubscrib
             */
            $adminAllowedGroupsForSubscrib = explode(",",$this->interestsHelper->getSelectedGroups($storeId));
            /**
             * Comparison between customer subscribed groups, and groups that are in admin, to get subscribed groups
             * by only that what are enabled in admin
             * @var  $subscribedGroupsBasedOnAllowedGroups
             */
            $subscribedGroupsBasedOnAllowedGroups = array_intersect($adminAllowedGroupsForSubscrib, $customerSubscribedGroups);
            /**
             * Difference between groups from post and groups that customer is subscribed,
             * to find unsubscribed groups
             * @var  $groupsToUnsubscribe
             */
            $groupsToUnsubscribe = array_diff($subscribedGroupsBasedOnAllowedGroups, $groupsPost);

            $md5HashEmail = $this->interestsHelper->getMd5HashEmail($email);

            /** Groups to subscribe */
            $interestidsTosubscribe = array();
            foreach ($groupsPost as $group){
                $interestidsTosubscribe[$group] = true;
            }
            $return =  $this->api->lists->members->addOrUpdate($this->dataHelper->getDefaultList(), $md5HashEmail, null,
                'subscribed', null, $interestidsTosubscribe, null, null, null, $email, 'subscribed');

            /** Groups to unsubscribe */
            $interestidsToUnsubscribe = array();
            foreach ($groupsToUnsubscribe as $group){
                $interestidsToUnsubscribe[$group] = false;
            }
            $return =  $this->api->lists->members->addOrUpdate($this->dataHelper->getDefaultList(), $md5HashEmail, null,
                'subscribed', null, $interestidsToUnsubscribe, null, null, null, $email, 'subscribed');

            /** If customer is unsubscribed from all groups, unsubscribing it from lists as well */
            if(empty($groupsPost)) {
                $this->api->lists->members->update($this->dataHelper->getDefaultList(), $md5HashEmail, null, 'unsubscribed');
            }
            $this->messageManager->addSuccess("Your changes saved successfully.");
        }catch (Exception $e) {
            var_dump($e->getMessage());
            $this->messageManager->addSuccess($e->getMessage());
        }
        $this->_redirect('newsletter/manage/');
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }


    
}