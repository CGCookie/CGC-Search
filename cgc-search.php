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
		add_filter( 'the_search_query', array( $this, 'tweak_search' ) );

		// Actions
		add_action( 'template_redirect', array( $this, 'search_template' ) );

	}


	// Override the default get_search_form() call
	public function searchform( $form ) {
		$s_type = isset( $_GET['s_type'] ) ? urldecode( $_GET['s_type'] ) : 'tutorials';
		ob_start(); ?>
		<form role="search" method="get" id="searchform" action="<?php echo home_url(); ?>">
			<label class="screen-reader-text" for="searchinput"></label>
			<input type="text" name="s" id="searchinput" placeholder="Search" value="<?php echo get_search_query(); ?>"/>
			<div class="search-buttons">
				<select name="s_type" id="cgc-search-s_type">
					<option value="tutorials">Tutorials</option>
					<option value="members"<?php selected( 'members', $s_type ); ?>>Members</option>
				</select>
				<button type="submit" id="searchsubmit"><i class="icon-search"></i></button>
			</div>
		</form>
		<?php
		return ob_get_clean();
	}


	// Set categories, tags, post s_types, etc
	public function tweak_search( $query ) {

		return $query;
	}


	// Redirect to custom template for user search
	public function search_template() {

		if( empty( $_GET['s'] ) )
			return;

		if( empty( $_GET['s_type'] ) )
			return;

		if( 'members' != $_GET['s_type'] )
			return;


		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'search-members.php' ) ) {
			$located = trailingslashit( get_stylesheet_directory() )  . 'search-members.php';

		// Check parent theme next
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . 'search-members.php' ) ) {
			$located = trailingslashit( get_template_directory() ) . 'search-members.php';
		}
		//echo $located;
		if( ! empty( $located ) ) {
			$templates = array( 'search-members.php' );
			locate_template( $templates, true, true );
			exit;
		}

	}
}

// Instantiate the class
$cgc_search = new CGC_Search_Form;
