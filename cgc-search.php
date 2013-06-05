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
		add_action( 'pre_get_posts', array( $this, 'tweak_search' ) );

		// Short Codes
		add_shortcode( 'advanced_search', array( $this, 'shortcode' ) );

	}


	// Override the default get_search_form() call
	public function searchform( $form ) {
		$s_type = isset( $_GET['s_type'] ) ? urldecode( $_GET['s_type'] ) : 'tutorials';
		ob_start(); ?>
		<form role="search" method="get" id="searchform" action="<?php echo home_url(); ?>">
			<label class="screen-reader-text" for="searchinput"></label>
			<input type="text" name="s" id="searchinput" placeholder="Search" value="<?php echo get_search_query(); ?>"/>
			<div class="search-buttons">
				<select name="s_type" id="cgc-search-type">
					<option value="tutorials">Tutorials</option>
					<option value="members"<?php selected( 'members', $s_type ); ?>>Members</option>
				</select>
				<button type="submit" id="searchsubmit"><i class="icon-search"></i></button>
			</div>
		</form>
		<?php
		return ob_get_clean();
	}


	/*--------------------------------------------*
	 * Short Code
	 *---------------------------------------------*/

	public function shortcode( $atts, $content = null) {

		extract( shortcode_atts( array(
				'categories'            => '1',
				'tags'                  => '1',
				'post_types'            => '1',
				'post_type'				=> NULL,
				'excluded_post_types'   => NULL,
				'taxonomies'            => NULL,
				'style'                 => 'radio',
				'search_text'           => 'Key Words',
				'placeholder'           => 'Enter search terms',
				'search_type_text'      => 'Search by Type',
				'search_cat_text'       => 'Search by Category',
				'search_tag_text'       => 'Search by Tag',
				'search_tax_text'       => 'Search by %s',
				'search_text'           => 'Search',
				'url'					=> home_url()
			), $atts )
		);

		if( $post_types && is_null( $post_type ) ) {
			$exluded_types = array();
			if( !is_null( $excluded_post_types ) ) {
				$exluded_types = explode( ',', $excluded_post_types );
			}
			$types = get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' );
		}

		ob_start(); ?>
			<form role="search" method="get" id="advsearchform" action="<?php echo $url; ?>">
				<?php
				if( $post_types && is_null( $post_type ) ) {
					echo '<div class="cgc-post-type">';
					if ( $search_type_text ) echo '<h3>' . $search_type_text . '</h3>';
					$value = isset( $_GET['post_type'] ) ? $_GET['post_type'] : 'any';
					$checked = ' ' . checked( '', $value, false );
					echo '<fieldset id="cgc-type-fields">';
						echo '<span class="cgc-type">';
							echo '<input type="checkbox" id="cgc-as-type-any" name="post_type[]" value="' . $value . '"' . $checked . '/>&nbsp;';
							echo '<label for="cgc-as-type-any" class="cgc-as-label">Any</label>';
						echo '</span>';

						foreach( $types as $type ) {
							$checked = ' ' . checked( $type->name, $value, false );
							if( !in_array( $type->name, $exluded_types ) || !is_array( $exluded_types ) ) {
								echo '<span class="cgc-type">';
									echo '<input type="checkbox" id="cgc-as-type-' . $type->name . '" name="post_type[]" value="' . $type->name . '"' . $checked . '/>&nbsp;';
									echo '<label for="cgc-as-type-' . $type->name . '" class="cgc-as-label">' . $type->labels->singular_name . '</label>';
								echo '</span>';
							}
						}
						echo '</fieldset>';
					echo '</div>';
				} else {
					echo '<input type="hidden" name="post_type" value="' . $post_type . '"/>';
				}

				if( $categories ) {
					$cats = get_categories();
					echo '<div class="cgc-category">';
						if ( $search_cat_text ) echo '<h3>' . $search_cat_text . '</h3>';
						$value   = isset( $_GET['category_name'] ) ? $_GET['category_name'] : '';
						$checked = ' ' . checked( '', $value, false );
						echo '<fieldset id="cgc-category-fields">';
							if( $style == 'radio' ) {
								echo '<span class="cgc-category">';
									echo '<input type="radio" id="cgc-as-cat-any" name="category_name" value=""' . $checked . '/>&nbsp;';
									echo '<label for="cgc-as-cat-any" class="cgc-as-label">Any</label>';
								echo '</span>';
								foreach( $cats as $cat ) {
									$checked = ' ' . checked( $cat->slug, $value, false );
									echo '<span class="cgc-category">';
										echo '<input type="radio" id="cgc-as-cat-' . $cat->slug . '" name="category_name" value="' . $cat->slug . '"' . $checked . '/>&nbsp;';
										echo '<label for="cgc-as-cat-' . $cat->slug . '" class="cgc-as-label">' . $cat->name . '</label>';
									echo '</span>';
								}
							} else {
								$selected = ' ' . selected( '', $value, false );
								echo '<select name="category_name" id="cgc-as-category-name">';
									echo '<option id="cgc-as-cat-any" value=""' . $selected . '>Any</option>';
									foreach( $cats as $cat ) {
										$selected = ' ' . selected( $cat->slug, $value, false );
										echo '<option id="cgc-as-cat-' . $cat->slug . '" value="' . $cat->slug . '"' . $selected . '>' . $cat->name . '</option>';
									}
								echo '</select>';
							}
						echo '</fieldset>';
					echo '</div>';
				}

				if( $tags ) {
					$tags = get_tags();
					echo '<div class="cgc-tag">';
						if ( $search_tag_text ) echo '<h3>' . $search_tag_text . '</h3>';
						$value   = isset( $_GET['post_tag'] ) ? $_GET['post_tag'] : '';
						$checked = ' ' . checked( '', $value, false );
						echo '<fieldset id="cgc-tag-fields">';
							if( $style == 'radio' ) {
								echo '<span class="cgc-tag">';
									echo '<input type="radio" id="cgc-as-tag-any" name="post_tag" value=""' . $checked . '/>&nbsp;';
									echo '<label for="cgc-as-tag-any" class="cgc-as-label">Any</label>';
								echo '</span>';
								foreach( $tags as $tag ) {
									$checked = ' ' . checked( $tag->slug, $value, false );
									echo '<span class="cgc-tag">';
										echo '<input type="radio" id="cgc-as-tag-' . $tag->slug . '" name="post_tag" value="' . $tag->slug . '"' . $checked . '/>&nbsp;';
										echo '<label for="cgc-as-tag-' . $tag->slug . '" class="cgc-as-label">' . $tag->name . '</label>';
									echo '</span>';
								}
							} else {
								$selected = ' ' . selected( '', $value, false );
								echo '<select name="post_tag" id="cgc-as-tag">';
									echo '<option id="cgc-as-tag-any" value=""' . $selected . '>Any</option>';
									foreach( $tags as $tag ) {
										$selected = ' ' . selected( $cat->slug, $value, false );
										echo '<option id="cgc-as-cat-' . $tag->slug . '" value="' . $tag->slug . '"' . $selected . '>' . $tag->name . '</option>';
									}
								echo '</select>';
							}
						echo '</fieldset>';
					echo '</div>';
				}

				if( ! is_null( $taxonomies ) ) {
					$taxonomies = explode( ',', $taxonomies );
					foreach( $taxonomies as $tax ) {
						$terms = get_terms( $tax );
						$taxonomy = get_taxonomy( $tax );
						echo '<div class="cgc-' . $taxonomy->query_var . '">';
							if ( $search_tax_text ) echo '<h3>' . sprintf( $search_tax_text, $taxonomy->labels->singular_name ) . '</h3>';
							$value   = isset( $_GET[$taxonomy->query_var] ) ? $_GET[$taxonomy->query_var] : '';
							$checked = ' ' . checked( '', $value, false );
							echo '<fieldset id="cgc-' . $taxonomy->query_var . '-fields">';
								if( $style == 'radio' ) {
									echo '<span class="cgc-category">';
										echo '<input type="radio" id="cgc-as-' . $taxonomy->query_var . '-any" name="' . $taxonomy->query_var . '" value=""' . $checked . '/>&nbsp;';
										echo '<label for="cgc-as-' . $taxonomy->query_var . '-any" class="cgc-as-label">Any</label>';
									echo '</span>';
									foreach( $terms as $term ) {
										$checked = ' ' . checked( $term->slug, $value, false );
										echo '<span class="cgc-' . $term->name . '">';
											echo '<input type="radio" id="cgc-as-' . $term->taxonomy . '-' . $term->term_id . '" name="' . $taxonomy->query_var . '" value="' . $term->slug . '"' . $checked . '/>&nbsp;';
											echo '<label for="cgc-as-' . $term->taxonomy . '-' . $term->term_id . '" class="cgc-as-label">' . $term->name . '</label>';
										echo '</span>';
									}
								} else {
									$selected = ' ' . selected( 0, $value, false );
									echo '<select name="' . $taxonomy->query_var . '" id="cgc-as-' . $taxonomy->query_var . '">';
										echo '<option id="cgc-as-' . $taxonomy->query_var . '-any" value="0"' . $selected . '>Any</option>';
										foreach( $terms as $term ) {
											$selected = ' ' . selected( $term->slug, $value, false );
											echo '<option id="cgc-as-' . $taxonomy->query_var . '-' . $term->term_id . '" value="' . $term->slug . '"' . $selected . '>' . $term->name . '</option>';
										}
									echo '</select>';
								}
							echo '</fieldset>';
						echo '</div>';
					}
				}

				?>
				<?php echo '<div class="cgc-search">';
				$placeholder = isset( $_GET['s'] ) ? $_GET['s'] : $placeholder;
				?>
					<fieldset id="cgc-search-terms">
						<?php if ( $search_text ) echo '<h3>' . $search_text . '</h3>'; ?>
						<input type="text" name="s" id="searchinput" placeholder="<?php echo $placeholder; ?>"/>
						<input type="hidden" name="cgc-search" value="1" />
						<input id="advsearchsubmit" type="submit" value="<?php echo $search_text; ?>"/>
					</fieldset>
				<?php echo '</div>'; ?>
			</form>
		<?php
		return ob_get_clean();
	}


	// Set categories, tags, post s_types, etc
	public function tweak_search( $query ) {

		if( $query->is_main_query() && $query->is_search() && isset( $_GET['cgc-search'] ) ) {

			$search_params = $_GET;
			foreach( $search_params as $key => $param ) {
				if( 'cgc-search' != $key ) {
					if( is_string( $param ) ) {
						$query->set( $key, rawurldecode( $param ) );
					} else {
						$query->set( $key, $param );
					}
				}
			}
			if( isset( $search_params['post_type'] ) && false !== array_search( 'any', $search_params['post_type'] ) ) {
				// override the other post types set
				$query->set( 'post_type', 'any' );
			}

			//echo '<pre>'; print_r( $query ); echo '</pre>'; exit;

		}

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
