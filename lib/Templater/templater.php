<?php

// Load our configuration stuff
require_once 'config.php';

/**
 * Template builder for Wordpress
 * @author Craig Childs
 */
class Templater {
	
	/** 
	 * Run the templater through the header
	 * @param Provide optional variables for customisation (Optional)
	 */
	public static function header($vars = array()) {
		$header = file_get_contents(THEME_PATH . 'header.php');
		
		// Here are some defualt header variables
		if(!isset($vars['name']))
			$vars['name'] = get_bloginfo('name');
		if(!isset($vars['title']))
			$vars['title'] = get_bloginfo('title');
		if(!isset($vars['classes']))
			$vars['classes'] = get_body_classes();
		if(!isset($vars['template_directory']))
			$vars['template_directory'] = get_bloginfo('template_directory');

		echo self::parse($header, $vars);
	}

	/** 
	 * Run the templater through the footer
	 * @param pass variable values (Optional)
	 */
	public static function footer($vars = array()){
		$footer = file_get_contents(THEME_PATH . 'footer.php');
		echo self::parse($footer, $vars);
	}

	/**
	 * Run the templater through a content page
	 * @param variables to be passed through the template
	 * @param an optional file to load
	 */
	public static function content($file, $path = false, $vars = array()) {
		if($file != '') {
			
			if(!$path) {
				$path  = INC;
			}

			$content = file_get_contents($path . $file . '.php');
			echo self::parse($content, $vars);
		}
	}

	/**
	 * Include all of the template structures
	 * @param all of the variables to pass to the parts
	 */
	public static function render($vars = array()) {
		if(isset($vars['globals'])) {
			$globals = $vars['globals'];
		} else {
			$globals = false;
		}

		if(isset($vars['header'])) {
			$header = $vars['header'];

			if($globals != false) {
				$header = array_merge($header, $globals);
			}

			Templater::header($header);
		}
		
		if(isset($vars['content'])) {
			$content = $vars['content'];
			$page = (isset($content['page']) ? $content['page'] : 'index');
			$path = (isset($content['path']) ? $content['path'] : INC);
			$v = (isset($content['vars']) ? $content['vars'] : array());

			if($globals != false) {
				$v = array_merge($v, $globals);
			}
			Templater::content($page, $path, $v);
		}

		if(isset($vars['footer'])) {
			$footer = $vars['footer'];

			if($globals != false) {
				$footer = array_merge($footer, $globals);
			}

			Templater::footer($footer);
		}
	}

	/**
	 * Parse a file with the {{ }} tag
	 * @param file to parse
	 * @param the variables
	 */
	private static function parse($file, $vars) {

		// Go through each variable and replace the values
		foreach($vars as $key => $value) {
			$pattern = '{{{' . $key . '}}}';
			$file = preg_replace($pattern, $value, $file);
		}

		// Let's search for partial inclusions
		// ~partial_name~
		$file = preg_replace_callback('/~([a-zA-Z0-9_]+)~/', function($matches) use($vars) {
			$file = preg_replace('/[^a-zA-Z0-9_]+/', '', $matches[0]) . '.php';
			$path = PARTIAL . $file;
			var_dump($path);
			return self::parse($path, $vars); // BUG HERE
		}, $file);

		if(!!$file) {
			return $file;
		} else {
			return 'failed';
		}
	}
}