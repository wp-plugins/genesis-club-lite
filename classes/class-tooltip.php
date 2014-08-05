<?php
if (!class_exists('DIY_Tooltip')) {
  class DIY_Tooltip {
    const HELP = '<span class="dashicons dashicons-editor-help"></span>';      
	private $labels = array();
	private $tabindex;	
	function __construct($labels) {
		$this->labels = $labels;
		$this->tabindex = 100;
	}

	function heading($label) {
		return array_key_exists($label,$this->labels) ? (__($this->labels[$label]['heading']).self::HELP) : ''; 
	}

	function text($label) {
		return array_key_exists($label,$this->labels) ? __($this->labels[$label]['tip']) : ''; 
	}

	function label($label, $text_only=false) {
		return $text_only ? $this->heading($label) : $this->tip($label); 
	}

	function tip($label) {
		$heading = $this->heading($label); 
		return $heading ? sprintf('<a href="#" class="diy-tooltip" tabindex="%3$s">%1$s<span class="tip">%2$s</span></a>',
			$heading, $this->text($label), $this->tabindex++) : '';
	}
  }
}
