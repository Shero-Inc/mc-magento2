<?php
/**
 *
 * @author    Ledian Hymetllari<ledian@sherocommerce.com>
 */

namespace Ebizmarts\MailChimp\Block;

use Magento\Framework\View\Element\Template;

class Customsubscribe extends Template
{

    /**
     * @var \Ebizmarts\MailChimp\Helper\Interests
     */
    protected $interestsHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ebizmarts\MailChimp\Helper\Interests $interestsHelper,
        \Magento\Framework\App\RequestInterface $request
    )
    {

        parent::__construct($context);
        $this->interestsHelper = $interestsHelper;
        $this->request = $request;


    }

    /**
     * Get all groups based on selected interest category
     * @return array
     */
    public function getGroups()
    {
        return $this->interestsHelper->getAllGroups();

    }


    /**
     * Get selected groups from admin
     * @return array|mixed
     */
    public function getSelectedGroups()
    {
        $scopeData = $this->interestsHelper->getScope();
        $storeId = $scopeData[0]['storeId'];
        return $this->interestsHelper->getSelectedGroups($storeId, true);

    }


}