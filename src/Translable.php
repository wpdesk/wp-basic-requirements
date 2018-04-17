<?php

/**
 * Have info about textdomain
 * have to be compatible with PHP 5.2.x
 */
interface WPDesk_Translable  {
	/** @return string */
	public function get_text_domain();
}