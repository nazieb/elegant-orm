<?php

// In CI 3 the EXT constant doesn't exist!
if(!defined('EXT'))
{
	define('EXT', '.php');
}


class Elegant {

	function __construct()
	{
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

spl_autoload_register(function($class){
	if(strpos($class, "Elegant\\") === 0)
	{
		$classname = str_replace("Elegant\\", "", $class);

		$path = 'src/' . strtolower( str_replace("\\", "/", $classname) ) . EXT;
		require_once $path;
	}

});
