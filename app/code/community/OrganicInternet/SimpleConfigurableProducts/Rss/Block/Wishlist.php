<?php

class OrganicInternet_SimpleConfigurableProducts_Rss_Block_Wishlist
    extends Mage_Rss_Block_Wishlist
{
    protected function _toHtml()
    {
        $descrpt = Mage::helper('core')->urlDecode($this->getRequest()->getParam('data'));
        $data = explode(',',$descrpt);
        $cid = (int)$data[0];

        $rssObj = Mage::getModel('rss/rss');

        if ($cid) {
            $customer = Mage::getModel('customer/customer')->load($cid);
            if ($customer && $customer->getId()) {

                $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer($customer, true);

                $newurl = Mage::getUrl('wishlist/shared/index',array('code'=>$wishlist->getSharingCode()));
                $title = Mage::helper('rss')->__('%s\'s Wishlist',$customer->getName());
                $lang = Mage::getStoreConfig('general/locale/code');

                $data = array('title' => $title,
                    'description' => $title,
                    'link'        => $newurl,
                    'charset'     => 'UTF-8',
                    'language'    => $lang
                );
                $rssObj->_addHeader($data);

                $collection = $wishlist->getProductCollection()
                            ->addAttributeToSelect('url_key')
                            ->addAttributeToSelect('name')
                            ->addAttributeToSelect('price')
                            ->addAttributeToSelect('thumbnail')
                            ->addAttributeToFilter('store_id', array('in'=> $wishlist->getSharedStoreIds()))
                            ->load();

                foreach($collection as $item){
                    $product = Mage::getModel('catalog/product');
                    $product->load($item->getProductId());
                    $description = '<table><tr>'.
                        '<td><a href="'.$item->getProductUrl().'"><img src="' . $this->helper('catalog/image')->init($item, 'thumbnail')->resize(75, 75) . '" border="0" align="left" height="75" width="75"></a></td>'.
                        '<td  style="text-decoration:none;">'.
                        $product->getDescription().
                        ($product->isConfigurable() ? '<p> Price From:' : '<p> Price:').
                        Mage::helper('core')->currency($product->getPrice()).
                        ($product->getPrice() != $product->getFinalPrice() ? ' Special Price:'. Mage::helper('core')->currency($product->getFinalPrice()) : '').
                        ($item->getDescription() && $item->getDescription() != Mage::helper('wishlist')->defaultCommentString() ? '<p>Comment: '.$item->getDescription().'<p>' : '').
                        '</td>'.
                        '</tr></table>';
                    $data = array(
                        'title'         => $product->getName(),
                        'link'          => $product->getProductUrl(),
                        'description'   => $description,
                        );
                    $rssObj->_addEntry($data);
                }

            }

        } else {
             $data = array('title' => Mage::helper('rss')->__('Cannot retrieve the wishlist'),
                    'description' => Mage::helper('rss')->__('Cannot retrieve the wishlist'),
                    'link'        => Mage::getUrl(),
                    'charset'     => 'UTF-8',
                );
                $rssObj->_addHeader($data);
        }
        return $rssObj->createRssXml();
    }
}

?>
