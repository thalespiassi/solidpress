<?php

namespace SolidPress\Core;

use Error;
use SolidPress\Hooks;

class Theme {
	/**
	 * Template engine
	 *
	 * @param array $args [
	 * 	'template_engine' => @param TemplateEngine
	 * 	'namespace' => @param string
	 * 	'base_folder' => @param string
	 * 	'registrable_namespaces' => @param string
	 * 	'theme_name' => @param string
	 * 	'js_dist_path' => @param string
	 * 	'css_dist_path' => @param string
	 * ]
	 */
	public $template_engine;
	public $namespace;
	public $base_folder;
	public $registrable_namespaces;
	public $theme_name;
	public $js_dist_path;
	public $css_dist_path;

	public function __construct(array $args) {
		if (
			!$args['template_engine'] ||
			!($args['template_engine'] instanceof TemplateEngine)
		) {
			throw new Error('Template engine not provided');
		}

		$this->template_engine = $args['template_engine'];
		$this->namespace = $args['namespace'];
		$this->base_folder = $args['base_folder'];
		$this->registrable_namespaces = $args['registrable_namespaces'];
		$this->theme_name = $args['theme_name'];
		$this->js_dist_path = $args['js_dist_path'];
		$this->css_dist_path = $args['css_dist_path'];

		$this->load_registrable_classes();

		new Hooks\TemplateEnqueues();
		new Hooks\ACF();
	}

	/**
	 * Search in folders for classes that implements Registrable interface,
	 * creates a new instance of those classes and calls the register method.
	 *
	 * @return void
	 */
	protected function load_registrable_classes(): void {
		foreach ($this->registrable_namespaces as $namespace) {
			$namespace_dir =
				get_template_directory() .
				"/{$this->base_folder}//" .
				$namespace;

			foreach (scandir($namespace_dir, 1) as $file) {
				if (strpos($file, '.php') === false) {
					continue;
				}

				$class_name_array = explode('.php', $file);
				$class_name = array_shift($class_name_array);
				$class_namespaced =
					$this->namespace . '\\' . $namespace . '\\' . $class_name;
				$class_instance = new $class_namespaced();
				$class_instance->register();
			}
		}
	}
}
