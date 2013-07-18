<?php
/**
 * @package Dredd_Comparer
 * @author Rija
 * @email rija@madadev.fr
 */
class Dredd_Comparer_Adminhtml_ComparerController extends Mage_Adminhtml_Controller_Action
{
    protected $_rulePrices = array();
    var $totalTemps = 0;
    var $totalTemps2 = 0;
    var $duree = 0;
    var $nombres = 0;
    var $entityTypeId = 0;

    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Dredd_Comparer');
        $entityType = Mage::getModel('eav/config')->getEntityType('catalog_product');
        $entityTypeId = $entityType->getEntityTypeId();

        $this->entityTypeId = $entityTypeId;
    }

    protected function _initComparer($idFieldName = 'id')
    {
        $comparerId     = $this->getRequest()->getParam($idFieldName);
        $comparerModel  = Mage::getModel('comparer/comparer')->load($comparerId);

        if($comparerId <= 0 || !$comparerModel->getId()){
            $comparerModel->setStoreId($this->getRequest()->getParam('store'));
            $comparerModel->setComparerMappageId($this->getRequest()->getParam('type'));
            $comparerModel->setName($this->getRequest()->getParam('name'));
        }

        Mage::register('comparer_data', $comparerModel);
        Mage::register('comparer', $comparerModel);
        return $this;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('comparer/comparer');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Comparer Manager'), Mage::helper('comparer')->__('Comparer Manager'));

        $this->_addContent($this->getLayout()->createBlock('comparer/adminhtml_comparer_comparer'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('comparer/adminhtml_comparer_grid')->toHtml());
    }

    public function expressAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('comparer/comparer');
        $this->_addContent($this->getLayout()->createBlock('comparer/adminhtml_comparer_express'));
        $this->renderLayout();
    }

    public function editAction()
    {
        $comparerId     = $this->getRequest()->getParam('id');
        $comparerModel  = Mage::getModel('comparer/comparer')->load($comparerId);

        if ($comparerModel->getId() || $comparerId == 0) {

            if($comparerId <= 0){
                $comparerModel->setStoreId($this->getRequest()->getParam('store'));
                $comparerModel->setComparerMappageId($this->getRequest()->getParam('type'));
            }
            Mage::register('comparer_data', $comparerModel);
            Mage::register('comparer', $comparerModel);

            $this->loadLayout();
            $this->_setActiveMenu('comparer/comparer');
            $this->_addBreadcrumb(Mage::helper('comparer')->__('Comparer Manager'), Mage::helper('comparer')->__('Comparer Manager'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('comparer')->__('Edit Comparer'), Mage::helper('comparer')->__('Edit Comparer'));

            //$this->getLayout()->getBlock('root')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('comparer/adminhtml_comparer_edit'))
            ->_addLeft($this->getLayout()->createBlock('comparer/adminhtml_comparer_edit_tree'));

            $this->getLayout()->getBlock('content')
            ->append($this->getLayout()->createBlock('comparer/adminhtml_comparer_edit_pcontainer'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('comparer')->__('Comparer not exists'));
            $this->_redirect('*/*/');
        }
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('comparer/comparer');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('comparer')->__('Comparer was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('comparer')->__('Unable to find a comparer to delete'));
        $this->_redirect('*/*/');
    }

    public function saveAction()
    {
        Mage::getSingleton('adminhtml/session')->addSuccess('saveComparer Action');
        //	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('comparer')->__('Comparer was successfully saved'));
        Mage::getSingleton('adminhtml/session')->setComparerData(false);
        $this->_redirect('*/*/');
    }

    public function newAction()
    {
        $this->_initComparer();
        Mage::register('settings', 1);

        $this->loadLayout();
        $this->_setActiveMenu('comparer/comparer');

        $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('comparer/adminhtml_comparer_new'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->renderLayout();
    }

    public function loadAction()
    {
        $t_data = array();
        $id = $this->getRequest()->getParam('id');
        $comparer = Mage::getModel('comparer/comparer');
        if($id){
            $comparer->load($id);
            $nombre = 0;
            if($ids = $comparer->getData('product_ids')){
                $nombre = count( explode(',', $ids) );
            }
            	
            	
            $t_data = array(
				'comparer_id' => $comparer->getId(),
				'name' => $comparer->getData('name'),
				'store_id' => $comparer->getData('store_id'),
				'comparer_products' => $comparer->getData('product_ids'),
				'comparer_categories' => $comparer->getData('category_ids'),
				'comparer_mappage_id' => $comparer->getData('comparer_mappage_id'),
				'in_stock' => $comparer->getData('in_stock'),
				'param_stock' => $comparer->getData('param_stock'),
				'tracking' => $comparer->getData('tracking'),
				'nombre' => $nombre,
				'current' => 0,
				'request' => $this->getUrl('*/*/request', array('_current'=>true)),
            );
        }
        $this->getResponse()->setBody(Zend_Json::encode($t_data));
    }

    public function requestAction()
    {

        $start3 = (float) array_sum(explode(' ',microtime()));

        $store_id = $this->getRequest()->getParam('store_id');
        $current = $this->getRequest()->getParam('current');
        $name = $this->getRequest()->getParam('name');
        //$product_id = $this->getRequest()->getParam('product_id');
        $product_ids = $this->getRequest()->getParam('product_ids');
        $category_ids = $this->getRequest()->getParam('category_ids');
        $mappage_id = $this->getRequest()->getParam('mappage_id');
        $in_stock = $this->getRequest()->getParam('in_stock');
        $tracking = $this->getRequest()->getParam('tracking');
        $nombre = $this->getRequest()->getParam('nombre');
        //$stock_param = $this->getRequest()->getParam('stock_param');

        $id = $this->getRequest()->getParam('id');
        $t_mappage = array();

        $comparer = Mage::getModel('comparer/comparer');
        if($id){
            $comparer->load($id);
        }
        if($current == 0) {
            $comparer->setData('category_ids', $category_ids);
            $comparer->setData('product_ids', $product_ids);
            $comparer->setData('in_stock', $in_stock);
            //$comparer->setData('stock_param', $stock_param);
            $comparer->setData('store_id', $store_id);
            $comparer->setData('comparer_mappage_id', $mappage_id);
            $comparer->setData('tracking', $tracking);
            $comparer->setData('name', $name);
            $comparer->setUpdatedAt(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())));
            $comparer->save();
            $id = $comparer->getId();
            $comparer->load($id);
        }

        $page = $this->getRequest()->getParam('page');
        $t_page = explode('-', $page);
        $t_product_ids = explode(',', $product_ids);
        $nb_product_ids = count($t_product_ids);

        if($nb_product_ids<=100) {
            $t_page[0] = 0;
            $t_page[1] = $nb_product_ids-1;
        }
        $t_ids = array(0);

        if (isset($t_page[1]) && $t_page[1] > $nb_product_ids) {
            $t_page[1] = $nb_product_ids-1;
        }

        if (isset($t_page[0]) && isset($t_page[1])) {
            for($i=$t_page[0]; $i<=$t_page[1]; $i++){
                $t_ids[] = $t_product_ids[$i];
            }
        }


        $mappage = Mage::getModel('comparer/mappage')->load($comparer->getData('comparer_mappage_id'));
        $separator = $mappage->getComparerMappageSeparator();
        if($separator == '\t') $separator = '	';
        $lines = Mage::getModel('comparer/mappageline')->getCollection()
                    ->addMappageFilter($mappage->getId())
                    ->setOrder('sort_order', 'ASC');

        foreach($lines as $line){
            $attribute_code = $line->getAttributeCode();
            if($attribute_code != "none"){
                array_push($t_mappage, $attribute_code);
            }
        }

        $csv = '';

        $nb_ligne = count($t_ids);
        $csv = Mage::helper('comparer/collect')->generateLineCollect($comparer, $separator, $lines, $t_ids, $this->entityTypeId);
        ++$current;

        $filename = $comparer->getFilename();
        $file = BP . DS . 'comparateur' . DS . $filename;

        if($csv!="" && $csv!="\r\n"){
            $objFile = new SplFileObject($file, "a");
            $objFile->fwrite($csv);
        }

        global $nombres;
        $nombres = $nombres + $nb_ligne;

        $t_data = array(
			'c' => $current,
			'id' => $id,
			'redirect' => $this->getUrl('*/*/'),
        );
        $this->getResponse()->setBody(Zend_Json::encode($t_data));
    }

    /**
     * Exporter le fichier csv
     */
    public function csvAction()
    {
        if($fileName = $this->getRequest()->getParam('file')) {
            $fileName = base64_decode($fileName);
            $content = file_get_contents(BP . DS . 'comparateur'. DS .$fileName);
            $this->_sendUploadResponse(str_replace(' ', '-',$fileName), $content);
        }
    }

    public function productAction()
    {
        $this->_initComparer();
        $this->getResponse()->setBody(
        $this->getLayout()->createBlock('comparer/adminhtml_comparer_edit_product')->toHtml()
        );
    }

    public function massDeleteAction()
    {
        $comparersIds = $this->getRequest()->getParam('comparer');
        if(!is_array($comparersIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('comparer')->__('Please select comparer(s)'));
        } else {
            try {
                $counter = 0;
                foreach ($comparersIds as $comparerId) {
                    $comparer = Mage::getModel('comparer/comparer')->load($comparerId);
                    if($comparer->canDelete()) {
                        $comparer->delete();
                        ++$counter;
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('comparer')->__('Total of %d record(s) were successfully deleted', $counter));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massExportParameterAction()
    {
        $comparersIds = $this->getRequest()->getParam('comparer');
        $status = (int)$this->getRequest()->getParam('status');
        if(!is_array($comparersIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('comparer')->__('Please select comparer(s)'));
        } else {
            try {
                $counter = 0;
                foreach ($comparersIds as $comparerId) {
                    $comparer = Mage::getModel('comparer/comparer')->load($comparerId);
                    $comparer->setStockParam($status)
                    ->save();
                    ++$counter;
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('comparer')->__(
                        'Total of %d record(s) were successfully updated', $counter
                )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');

        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    public function categoriesJsonAction()
    {
        $comparer = $this->_initComparer();

        $this->getResponse()->setBody(
        $this->getLayout()->createBlock('comparer/adminhtml_comparer_edit_tree')
        ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    public function _getReadAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    protected function _isAllowed()
    {
        return true; //Mage::getSingleton('admin/session')->isAllowed('partenaire/partenaire');
    }
}