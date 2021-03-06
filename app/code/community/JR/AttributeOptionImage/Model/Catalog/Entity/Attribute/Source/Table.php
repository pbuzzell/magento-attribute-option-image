<?php

/**
 * Catalog attribute selection table source
 *
 */
class JR_AttributeOptionImage_Model_Catalog_Entity_Attribute_Source_Table extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    /**
     * Retrieve Full Option values array
     * 
     * Take care of it with every new Magento version, it's a copy cat.
     * 
     * @param bool $withEmpty Add empty option to array
     * @param bool $defaultValues
     * @return array
     * 
     * @version 1.7.0.0
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false) 
    {
        $storeId = $this->getAttribute()->getStoreId();
        if (!is_array($this->_options)) {
            $this->_options = array();
        }
        if (!is_array($this->_optionsDefault)) {
            $this->_optionsDefault = array();
        }
        if (!isset($this->_options[$storeId])) {
            $collection = Mage::getResourceModel('aoi/catalog_attribute_option_collection') // [patch]
                ->setPositionOrder('asc')
                ->setAttributeFilter($this->getAttribute()->getId())
                ->setStoreFilter($this->getAttribute()->getStoreId())
                ->load();
            $this->_options[$storeId]        = $collection->toOptionArray();
            $this->_optionsDefault[$storeId] = $collection->toOptionArray('default_value');
        }
        $options = ($defaultValues ? $this->_optionsDefault[$storeId] : $this->_options[$storeId]);
        if ($withEmpty) {
            array_unshift($options, array('label' => '', 'value' => '', 'image' => null, 'thumbnail' => null)); // [patch]
        }
    
        return $options;
    }
    
    /**
     * Get a image for option value
     *
     * @param string|integer $value
     * @return string|array
     */
    public function getOptionImage($value) {
        return $this->_getOptionExtension($value, 'image');
    }
    
    /**
     * Get a thumbnail for option value
     *
     * @param string|integer $value
     * @return string|array
     */
    public function getOptionThumbnail($value) {
        return $this->_getOptionExtension($value, 'thumbnail');
    }
    
    /**
     * Get a extended field for option value
     *
     * @param string|integer $value
     * @param string $field
     * @return string|array
     */
    protected function _getOptionExtension($value, $field) {
        $isMultiple = false;
        if (strpos($value, ',')) {
            $isMultiple = true;
            $value = explode(',', $value);
        }
    
        $options = $this->getAllOptions(false);
    
        if ($isMultiple) {
            $values = array();
            foreach ($options as $item) {
                if (in_array($item['value'], $value)) {
                    $values[] = $item[$field];
                }
            }
            return $values;
        }
    
        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item[$field];
            }
        }
        return false;
    }
}