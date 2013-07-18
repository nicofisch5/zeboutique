<?php
class Dredd_Comparer_Adminhtml_CronController extends Mage_Adminhtml_Controller_Action
{
    public function  indexAction()
    {
        //echo "cronController";
        $this->loadLayout();
        // $this->_setActiveMenu('compare/cron');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('comparer Manager'), Mage::helper('comparer')->__('comparer Manager'));
        $this->_addContent($this->getLayout()->createBlock('comparer/adminhtml_cron_cron'));
        $this->renderLayout();
    }

    public function newAction ()
    {
        $this->_forward('edit');
    }
   
    public function editAction()
    {
        $cronId = $this->getRequest()->getParam('id');
        $cronModel = Mage::getModel('comparer/cron')->load($cronId);
        if($cronModel->getId() || $cronId == 0)
        {
            Mage::register('cron_data', $cronModel);

            $this->loadLayout();
            $this->_setActiveMenu('comparer/cron');
            $this->_addBreadcrumb(Mage::helper('comparer')->__('comparer Manager'), Mage::helper('comparer')->__('Cron Manager'));
            $this->_addBreadcrumb(Mage::helper('comparer')->__('comparer Manager'), Mage::helper('comparer')->__('Edit Cron'));

            $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('comparer/adminhtml_cron_edit'));
        	$this->renderLayout();
        }
    }
	
	public function saveAction(){
		$request = $this->getRequest();
		$data = $request->getParams();
		$cron = Mage::getModel('comparer/cron');
		 if ($id = (int)$request->getParam('id')) {
            $cron->load($id);
        }
		try {
				$cron->addData($data);
				$cron->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('comparer')->__('Cron was successfully saved'));
                $this->_redirect('*/*/');
			}
		 catch (Mage_Core_Exception $e) {
		 	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('comparer')->__('Can\'t save cron'));
		 }
	}
	
	public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('comparer/cron');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('comparer')->__('Cron was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('comparer')->__('Unable to find a cron to delete'));
        $this->_redirect('*/*/');
    }
	
	public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('comparer/adminhtml_cron_grid')->toHtml());
    }

}

