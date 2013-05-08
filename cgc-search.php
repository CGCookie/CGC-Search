<?php
/*
 * Plugin Name: CGC Search
 * Description: Adds advanced search features to CGC, including users, difficulty, etc
 * Author: Pippin Williamson
 */


class CGC_Search_Form {

	public function __construct() {

		// Filters
		add_filter( 'get_search_form', array( $this, 'searchform' ) );

		// Actions


	}


	// Override the default get_search_form() call
	public function searchform( $form ) {
		$form = '<form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
		<div><label class="screen-reader-text" for="s">' . __('Search for:') . '</label>
		<input type="text" value="' . get_search_query() . '" name="s" id="s" />
		<input type="submit" id="searchsubmit" value="'. esc_attr__('Search') .'" />
		</div>
		</form>';

		return $form;
	}

}

// Instantiate the class
$cgc_search = new CGC_Search_Form;