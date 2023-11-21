<?php

namespace MedusaWP;

use Illuminate\Validation\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation;

class ValidatorFactory {
	private $factory;

	public function __construct() {
		$this->factory = new Factory(
			$this->load_translator()
		);
	}

	/**
	 * Load translator
	 *
	 * @return Translation\Translator
	 */
	private function load_translator() {
		$filesystem = new Filesystem();
		$loader     = new Translation\FileLoader( $filesystem, MEDUSAWP_PLUGIN_DIR . '/lang' );
		$loader->addNamespace( 'lang', MEDUSAWP_PLUGIN_DIR . '/lang' );
		$loader->load( 'en', 'validation', 'lang' );

		return new Translation\Translator( $loader, 'en' );
	}

	/**
	 * @param  mixed $data
	 * @param  mixed $rules
	 * @param  mixed $messages
	 * @param  mixed $custom_attributes
	 * @return Validator
	 */
	public function make( $data, $rules, $messages = array(), $custom_attributes = array() ) {
		return $this->factory->make( $data, $rules, $messages, $custom_attributes );
	}
}
