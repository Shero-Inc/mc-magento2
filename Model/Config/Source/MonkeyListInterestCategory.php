<?php
/**
 * mc-magento2 Magento Component
 *
 * @category Ebizmarts
 * @package mc-magento2
 * @author Ledian Hymetllari <ledian@sherodesigns.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

 * @file: MonkeyListInterestCategory.php
 */

namespace Ebizmarts\MailChimp\Model\Config\Source;

class MonkeyListInterestCategory implements \Magento\Framework\Option\ArrayInterface
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

                $interestsData = $helper->getApi()->lists->interestCategory
                    ->getAll($helper->getConfigValue(\Ebizmarts\MailChimp\Helper\Data::XML_PATH_LIST, $storeId,$scope));
                $categories = array();
                foreach($interestsData['categories'] as $group){
                    $categories[] = array('id' => $group['id'], 'label' => $group['title']);
                }

                $this->options = $categories;
            } catch (\Exception $e) {
                $helper->log($e->getMessage());
            }
        }
    }
    public function toOptionArray()
    {

        if (is_array($this->options)) {

            $rc = [];
            $rc[] = ['value' => -1, 'label' => 'Select Interest to show groups'];
            foreach ($this->options as $category) {

                $rc[] = ['value' => $category['id'], 'label' => $category['label']];

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
