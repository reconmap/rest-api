<?php

class Controller {

	protected $templates;

	public function __construct() {
		$this->templates = $GLOBALS['templates'];
	}
}
