<?php
/**
 * Created by PhpStorm.
 * User: sherodesigns
 * Date: 20/11/2017
 * Time: 14:40
 */

namespace Ebizmarts\MailChimp\Controller\Customsubscribe;


use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Customerdashboard extends Action
{


    /**
     * @var Ebizmarts\MailChimp\Helper\Interests
     */
    protected $interestsHelper;

    protected $dataHelper;

    protected $storeManager;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ebizmarts\MailChimp\Helper\Interests $interestsHelper,
        \Ebizmarts\MailChimp\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    ) {
        $this->interestsHelper      = $interestsHelper;
        $this->dataHelper           = $dataHelper;
        $this->_resultPageFactory   = $resultPageFactory;
        $this->storeManager         = $storeManager;
        parent::__construct($context);
    }


    public function execute()
    {

        $defaultList = $this->dataHelper->getDefaultList();
        $params = $this->getRequest()->getParams();
        $email = $params['email'];
//        var_dump($params ); die();
        try{

            $storeId = $this->interestsHelper->getStoreId();
            $customerSubscribedGroups = $this->interestsHelper->getCustomerSubscribedInterests($defaultList, $email);

            var_dump($customerSubscribedGroups);

            die();
//            $md5HashEmail = md5(strtolower($email));
//
//
//            $checkSubscriber = $this->_subscriber->loadByEmail($email);
//            if(!$checkSubscriber->isSubscribed()) {
//                $this->_subscriber->subscribe($email);
//
//            } else { //subscribe for specific group.
//                $interestids = array();
//                foreach ($groups as $group){
//                    $interestids[$group] = true;
//                }
//                $return =  $this->api->lists->members->addOrUpdate($this->_helper->getDefaultList(), $md5HashEmail, null,
//                    'subscribed', null, $interestids, null, null, null, $email, 'subscribed');
//            }
//            $this->messageManager->addSuccess("You subscribed successfully.");
        }catch (Exception $e) {
            var_dump($e->getMessage());
            $this->messageManager->addSuccess($e->getMessage());
        }
        $this->_redirect('*/*/index');
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
        
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}