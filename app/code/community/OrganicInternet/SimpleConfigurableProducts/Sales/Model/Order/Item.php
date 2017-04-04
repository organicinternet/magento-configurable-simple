<?php

class OrganicInternet_SimpleConfigurableProducts_Sales_Model_Order_Item extends Mage_Sales_Model_Order_Item {
	
	/**
	 * Get product options array
	 *
	 * @return array
	 */
	public function getProductOptions() {
		if ($options = $this->_getData('product_options')) {
			$options = unserialize($options);
			
			// If options not found, fetch them from super attribute
			if(!isset($options['attributes_info']) && isset($options['info_buyRequest']['super_attribute'])){
				$storeId = Mage::app()->getStore()->getId();
				$info = array();
				foreach($options['info_buyRequest']['super_attribute'] as $k => $v){
					$attributeModel = Mage::getModel('eav/entity_attribute')->load($k);
					
					$info[] = array(
						'label' => $attributeModel->getStoreLabel($storeId),
						'value' => $attributeModel->getSource()->getOptionText($v)
					);
				}
				
				if(!empty($info)) {
					$options['attributes_info'] = $info;
				}
			}
			
			return $options;
		}
		return array();
	}

}
