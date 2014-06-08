<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Zeboutique
 * @package     Zeboutique_Core
 * @copyright   Copyright (c) 2014 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Core
 *
 * @category    Zeboutique
 * @package     Zeboutique_Core
 * @author      Zeboutique
 */
class Zeboutique_Core_Model_Observer extends Mage_Core_Model_Observer
{
    const CACHE_LIFETIME = 86400; //1 jour (attention : valeur arbitraire)
    const PREFIX = "APCA_CACHEKEY";
    const BOF = "BOF";
    const EOF = "EOF";
    const BLOC_CLASS = "BLOCCLASS";
    const SEP = "_";
    const STORE = "STORE";
    const NAME_IN_LAYOUT = "NIL";
    const TEMPLATE = "TEMPLATE";
    const ROUTENAME = "ROUTENAME";
    const CONTROLLER_NAME = "CONTROLLERNAME";
    const CONTROLLER_MODULE = "CONTROLLERMODULE";
    const CONTROLLER_KEY = "CONTROLLERKEY";
    const ACTION_NAME = "ACTIONNAME";
    const ACTION_KEY = "ACTIONKEY";
    const ALLOW_SAVE_COOKIE = 'ALLOWSAVECOOKIE';
    const PREVENT_TURPENTINE = 'PREVENT_TURPENTINE';
    const CACHE_KEY_INFO = 'CACHEKEYINFO';
    const REQ_SECURE = 'REQSECURE';
    const CURRENT_CATEGORY='CURCATEGORY';
    const CURRENT_PRODUCT='CURPRODUCT';
    const PARAM='PARAM';

    /**
     * Add block caching
     *
     * @param $observer
     */
    public function configureCache($observer)
    {
        $evt = $observer->getData('event');
        $block = $evt->getData('block');
        $bc = get_class($block);
        //Mage::log('CONFIGURE CACHE : '.$bc);
        $work = false;

        switch ($bc) {
            // Block to exclude
            case 'Mage_Core_Model_Layout_Element':
            case 'Mage_Page_Block_Template_Links':
            case 'Mage_Checkout_Block_Cart_Sidebar':
                $work = false;
                break;

            // Home
            case 'Zeon_Manufacturer_Block_Home':
            case 'Magentothem_Featuredproductvertscroller_Block_Featuredproductvertscroller':
            case 'Magentothem_Mostviewedproduct_Block_Mostviewedproduct':
            case 'Magentothem_Newproductslider_Block_Newproductslider':
            case 'Magentothem_Banner7_Block_Banner7':

            // Product page
            case 'Mage_Catalog_Block_Product_View':
            case 'Mage_Catalog_Block_Product_View_Type_Configurable':
            case 'Mage_Catalog_Block_Product_View_Options':
            case 'Mage_Payment_Block_Catalog_Product_View_Profile':
            case 'Mage_Catalog_Block_Product_Price':
            case 'Mage_Catalog_Block_Product_View_Tabs':
            case 'Mage_Catalog_Block_Product_View_Description':
            case 'Mage_Catalog_Block_Product_List_Upsell':
            case 'Mage_Catalog_Block_Product_View_Attributes':
            case 'Mage_Review_Block_Product_View_List':
            case 'Mage_Catalog_Block_Layer_View':

            // Category page
            case 'Mage_Catalog_Block_Category_View':
            case 'Mage_Catalog_Block_Product_List':
            case 'Magentothem_Verticalmenu_Block_Verticalmenu':
            case 'Amasty_Review_Block_Sidebar':

            // All pages
            case 'Fishpig_Wordpress_Block_Menu':
            case 'Fishpig_Wordpress_Block_Post_Associated':
            case 'IG_Cmslevels_Block_Cms_Page':
            case 'Pw_Twittercard_Block_Twittercard':
            case 'Mage_Page_Block_Html_Topmenu':
            case 'Mage_Page_Block_Html_Footer':
            case 'Mage_Cms_Block_Block':
            case 'Mage_Page_Block_Template_Links':
            case 'Mage_Page_Block_Switch':
            case 'Mage_Page_Block_Html_Head':
            case 'Mage_Page_Block_Js_Cookie':
            //case 'Apca_Turpentine_Block_Store':
            //case 'Nexcessnet_Turpentine_Block_Notices':
            case 'Mage_Page_Block_Html_Notices':
            case 'Mage_GoogleAnalytics_Block_Ga':
            case 'Mage_Page_Block_Html_Breadcrumbs':
                $work = true;
                break;
            case 'Mage_Core_Block_Template':
                $tfile = $block->getTemplateFile();
                if ($tfile == 'frontend/base/default/template/directory/js/optional_zip_countries.phtml') {
                    $work = true;
                }
                if ($tfile == 'frontend/default/apca/template/page/html/socialnetworks/body-start.phtml') {
                    $work = true;
                }
                if ($tfile == 'frontend/default/apca/template/customer/form/header.toplinks.cookie.phtml') {
                    $work = true;
                }
                if ($tfile == 'frontend/default/apca/template/catalogsearch/form.mini.phtml') {
                    $work = true;
                }
                if($tfile =='frontend/default/apca/template/page/html/home/reinsurance.phtml'){
                    $work=true;
                }
                if($tfile=='frontend/default/apca/template/page/html/socialnetworks/body-end.phtml'){
                    $work=true;
                }
                break;
            case 'Mage_Core_Block_Text_List':
                $nil = $block->getNameInLayout();
                if ($nil == 'checkout.links') {
                    $work = true;
                }
            case 'Mage_Cms_Block_Block':
                $nil = $block->getNameInLayout();
                if ($nil == 'top.navigation.links') {
                    $work = true;
                }
            default:
                break;
        }
        if ($work) {
            /* on positionne le temps de cache du bloc */
            $block->setData('cache_lifetime', self::CACHE_LIFETIME);
            /*
             * éléments constitutifs fondamentaux de la clef :
             * le store
             * la classe du bloc
             * le fichier de template
             * le name in layout
             * le cache key info
             * Et on fait un ajout structuré pour essayer de faire des clefs uniques
             */

            $store = Mage::app()->getStore();
            $sid = $store->getId();
            if (is_null($sid)) {
                $sid = "";
            }
            $tfile = $block->getTemplateFile();
            if (is_null($tfile)) {
                $tfile = "";
            }
            $nil = $block->getNameInLayout();
            if (is_null($nil)) {
                $nil = "";
            }
            $cki = implode('|', $block->getCacheKeyInfo());
            $key = "";
            $key.=self::PREFIX;
            $key.=self::SEP . self::BOF . self::STORE . $sid . self::EOF . self::STORE; //store
            $key.=self::SEP . self::BOF . self::BLOC_CLASS . $bc . self::EOF . self::BLOC_CLASS; //bloc class
            $key.=self::SEP . self::BOF . self::TEMPLATE . $tfile . self::EOF . self::TEMPLATE; //template file
            $key.=self::SEP . self::BOF . self::NAME_IN_LAYOUT . $nil . self::EOF . self::NAME_IN_LAYOUT; //name in layout
            $key.=self::SEP . self::BOF . self::CACHE_KEY_INFO . $cki . self::EOF . self::CACHE_KEY_INFO; //cache key info

            switch ($bc) {
                //les classes pour lesquelles on n'a pas besoin de rajouter d'info
                //case 'Apca_Page_Block_Html_Topmenu':
                case 'Mage_Cms_Block_Block':
                case 'Mage_Core_Block_Text_List':
                case 'Mage_Page_Block_Template_Links':
                case 'Mage_Page_Block_Switch':
                case 'Mage_Page_Block_Js_Cookie':
                //case 'Apca_Turpentine_Block_Store':
                    break;

                case 'Mage_Page_Block_Html_Breadcrumbs'://il y a besoin de rajouter la categorie courante et le produit courant
                case 'Mage_Catalog_Block_Category_View':
                case 'Mage_Catalog_Block_Product_List':
                case 'Mage_Catalog_Block_Product_View':
                case 'Mage_Catalog_Block_Product_View_Type_Configurable':
                case 'Mage_Catalog_Block_Product_View_Options':
                case 'Mage_Payment_Block_Catalog_Product_View_Profile':
                case 'Mage_Catalog_Block_Product_Price':
                case 'Mage_Catalog_Block_Product_View_Tabs':
                case 'Mage_Catalog_Block_Product_View_Description':
                case 'Mage_Catalog_Block_Product_List_Upsell':
                case 'Mage_Catalog_Block_Product_View_Attributes':
                case 'Mage_Review_Block_Product_View_List':
                case 'Mage_Catalog_Block_Layer_View':
                case 'Magentothem_Verticalmenu_Block_Verticalmenu':
                case 'Amasty_Review_Block_Sidebar':
                    $curcat=Mage::registry('current_category');
                    $curprod=Mage::registry('current_product');
                    $curcatid=!is_null($curcat)?$curcat->getId():"";
                    $curprodid=!is_null($curprod)?$curprod->getId():"";
                    $key.=self::SEP.self::BOF.self::CURRENT_CATEGORY.$curcatid.self::EOF.self::CURRENT_CATEGORY;
                    $key.=self::SEP.self::BOF.self::CURRENT_PRODUCT.$curprodid.self::EOF.self::CURRENT_PRODUCT;

                    $request = Mage::app()->getRequest();
                    if ($params = $request->getParams()) {
                        foreach ($params as $paramName => $paramValue) {
                            if ($paramName == 'id') {
                                continue;
                            }
                            $key.=self::SEP.self::BOF.self::PARAM.$paramName.$paramValue.self::EOF;
                        }
                    }

                case 'Mage_Core_Block_Template':
                    if ($tfile == 'frontend/default/apca/emplate/catalogsearch/form.mini.phtml') {
                        $reqsec = Mage::app()->getFrontController()->getRequest()->isSecure() ? 1 : 0;
                        $key.=self::SEP . self::BOF . self::REQ_SECURE . $reqsec . self::EOF . self::REQ_SECURE; //request secure
                    }
                    break;
                case 'Mage_GoogleAnalytics_Block_Ga':
                case 'Mage_Page_Block_Html_Notices':
                    $uask = Mage::app()->getRequest()->getCookie('user_allowed_save_cookie', false);
                    $key.=self::SEP . self::BOF . self::ALLOW_SAVE_COOKIE . $uask . self::EOF . self::ALLOW_SAVE_COOKIE;
                    break;
                case 'Nexcessnet_Turpentine_Block_Notices':
                    $turpentine_notice = Mage::helper('turpentine/varnish')->shouldDisplayNotice() ? '1' : '0';
                    $key.=self::SEP . self::BOF . self::PREVENT_TURPENTINE . $turpentine_notice . self::EOF . self::PREVENT_TURPENTINE;
                    break;
                case 'Mage_Page_Block_Html_Head':
                    //il faut rajouter les informations spécifiques au layout : router, controllerName,action
                    $request = Mage::app()->getRequest();
                    $controller_module = $request->getControllerModule();
                    if (is_null($controller_module)) {
                        $controller_module = '';
                    }
                    $controller_name = $request->getControllerName();
                    if (is_null($controller_name)) {
                        $controller_name = "";
                    }
                    $controller_key = $request->getControllerKey();
                    if (is_null($controller_key)) {
                        $controller_key = "";
                    }
                    $action_key = $request->getActionKey();
                    if (is_null($action_key)) {
                        $action_key = "";
                    }
                    $action_name = $request->getActionName();
                    if (is_null($action_name)) {
                        $action_name = "";
                    }
                    $route = $request->getRouteName();
                    if (is_null($route)) {
                        $route = "";
                    }
                    $key.=self::SEP . self::BOF . self::ROUTENAME . $route . self::EOF . self::ROUTENAME;
                    $key.=self::SEP . self::BOF . self::CONTROLLER_MODULE . $controller_module . self::EOF . self::CONTROLLER_MODULE;
                    $key.=self::SEP . self::BOF . self::CONTROLLER_NAME . $controller_name . self::EOF . self::CONTROLLER_NAME;
                    $key.=self::SEP . self::BOF . self::CONTROLLER_KEY . $controller_key . self::EOF . self::CONTROLLER_KEY;
                    $key.=self::SEP . self::BOF . self::ACTION_KEY . $action_key . self::EOF . self::ACTION_KEY;
                    $key.=self::SEP . self::BOF . self::ACTION_NAME . $action_name . self::EOF . self::ACTION_NAME;

                    // For categories and products pages
                    $curcat=Mage::registry('current_category');
                    $curprod=Mage::registry('current_product');
                    $curcatid=!is_null($curcat)?$curcat->getId():"";
                    $curprodid=!is_null($curprod)?$curprod->getId():"";
                    $key.=self::SEP.self::BOF.self::CURRENT_CATEGORY.$curcatid.self::EOF.self::CURRENT_CATEGORY;
                    $key.=self::SEP.self::BOF.self::CURRENT_PRODUCT.$curprodid.self::EOF.self::CURRENT_PRODUCT;

                    break;
                default:
                    break;
            }
            $skey=sha1($key);//peut provoquer des collisions, mais nécessaire à cause des clefs
            $block->setData('cache_key', $skey);
        }


        $store = Mage::app()->getStore();


        $key = "";
    }
}