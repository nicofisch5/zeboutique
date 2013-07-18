<?php
class Dredd_Comparer_Adminhtml_CroningController extends Mage_Adminhtml_Controller_Action
{
    public function  indexAction()
    {
        //echo "croningController";
        $this->loadLayout();
        $this->_setActiveMenu('compare/croning');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('comparer Manager'), Mage::helper('comparer')->__('comparer Manager'));
        $this->_addContent($this->getLayout()->createBlock('comparer/adminhtml_croning_croning'));
        $this->renderLayout();
    }

    public function newAction()
    {
        echo "nouvelle tache planifiée";
        $croningId = $this->getRequest()->getParam('id');
        $croningModel = Mage::getModel('comparer/cron')->load($croningId);

        if($croningModel->getId() || $croningId == 0)
        {
            Mage::register('croning_data', $croningModel);
            Mage::register('croning',$croningModel);

            $this->loadLayout();
            $this->_setActiveMenu('comparer/croning');
            $this->_addBreadcrumb(Mage::helper('comparer')->__('comparer Manager'), Mage::helper('comparer')->__('Cron Manager'));
            $this->_addBreadcrumb(Mage::helper('comparer')->__('comparer Manager'), Mage::helper('comparer')->__('Edit Cron'));

            $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('comparer/adminhtml_croning_edit'));
        }
    }
}

?>
