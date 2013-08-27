<?php namespace Elegant;

class Elegant {

	function __construct()
	{
		require_once 'helper.php';
		require_once 'row.php';
		require_once 'result.php';
		require_once 'querybuilder.php';
		require_once 'model.php';
		// require_once 'relationship.php';

		$mod_path = APPPATH . 'models/';
		if(file_exists($mod_path)) $this->_read_model_dir($mod_path);
	}

	// Open model directories recursively and load the models inside
	private function _read_model_dir($dirpath)
	{
		$ci =& get_instance();

		$handle = opendir($dirpath);
		if(!$handle) return;

		while (false !== ($filename = readdir($handle)))
		{
			if($filename == "." or $filename == "..") continue;

			$filepath = $dirpath.$filename;
			if(is_dir($filepath))
				$this->_read_model_dir($filepath);

			elseif(strpos(strtolower($filename), '.php') !== false)
			{
				$name = strtolower($filepath);
				require_once $name;
			}

			else continue;
		}

		closedir($handle);
	}

}