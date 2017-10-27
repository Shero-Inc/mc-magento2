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
    private $_helper;

    /**
     *
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $_subscriber;

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
        \Magento\Newsletter\Model\Subscriber $subscriber
    ){
        $this->_resultPageFactory = $resultPageFactory;
        $this->_subscriber= $subscriber;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $params = $this->getRequest()->getParams();
        $email = $params['email'];

        try{

            $checkSubscriber = $this->_subscriber->loadByEmail($email);
            if(!$checkSubscriber->isSubscribed()) {
                $this->_subscriber->subscribe($email);
            } else {
                //TODO:: SUBSCRIBE FOR GROUPS HERE
            }

        }catch (\Exception $e) {
            var_dump($e);
            die();
        }

        $this->_redirect('*/*/index');
//        var_dump($params);
//        echo "subscribe groups";
//        die();
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }


}