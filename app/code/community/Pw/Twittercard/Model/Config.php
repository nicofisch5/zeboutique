<?php

class Pw_Twittercard_Model_Config extends Varien_Object
{
    
    public function getConfigData($key, $default=false)
    {
        if (!$this->hasData($key)) {
            $value = Mage::getStoreConfig('catalog/twittercard/'.$key);
            if (is_null($value) || false===$value) {
                $value = $default;
            }
            $this->setData($key, $value);
        }
        return $this->getData($key);
    }
  
    public function getTwitterUsername ()
    {
        return $this->getConfigData('twitter_username');
    }
}