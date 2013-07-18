<?php
class Dredd_Comparer_Helper_Pagination extends Mage_Core_Helper_Abstract
{
	protected $total_records;
	protected $current_page ;
	protected $display_count;

	protected $nb_page;

	protected $current_row;
	protected $current_end_row;
	protected $current_nb_row;
	protected $liste_page;

	public function __construct($v_total_records=0, $v_start_page=0, $v_display_count = 20)
	{
		$this -> total_records = intval("0".$v_total_records , 10);
		$this -> current_page = intval("0".$v_start_page , 10);
		$this -> display_count = intval("0".$v_display_count , 10);
		
		$this -> nb_page = 0;
		$this -> current_row = 0;
		$this -> current_end_row = 0;
		$this -> current_nb_row = 0;
		$this -> liste_page = array();
		
		$this -> calculate();
	}

	public function calculate() 
	{
		$this -> display_count = ($this -> display_count > 0) ? $this -> display_count : 20 ;
		$this -> nb_page = ceil($this -> total_records / $this -> display_count);
		$this -> current_page = ( ($this -> current_page>0) &&  ($this -> current_page<=$this -> nb_page) ) ? $this -> current_page : 1 ;
		
		if($this -> total_records >0) {
			$this -> current_row = (($this -> current_page-1) * $this -> display_count) +1; ;
			$this -> current_end_row = $this -> current_row + $this -> display_count -1;
			$this -> current_end_row = ( $this -> current_end_row > $this -> total_records) ?  $this -> total_records : $this -> current_end_row;
			$this -> current_nb_row = $this -> current_end_row - $this -> current_row + 1;
		}
		
		$this -> liste_page = array();
		for($i=0; $i < $this -> nb_page; $i++) {
			$this -> liste_page[$i]["bf_start_page"] = $i+1;
			$this -> liste_page[$i]["bf_start_row"] = ($i * $this -> display_count) +1;
			$this -> liste_page[$i]["bf_end_row"] = $this -> liste_page[$i]["bf_start_row"] + $this -> display_count -1;
			if($this -> liste_page[$i]["bf_end_row"] > $this -> total_records ) {
				$this -> liste_page[$i]["bf_end_row"] = $this -> total_records;
			}
			$this -> liste_page[$i]["bf_selected"] = "";
		}
	}
	
	public function has_next() {
		return ($this -> current_page < $this -> nb_page);
	}
	public function has_previous() {
		return ($this -> current_page > 1);
	}
	public function get_loop() {
		return $this -> liste_page;
	}
	public function get_current_row() {
		return $this -> current_row;
	}
	public function get_current_end_row() {
		return $this -> current_end_row;
	}
	public function get_current_row_count() {
		return $this -> current_nb_row;
	}
	public function get_total_records () {
		return $this -> total_records;
	}
	public function is_multi_page () {
		return ($this -> nb_page > 1) ;
	}
}