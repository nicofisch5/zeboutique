<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
*/
class Dredd_Comparer_Adminhtml_MappageController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('comparer/mappage');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Comparer Manager'), Mage::helper('comparer')->__('Comparer Manager'));

	$this->_addContent($this->getLayout()->createBlock('comparer/adminhtml_mappage_mappage'));
        $this->renderLayout();
    }

	public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('comparer/adminhtml_mappage_grid')->toHtml());
    }

    public function delChildAction()
	{
		$id = $this->getRequest()->getParam('id');
		$collection = Mage::getModel('comparer/comparer')->getCollection()->addMappageFilter($id);
		foreach($collection as $comparer){
			$comparer = Mage::getModel('comparer/comparer')->load($comparer->getId());
			$comparer->delete();
		}
		echo 0;
	}

	protected function _edition()
	{
            $mappageId     = $this->getRequest()->getParam('id');
            $mappageModel  = Mage::getModel('comparer/mappage')->load($mappageId);

		if ($mappageModel->getId() || $mappageId == 0) {
                    Mage::register('mappage_data', $mappageModel);
                    Mage::register('mappage', $mappageModel);

            $this->loadLayout();
            $this->_setActiveMenu('comparer/mappage');
            $this->_addBreadcrumb(Mage::helper('comparer')->__('Comparer Manager'), Mage::helper('comparer')->__('Format Manager'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('comparer')->__('Edit Mappage'), Mage::helper('comparer')->__('Edit Format'));

            $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('comparer/adminhtml_mappage_edit'))
                ->_addLeft($this->getLayout()->createBlock('comparer/adminhtml_mappage_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('comparer')->__('Format not exists'));
            $this->_redirect('*/*/');
        }
	}

	public function validateAction()
	{
        $response = new Varien_Object();
        $response->setError(false);

        if ( $data = $this->getRequest()->getPost() ) {
            try {
                $model = Mage::getModel('comparer/mappage');
                $model->setData($data);

		if( $this->getRequest()->getParam('id') > 0 ) {
                    $model->setId($this->getRequest()->getParam('id'));
                }

				$lignes = $this->getRequest()->getParam('ligne', array());
				if(!is_array($lignes) || sizeof($lignes) == 0 ) {
					Mage::throwException(Mage::helper('comparer')->__('Please, add a few lines to this mapping first'));
				}

				foreach($lignes as $key => $value){
					$line = Mage::getModel('comparer/mappageline');
					$line->setData($value);
					if ( intVal($key) > 0 ){
						$line->setId($key);
					}
					$model->addLine($line);
				}

				$model->save();

				$deleteLigne = $this->getRequest()->getParam('deleteLigne', false);
				if($deleteLigne){
					foreach($deleteLigne as $id){
						$mappageline = Mage::getModel('comparer/mappageline')
							->setId($id)
							->delete();
					}
				}

            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_initLayoutMessages('adminhtml/session');
                $response->setError(true);
                $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
            }
        }
        $this->getResponse()->setBody($response->toJson());
	}

    public function saveAction()
    {
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('comparer')->__('Format was successfully saved. <span style="color:#e26b1d;">Please regenerate exports depending on this format by editing and save them Or Click Regenerate Csv</span>'));
        Mage::getSingleton('adminhtml/session')->setMappageData(false);

        //Mage::helper('comparer/flat')->_addAttrMappageToFlat($storeId, $t_mappage, $t_attribFlat);

        $this->_redirect('*/*/');
    }

	public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('comparer/mappage');
                $model->setId($id);
				$bDelete = false;
				if($model->canDelete()) {
                	$model->delete();
					$bDelete = true;
				}
				if($bDelete){
                	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('comparer')->__('Format was successfully deleted'));
				}else{
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('comparer')->__('Format already referenced'));
				}
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('comparer')->__('Unable to find a Format to delete'));
        $this->_redirect('*/*/');
    }

    public function editAction()
    {
		$this->_edition();
    }

    public function newAction()
    {
	$this->getRequest()->setParam('id', 0);
        $this->_edition();
    }

    protected function _isAllowed()
    {
        return true; //Mage::getSingleton('admin/session')->isAllowed('partenaire/partenaire');
    }
}