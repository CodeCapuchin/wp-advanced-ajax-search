<?php session_start();


// hook into wordpress
require('../../../wp-blog-header.php');


// set up search vars - $search_s, $search_ind & $search_cat (and $indTerm & $catTerm)
$search_s = $_SESSION['search_s'] = isset( $_POST['search_s'] ) ? $_POST['search_s'] : ( isset( $_SESSION['search_s'] ) ? $_SESSION['search_s'] : '' );
$search_ind = $_SESSION['search_ind'] = isset( $_POST['search_ind'] ) ? $_POST['search_ind'] : ( isset( $_SESSION['search_ind'] ) ? $_SESSION['search_ind'] : '' );
	$indTerm = $search_ind ? get_term( $search_ind, 'industry' ) : '';
$search_cat = $_SESSION['search_cat'] = isset( $_POST['search_cat'] ) ? $_POST['search_cat'] : ( isset( $_SESSION['search_cat'] ) ? $_SESSION['search_cat'] : '' );
	$catTerm = $search_cat ? get_term( $search_cat, 'listing_category' ) : '';
$sortBy = $_SESSION['sortBy'] = isset( $_POST['sortBy'] ) ? $_POST['sortBy'] : ( isset( $_SESSION['sortBy'] ) ? $_SESSION['sortBy'] : '' );


// set up $search_des
$search_des = $_SESSION['search_des'] = isset( $_POST['search_des'] ) ? $_POST['search_des'] : ( isset( $_SESSION['search_des'] ) ? $_SESSION['search_des'] : array() );
if( isset( $_POST['desToggle'] ) && '' != $_POST['desToggle'] ) {
	if( in_array( $_POST['desToggle'], $search_des ) ) {
		$search_des = array_diff( $search_des, array( $_POST['desToggle'] ) );
	} else {
		$search_des[] = $_POST['desToggle'];
	}
	if( in_array( $_POST['desToggle'], $_SESSION['search_des'] ) ) {
		$_SESSION['search_des'] = array_diff( $_SESSION['search_des'], array( $_POST['desToggle'] ) );
	} else {
		$_SESSION['search_des'][] = $_POST['desToggle'];
	}
}
if( isset( $_POST['search_des'] ) && !empty( $_POST['search_des'] ) ) {
	$all_des = get_terms( 'designation', array( 'hide_empty' => 0 ) );
	foreach( $all_des as $des ) {
		if( in_array( $des->term_id, $_POST['search_des'] ) && !in_array( $des->term_id, $search_des ) ) {
			$search_des[] = $des->term_id;
		} elseif( !in_array( $des->term_id, $_POST['search_des'] ) ) {
			$search_des = array_diff( $search_des, array( $des->term_id ) );
		}
	}
}


// set up $search_own
$search_own = $_SESSION['search_own'] = ( isset( $_SESSION['search_own'] ) && !empty( $_SESSION['search_own'] ) ) ? $_SESSION['search_own'] : array();
if( isset( $_POST['ownToggle'] ) && '' != $_POST['ownToggle'] ) {
	if( in_array( $_POST['ownToggle'], $search_own ) ) {
		$search_own = array_diff( $search_own, array( $_POST['ownToggle'] ) );
	} else {
		$search_own[] = $_POST['ownToggle'];
	}
	if( in_array( $_POST['ownToggle'], $_SESSION['search_own'] ) ) {
		$_SESSION['search_own'] = array_diff( $_SESSION['search_own'], array( $_POST['ownToggle'] ) );
	} else {
		$_SESSION['search_own'][] = $_POST['ownToggle'];
	}
}
if( isset( $_POST['search_own'] ) && !empty( $_POST['search_own'] ) ) {
	$all_owns = get_terms( 'ownership', array( 'hide_empty' => 0 ) );
	foreach( $all_owns as $own ) {
		if( in_array( $own->term_id, $_POST['search_own'] ) && !in_array( $own->term_id, $search_own ) ) {
			$search_own[] = $own->term_id;
		} elseif( !in_array( $own->term_id, $_POST['search_own'] ) ) {
			$search_own = array_diff( $search_own, array( $own->term_id ) );
		}
	}
}


// $paramToggle - user removes individual search parameter
$_POST['paramToggle'] = isset( $_POST['paramToggle'] ) ? intval( $_POST['paramToggle'] ) : NULL;
if( $_POST['paramToggle'] != NULL ) {
	if( intval( $search_ind ) === $_POST['paramToggle'] ) {
		$search_ind = $_SESSION['search_ind'] = $indTerm = NULL;
		unset($_SESSION['search_ind']);
	} elseif( intval( $search_cat ) === $_POST['paramToggle'] ) {
		$search_cat = $_SESSION['search_cat'] = $catTerm = NULL;
		unset($_SESSION['search_cat']);
	} elseif( in_array( $_POST['paramToggle'], $search_des ) ) {
		$search_des = array_diff( $search_des, array( $_POST['paramToggle'] ) );
		$_SESSION['search_des'] = array_diff( $_SESSION['search_des'], array( $_POST['paramToggle'] ) );
	} elseif( in_array( $_POST['paramToggle'], $search_own ) ) {
		$search_own = array_diff( $search_own, array( $_POST['paramToggle'] ) );
		$_SESSION['search_own'] = array_diff( $_SESSION['search_own'], array( $_POST['paramToggle'] ) );
	}
}


// $desTerms - Designation terms
$desTerms = array();
foreach( $search_des as $designation ) {
	$term = get_term( $designation, 'designation' );
	$desTerms[] = '<li><a href="#" class="paramToggle" data-type="search_des" data-val="' . $term->term_id . '" title="remove">' . $term->name . '</a></li>';
}


// $ownTerms - Ownership terms
$ownTerms = array();
foreach( $search_own as $ownership ) {
	$term = get_term( $ownership, 'ownership' );
	$ownTerms[] = '<li><a href="#" class="paramToggle" data-type="search_own" data-val="' . $term->term_id . '" title="remove">' . $term->name . '</a></li>';
}


// $paged
$paged = ( get_query_var('paged') ? get_query_var('paged') : ( get_query_var('page') ? get_query_var('page') : 1 ) );


//clean up vars before feeding WP_Query
$search_s = sanitize_text_field( $search_s );
$search_ind = intval( $search_ind );
$search_cat = intval( $search_cat );
if( !is_string( $search_s ) ) exit('Invalid search term.');
if( !is_string( $sortBy ) ) exit('Invalid sorting option.');
if( !is_int( $paged ) ) exit('Invalid page selection.');
if( !is_array( $search_des ) ) exit('Invalid designation.');
if( !is_array( $search_own ) ) exit('Invalid ownership type.');


// set up base $args
$args = array(
	'post_type' => 'listing',
	'posts_per_page' => 10,
	'paged' => $paged,
);

// define sorting $args
switch ( $sortBy ) {
	case 'title':									//if "Alphabetical" is selected, sort by title:
		$args['meta_key'] = '';
		$args['orderby'] = 'title';
		$args['order'] = 'ASC';
		break;
	case 'comment_count':							//if "Most Rated" is selected, sort by comment_count:
		$args['meta_key'] = '';
		$args['orderby'] = 'comment_count date';
		$args['order'] = 'DESC';
		break;
	default:										//if "Newest" or no sorting is selected, sort by date:
		$args['meta_key'] = 'featured-home';
		$args['orderby'] = 'meta_value_num rand';
		$args['order'] = 'DESC';
		break;
}

// define search parameter $args
if( $search_s ) $args['s'] = $search_s;

// define tax_query $args
if( $search_ind || $search_cat || $search_des || $search_own ) {
	$taxes = array();
	if( !empty( $search_ind ) ) $taxes[] = 1;
	if( !empty( $search_cat ) ) $taxes[] = 1;
	if( !empty( $search_des ) ) $taxes[] = 1;
	if( !empty( $search_own ) ) $taxes[] = 1;
	$tax_count = count( $taxes );
	$args['tax_query'] = ( $tax_count > 1 ) ? array( 'relation' => 'AND' ) : array();
	if( $search_ind ) $args['tax_query'][] = array( 'taxonomy' => 'industry', 'field' => 'id', 'terms' => $search_ind );
	if( $search_cat ) $args['tax_query'][] = array( 'taxonomy' => 'listing_category', 'field' => 'id', 'terms' => $search_cat );
	if( $search_des ) {
		foreach( $search_des as $des ) {
			$args['tax_query'][] = array( 'taxonomy' => 'designation', 'field' => 'id', 'terms' => $des );
		}
	}
	if( $search_own ) {
		foreach( $search_own as $own ) {
			$args['tax_query'][] = array( 'taxonomy' => 'ownership', 'field' => 'id', 'terms' => $own );
		}
	}
}

// $the_query
$the_query = null;
$the_query = new WP_Query( $args );


//FOR DEV DEBUGGING:
	if( current_user_can( 'update_themes' ) ) {
		// echo '<pre><strong>GOTTEN FROM $_GET:</strong><br><br>'; print_r( $_GET ); echo '</pre>';
		// echo '<pre><strong>DELIVERED FROM $_POST:</strong><br><br>'; print_r( $_POST ); echo '</pre>';
		// echo '<pre><strong>STORED IN $_SESSION:</strong><br><br>search_s: '; print_r( $_SESSION['search_s'] );
			// echo '<br>sortBy: '; print_r( $_SESSION['sortBy'] );
			// echo '<br>search_ind: '; print_r( $_SESSION['search_ind'] );
			// echo '<br>search_cat: '; print_r( $_SESSION['search_cat'] );
			// echo '<br>search_des: '; print_r( $_SESSION['search_des'] );
			// echo '<br>search_own: '; print_r( $_SESSION['search_own'] );
		// echo '</pre>';
		echo '<pre><strong>$args</strong> = '; print_r( $args ); echo '</pre>';
	}


?><div class="section-head">
	<h1><?php _e( 'Listings', APP_TD ); ?></h1>
</div>

<h3 class="searchTitle">
	<?php // display search parameters
	if( $the_query->have_posts() ) $posts_count = $the_query->found_posts;
	echo ( $the_query->have_posts() ) ?
		( $search_s ? ( ( $indTerm || $catTerm || $desTerms || $ownTerms ) ? 'Listing' . ( $posts_count > 1 ? 's' : '' ) : $posts_count . ' listing' . ( $posts_count > 1 ? 's' : '' ) ) . ' found containing "' . $search_s . '"<br>' : '' )
		. ( ( $indTerm || $catTerm || !empty( $desTerms ) || !empty( $ownTerms ) ) ? '<small>Showing ' . $posts_count . ' listing' . ( $posts_count > 1 ? 's' : '' ) . ' for <ul class="showing">' : '' )
		. ( $indTerm ? '<li><a href="#" class="paramToggle" data-type="search_ind" data-val="' . $indTerm->term_id . '" title="remove">' . $indTerm->name . '</a></li>' : '' )
		. ( $catTerm ? '<li><a href="#" class="paramToggle" data-type="search_cat" data-val="' . $catTerm->term_id . '" title="remove">' . $catTerm->name . '</a></li>' : '' )
		. ( !empty( $desTerms ) ? implode( '', $desTerms ) : '' )
		. ( !empty( $ownTerms ) ? implode( '', $ownTerms ) : '' )
		. ( ( $indTerm || $catTerm || !empty( $desTerms ) || !empty( $ownTerms ) ) ? '</ul></small>' : '' )
		. ( ( !$search_s && !$indTerm && !$catTerm && empty( $desTerms ) && empty( $ownTerms ) ) ? '<small>Showing all ' . $posts_count . ' listings</small>' : '' )
		. ( ( $paged > 1 ) ? '<small class="paged">Page ' . $paged . '</small>' : '' )
	:
		'<small>No listings found' . ( $search_s ? ' containing "' . $search_s . '"' . ( ( $indTerm || $catTerm || $desTerms || $ownTerms ) ? ' in ' : '' ) : ' for ' ) . '<ul class="showing none-found">'
		. ( $indTerm ? '<li><a href="#" class="paramToggle" data-type="search_ind" data-val="' . $indTerm->term_id . '" title="remove">' . $indTerm->name . '</a></li>' : '' )
		. ( $catTerm ? '<li><a href="#" class="paramToggle" data-type="search_cat" data-val="' . $catTerm->term_id . '" title="remove">' . $catTerm->name . '</a></li>' : '' )
		. ( !empty( $desTerms ) ? implode( '', $desTerms ) : '' )
		. ( !empty( $ownTerms ) ? implode( '', $ownTerms ) : '' )
		. '</ul></small>'
	; ?>
</h3>

<?php if( $the_query->have_posts() ) {

	// if results, loop-de-loop:
	while( $the_query->have_posts() ) {
		$the_query->the_post();
		$featured = ( va_show_featured() && va_is_listing_featured( get_the_ID() ) ) ? 'featured' : '';  // featured listings  ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( $featured ); echo ' ' . va_post_coords_attr(); ?> itemscope itemtype="http://schema.org/Organization">
			<?php echo ( va_show_featured() && va_is_listing_featured( get_the_ID() ) ) ? '<div class="featured-head"><h3>Featured</h3></div>' : '';
			get_template_part( 'content-listing' ); ?>
		</article>
	<?php }

	// pagination ?>
	<nav class="pagination">
		<?php appthemes_pagenavi( $the_query, 'paged', $args ); ?>
	</nav>

	<?php wp_reset_postdata();

} else {

	//if no results ?>
	<h4>Click <a href="<?php echo home_url(); ?>/listings/?clear=true">here</a> to reset all search parameters.</h4>

<?php }

// enqueue jquery
wp_enqueue_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js', array(), '1.11.3', false ); ?>

<script>


	//designation icon tooltips
	jQuery(document).ready(function( $ ){
		$('.des-icon').tooltip();
	});


	// afterPaginate() function
	var afterPaginate = function(){
		jQuery('.page-numbers').click(function(e){
			e.preventDefault();
			var targ = jQuery(this).attr('href');
			var resultsPos = $('#search-results').offset();
			$('#search-results').load( targ, afterPaginate());
			$('body').stop().animate( { scrollTop: ( resultsPos.top - 30 ) }, '500' );
			return false;
		});
	};
	afterPaginate();


	// paramToggles() function
	var paramToggle = function(){
		jQuery('.paramToggle').tooltip({ 'trigger' : 'hover' });
		jQuery('.paramToggle').click(function(e){
			e.preventDefault();
			var thisVal = jQuery(this).attr('data-val');
			var thisType = jQuery(this).attr('data-type');
			switch( thisType ) {
				case 'search_ind':
					jQuery('#search_ind option[value=""]').attr('selected', 'selected');
					break;
				case 'search_cat':
					jQuery('#search_cat option[value=""]').attr('selected', 'selected');
					break;
				case 'search_des':
					jQuery('.desToggle[data-search_des="' + thisVal + '"]').removeClass('toggled');
					break;
				case 'search_own':
					jQuery('.ownToggle[data-search_own="' + thisVal + '"]').removeClass('toggled');
					break;
			}
			$('#search-results').html('<div id="searching"><i class="fa fa-refresh fa-spin"></i></div>');
			$('#search-results').load( '<?php echo get_stylesheet_directory_uri(); ?>/advanced_search.php', { 'paramToggle' : thisVal }, paramToggle());
			setTimeout(function(){
				$('#searching').hide();
			}, 5000);
		});
	};
	paramToggle();


	// hide #clear-filters if no search parameters set
	<?php echo ( !$indTerm && !$catTerm && empty( $desTerms ) && empty( $ownTerms ) ) ? 'document.getElementById("clear-filters").style.display = "none";' : ''; ?>


</script>



