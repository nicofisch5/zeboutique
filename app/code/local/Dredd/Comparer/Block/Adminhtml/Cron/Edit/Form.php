<?php
class Dredd_Comparer_Block_Adminhtml_Cron_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                                        'id' => 'edit_form',
                                        'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                        'method' => 'get',
										'enctype'=> 'multipart/form-data',
                                     )
        );
       
		$fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('comparer')->__('Add cron')));
		
		$model  = $this->getModel();
		 if ($model->getId()) {
			$fieldset->addField('id', 'hidden', array(
	            'name'      => 'id',
	            'label'     => Mage::helper('comparer')->__('ID'),
	            'title'     => Mage::helper('comparer')->__('ID'),
				'value'		=> $model->getId(),
	        ));
		}
		$fieldset->addField('comparer_id', 'select', array(
            'name'      => 'comparer_id',
            'label'     => Mage::helper('comparer')->__('Select export'),
            'title'     => Mage::helper('comparer')->__('Select export'),
            'required'  => true,
			'values'	=> Mage::getModel('comparer/comparer')->getCollection()->setOrder('name', 'ASC')->toOptionHash(),
			'value'		=> $model->getComparerId(),
        ));
		$fieldset->addField('timing', 'time', array(
            'name'      => 'timing',
            'label'     => Mage::helper('comparer')->__('Start Time'),
            'title'     => Mage::helper('comparer')->__('Start Time'),
            'required'  => true,
			'value'	=> str_replace(':',',',$model->getTiming()),
        ));
		$fieldset->addField('frequency', 'select', array(
            'name'      => 'frequency',
            'label'     => Mage::helper('comparer')->__('Frequency'),
            'title'     => Mage::helper('comparer')->__('Frequency'),
            'required'  => true,
			'values'	=> Mage::helper('comparer/cron')->toOptionHashFrequency(),
			'value'	=> $model->getFrequency(),
        ));
		
		$fieldset->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => Mage::helper('comparer')->__('E-mail on error'),
            'title'     => Mage::helper('comparer')->__('E-mail on error'),
			'class'     => 'validate-email',
            'required'  => true,
			'value'		=>$model->getEmail(),	
        ));
		$fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('comparer')->__('Status'),
            'title'     => Mage::helper('comparer')->__('Status'),
            'name'      => 'status',
            'required' => true,
			'value'	=> $model->getStatus(),
            'options'    => array(
                '1' => Mage::helper('comparer')->__('Active'),
                '0' => Mage::helper('comparer')->__('Inactive'),
			
            ),
        ));

		$form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
	
	/**
     * Retrieve template object
     *
     * @return Mage_Newsletter_Model_Template
     */
    public function getModel()
    {
        return Mage::registry('cron_data');
    }

}