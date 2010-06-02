<?php

#SCP: Passed parent productid (which is set by the SCP ajax controller) as the SCP ajax controller needs it again
#to be able to verify that this simple product belongs to a configurable product that's visible

class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Media extends Mage_Catalog_Block_Product_View_Media {

    public function getGalleryUrl($image=null)
    {
        #$params = array('id'=>$this->getProduct()->getId());
        $params = array(
            'id'=>$this->getProduct()->getId(),
            'pid'=>$this->getProduct()->getCpid()
        );
        if ($image) {
            $params['image'] = $image->getValueId();
            return $this->getUrl('*/*/gallery', $params);
        }
        return $this->getUrl('*/*/gallery', $params);
    }


}


