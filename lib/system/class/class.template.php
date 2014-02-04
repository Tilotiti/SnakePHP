<?php
class template {
	private
		$file   = false,
		$smarty = false;
		
	public function __construct($file) {
		$this->smarty = new smarty();
		$this->smarty->compile_dir  = CACHE;
		$this->file = $file;
	}
	
	public function assign($key, $value) {
		$this->smarty->assign($key, $value);
	}
	
	public function display($force = false) {
		if(file_exists($this->file)):
			$output = $this->smarty->fetch($this->file);

			if($force):
				echo $output;
				return true;
			else:
				return $output;
			endif;
		else:
			return false;
		endif;
	}
}