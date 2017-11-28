<?php
/**
 * Created by PhpStorm.
 * User: sherodesigns
 * Date: 27/10/2017
 * Time: 13:57
 */

namespace Ebizmarts\MailChimp\Controller\Customsubscribe;

use Braintree\Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;


class Subscribegroups extends Action
{
    /**
     * @var ResultFactory
     */
    private $_resultPageFactory;
    /**
     * @var \Ebizmarts\MailChimp\Helper\Data
     */
    protected $_helper;

    /**
     *
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $_subscriber;

    /**
     * @var \Mailchimp
     */
    protected $api;

    protected $messageManager;
    /**
     * Index constructor.
     * @param Context $context
     * @param \Ebizmarts\MailChimp\Helper\Data $helper
     * @param \Ebizmarts\MailChimp\Model\MailChimpWebhookRequestFactory $chimpWebhookRequestFactory
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Mailchimp $api,
        \Ebizmarts\MailChimp\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ){
        $this->_resultPageFactory   = $resultPageFactory;
        $this->_helper              = $helper;
        $this->messageManager       = $messageManager;
        $this->api = $this->_helper->getApi();
        $this->_subscriber= $subscriber;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Pagew
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if(!isset($params['email'])) {
            $this->messageManager->addError(__("Please fill the email input."));
            $this->_goBack();
            return;
        }
        if(!isset($params['group'])) {
            $this->messageManager->addError(__("Please select at least 1 group."));
            $this->_goBack();
            return;
        }

        $email = $params['email'];
        $groups = $params['group'];


        try{

            $md5HashEmail = md5(strtolower($email));
            $checkSubscriber = $this->_subscriber->loadByEmail($email);
            if(!$checkSubscriber->isSubscribed()) {
                $this->_subscriber->subscribe($email);

            } else { //subscribe for specific group.
                $interestids = array();
                foreach ($groups as $group){
                    $interestids[$group] = true;
                }
                $return =  $this->api->lists->members->addOrUpdate($this->_helper->getDefaultList(), $md5HashEmail, null,
                    'subscribed', null, $interestids, null, null, null, $email, 'subscribed');
            }
            $this->messageManager->addSuccess("You subscribed successfully.");
        }catch (\Exception $e) {
//            var_dump($e->getMessage());
            $this->messageManager->addError($e->getMessage());
        }
        $this->_goBack();
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }

    public function _goBack()
    {
        return $this->_redirect('*/*/index');
    }


//    /**
//     *
//     * @param $email
//     * @return mixed
//     */
//    public function checkIfgroupSubscribed($email)
//    {
//        $md5HashEmail = md5(strtolower($email));
//        $customerMailchimpData = $this->api->lists->members->get($this->_helper->getDefaultList(), $md5HashEmail);
//        return $customerMailchimpData['interests'];
//    }


}