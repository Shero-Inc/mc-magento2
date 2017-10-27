<?php
/**
 * mc-magento2 Magento Component
 *
 * @category Ebizmarts
 * @package mc-magento2
 * @author Ledian Hymetllari <ledian@sherodesigns.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 3/28/17 10:57 AM
 * @file: MonkeyListInterest.php
 */

namespace Ebizmarts\MailChimp\Model\Config\Source;

class MonkeyListInterestCategoryInterest implements \Magento\Framework\Option\ArrayInterface
{
    private $options = null;

    /**
     * MonkeyListInterest constructor.
     * @param \Ebizmarts\MailChimp\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Ebizmarts\MailChimp\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $storeId = (int) $request->getParam("store", 0);
        if($request->getParam('website',0)) {
            $scope = 'websites';
            $storeId = $request->getParam('website',0);
        }
        elseif($request->getParam('store',0)) {
            $scope = 'stores';
            $storeId = $request->getParam('store',0);
        }
        else {
            $scope = 'default';
        }

        if ($helper->getApiKey($storeId)) {
            try {

                $interestsDataGroups = $helper->getApi()->lists->interestCategory->interests
                    ->getAll($helper->getConfigValue(\Ebizmarts\MailChimp\Helper\Data::XML_PATH_LIST, $storeId,$scope),
                        $helper->getConfigValue(\Ebizmarts\MailChimp\Helper\Data::XML_PATH_INTEREST));


                $groups = array();
                foreach($interestsDataGroups['interests'] as $group){

                    $groups[] = array('id' => $group['id'], 'label' => $group['name']);

                }

                $this->options = $groups;
            } catch (\Exception $e) {
                $helper->log($e->getMessage());
            }
        }
    }
    public function toOptionArray()
    {

        if (is_array($this->options)) {

            $rc = [];
            foreach ($this->options as $group) {

                $rc[] = ['value' => $group['id'], 'label' => $group['label']];

            }
        } else {
            $rc[] = ['value' => 0, 'label' => __('---No Data---')];
        }

        return $rc;
    }
    public function toArray()
    {
        $rc = [];
        $rc[$this->options['id']] = $this->options['name'];
        return $rc;
    }
}
