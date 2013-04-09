<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.idealiagroup.com/magento-ext-license.html
 *
 * @category   IG
 * @package    IG_Cmslevels
 * @copyright  Copyright (c) 2011-2012 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://www.idealiagroup.com/magento-ext-license.html
 */
 
class IG_Cmslevels_Block_Cms_Page extends Mage_Cms_Block_Page
{
	/**
	 * Check if component is enabled or not
	 *
	 * @return bool
	 */
	public function getIsEnabled()
	{
		return Mage::helper('ig_cmslevels')->getIsEnabled();
	}
	
	/**
	 * Check if component is enabled or not
	 *
	 * @return bool
	 */
	public function getLevelSeparator()
	{
		return Mage::helper('ig_cmslevels')->getLevelSeparator();
	}
	
	/**
     * Prepare global layout
     *
     * @return IG_Cmslevels_Block_Cms_Page
     */
    protected function _prepareLayout()
    {
		if (!$this->getIsEnabled())
			return parent::_prepareLayout();
		
        $page = $this->getPage();

        // show breadcrumbs
        if (Mage::getStoreConfig('web/default/show_cms_breadcrumbs') &&
            ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) &&
            ($page->getIdentifier()!==Mage::getStoreConfig('web/default/cms_home_page')) &&
            ($page->getIdentifier()!==Mage::getStoreConfig('web/default/cms_no_route'))
		) {
			$titleParts = explode($this->getLevelSeparator(), $page->getTitle());
			$urlParts = explode('/', $page->getIdentifier());
			$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
			
			$link = array();
			for ($i=0; $i<count($titleParts); $i++)
			{
				$linkUrl = '';
				
				$titlePart = trim($titleParts[$i]);
				if (count($urlParts)-1 > $i)
				{
					$link[] = $urlParts[$i];
					$identifier = implode('/', $link);
					
					$parent = Mage::getModel('cms/page')->load($identifier, 'identifier');

					if ($parent->getId())
					{
						$linkUrl = Mage::getBaseUrl().$parent->getIdentifier();
					}
					
					if (!$linkUrl)
					{
						$parent = Mage::getModel('cms/page')->load($identifier.'.html', 'identifier');
						
						if ($parent->getId())
							$linkUrl = Mage::getBaseUrl().$parent->getIdentifier();
					}
				}
				
				if ($linkUrl)
				{
					$breadcrumbs->addCrumb($titlePart, array('label'=>$titlePart, 'title'=>$titlePart, 'link' => $linkUrl));
					$page->setTitle($titlePart);
				}
				else
				{
					$breadcrumbs->addCrumb($titlePart, array('label'=>$titlePart, 'title'=>$titlePart));
					$page->setTitle($titlePart);
				}
			}
        }
		
		$root = $this->getLayout()->getBlock('root');
        if ($root)
		{
            $root->addBodyClass('cms-'.$page->getIdentifier());
        }

        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($page->getTitle());
            $head->setKeywords($page->getMetaKeywords());
            $head->setDescription($page->getMetaDescription());
        }

        return $this;
    }
}
