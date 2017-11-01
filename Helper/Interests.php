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



    /**
     * Data helper
     * @var \Ebizmarts\MailChimp\Helper\Data
     */
    protected $mailchimpDataHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;


    public function __construct(
        \Ebizmarts\MailChimp\Helper\Data $mailchimpDataHelper,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->mailchimpDataHelper  = $mailchimpDataHelper;
        $this->request              = $request;
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
     * Get selected interest category from admin
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


}