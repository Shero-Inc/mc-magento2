<?php
/**
 * mc-magento2 Magento Component
 *
 * @category Ebizmarts
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 5/23/17 3:36 PM
 * @file: Index.php
 */

namespace Ebizmarts\MailChimp\Controller\Customsubscribe;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Index extends Action
{

    /**
     * @var ResultFactory
     */
    private $_resultFactory;
    /**
     * @var \Ebizmarts\MailChimp\Helper\Data
     */
    private $_helper;
    /**
     * @var \Ebizmarts\MailChimp\Model\MailChimpWebhookRequestFactory
     */
    private $_chimpWebhookRequestFactory;
    private $_remoteAddress;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Ebizmarts\MailChimp\Helper\Data $helper
     * @param \Ebizmarts\MailChimp\Model\MailChimpWebhookRequestFactory $chimpWebhookRequestFactory
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory

    ) {


        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }

}
