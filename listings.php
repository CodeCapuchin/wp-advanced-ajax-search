<?php

// clean up & validate search vars
if( isset( $_POST ) ) {
	$_POST['search_s'] = isset( $_POST['search_s'] ) ? sanitize_text_field( $_POST['search_s'] ) : NULL;
	$_POST['search_ind'] = isset( $_POST['search_ind'] ) ? intval( $_POST['search_ind'] ) : NULL;
	$_POST['search_cat'] = isset( $_POST['search_cat'] ) ? intval( $_POST['search_cat'] ) : NULL;
}
if( isset( $_POST['search_des'] ) && is_array( $_POST['search_des'] ) ) {
	foreach( $_POST['search_des'] as $key => $value ) {
		$_POST['search_des'][$key] = intval( $value );
		if( !in_array( intval( $value ), $_SESSION['search_des'] ) ) $_SESSION['search_des'][] = intval( $value );
	}
}
if( isset( $_POST['search_own'] ) && is_array( $_POST['search_own'] ) ) {
	foreach( $_POST['search_own'] as $key => $value ) {
		$_POST['search_own'][$key] = intval( $value );
		if( !in_array( intval( $value ), $_SESSION['search_own'] ) ) $_SESSION['search_own'][] = intval( $value );
	}
}


// determine if this is a taxonomy page instead of "/listings":
$catPage = 'false';
if( isset( $wp_query->query_vars['industry'] ) ) {
	$catPage = 'true';
	$termID = get_term_by( 'slug', $wp_query->query_vars['industry'], 'industry' )->term_id; ?>
	<input type="hidden" name="search_ind" value="<?php echo $termID; ?>" form="main-search">
	<script>jQuery('#search_ind option[value="<?php echo $termID; ?>"]').attr('selected', 'selected');</script>
<?php }
if( isset( $wp_query->query_vars['listing_category'] ) ) {
	$catPage = 'true';
	$termID = get_term_by( 'slug', $wp_query->query_vars['listing_category'], 'listing_category' )->term_id; ?>
	<input type="hidden" name="search_cat" value="<?php echo $termID; ?>" form="main-search">
	<script>jQuery('#search_cat option[value="<?php echo $termID; ?>"]').attr('selected', 'selected');</script>
<?php } ?>


<form id="search-sorting" class="col-sm-9">
	<label for="sortBy" class="sr-only">Sort by </label>
	<select name="sortBy" id="sortBy" form="search-sorting">
		<option value="">Sort by</option>
		<option value="title" <?php if( $_SESSION['sortBy'] == 'title' || $_POST['sortBy'] == 'title' ) echo 'selected'; ?>>Alphabetical</option>
		<option value="date" <?php if( $_SESSION['sortBy'] == 'date' || $_POST['sortBy'] == 'date' ) echo 'selected'; ?>>Newest</option>
	</select>
</form>


<div id="search-results" class="main col-sm-9">
	<div id="searching"><i class="fa fa-refresh fa-spin"></i></div>
</div>


<div class="sidebar col-sm-3">
	<?php get_sidebar( app_template_base() ); ?>
</div>


<script>
	jQuery(document).ready(function($){

		var advanced_search = '<?php echo get_stylesheet_directory_uri(); ?>/advanced_search.php';

		// user submits search form on "/listings" page:
		$('#main-search').on("submit", function(event){

			// ga event to log search term:
			var search_term = $('#search_s').val();
			if ( search_term != '' ) ga( 'send', 'event', 'Search', search_term, null, null );

			// load results via ajax:
			var data = $(this).serialize();
			$.post( advanced_search, data ).success(function( result ){
				$("#search-results").html( result );
			}).error(function(){
				console.log( 'Error loading advanced search file' );
			});
			return false;

		});

		// user changes sorting option on "/listings" page:
		$('#sortBy').on("change", function(){
			var sortBy =  $(this).val();
			$('#search-results').html('<div id="searching"><i class="fa fa-refresh fa-spin"></i></div>');
			$('#main-search').append('<input type="hidden" name="sortBy" value="' + sortBy + '">').submit();
			setTimeout(function(){
				$('#searching').hide();
			}, 5000);
		});

		// user unfocuses on search field on "/listings" page:
		$('#search_s').on("blur", function(e){
			e.preventDefault();
			$('#search-results').html('<div id="searching"><i class="fa fa-refresh fa-spin"></i></div>');
			$('#main-search').submit();
			setTimeout(function(){
				$('#searching').hide();
			}, 5000);
		});

		// designation toggled from "/listings" page:
		$('.desToggle').click(function(e){
			e.preventDefault();
			var search_des = $(this).attr('data-search_des');
			$('#search-results').html('<div id="searching"><i class="fa fa-refresh fa-spin"></i></div>');
			$(this).toggleClass('toggled');
			$('#search-results').load( '<?php echo get_stylesheet_directory_uri(); ?>/advanced_search.php', { "desToggle" : search_des } );
			$('#clear-filters').show();
			setTimeout(function(){
				$('#searching').hide();
			}, 5000);
		});

		// ownership toggled from "/listings" page:
		$('.ownToggle').click(function(e){
			e.preventDefault();
			var search_own = $(this).attr('data-search_own');
			$('#search-results').html('<div id="searching"><i class="fa fa-refresh fa-spin"></i></div>');
			$(this).toggleClass('toggled');
			$('#search-results').load( '<?php echo get_stylesheet_directory_uri(); ?>/advanced_search.php', { "ownToggle" : search_own } );
			$('#clear-filters').show();
			setTimeout(function(){
				$('#searching').hide();
			}, 5000);
		});

	<?php // traffic to a taxonomy page rather than "/listings":
	if( $catPage === 'true' ) {

		mdc_reset_search_session(); ?>
		$('#main-search').append('<input type="hidden" name="catPage" value="true">').submit();

	<?php // form submitted from non-"/listings" page:
	} elseif( $_POST['archivePage'] === 'false' ) {

		$data = json_encode($_POST);

		if( is_array( $_POST['search_des'] ) ) {
			echo '$(\'#clear-filters\').show();';
			foreach( $_POST['search_des'] as $toggle ) { echo '$(\'.desToggle[data-search_des="' . $toggle . '"]\').addClass(\'toggled\');'; }
		}
		if( is_array( $_POST['search_own'] ) ) {
			echo '$(\'#clear-filters\').show();';
			foreach( $_POST['search_own'] as $toggle ) { echo '$(\'.ownToggle[data-search_own="' . $toggle . '"]\').addClass(\'toggled\');'; }
		} ?>

		// ga event to log search term:
		var search_term = $('#search_s').val();
		if ( search_term != '' ) ga( 'send', 'event', 'Search', search_term, null, null );

		$.post( advanced_search, <?php echo $data; ?> ).success(function( result ){
			$("#search-results").html( result );
		}).error(function(){
			console.log( 'Error loading advanced search file' );
		});

	<?php // direct traffic to "/listings":
	} else { ?>

		$('#search-results').load( advanced_search );

	<?php }

	if( $catPage === 'true' ) { ?>

		// industry or category selected on category page:
		$('#search_ind, #search_cat').on("change", function(e){
			e.preventDefault();
			var thisName = $(this).attr('name');
			var thisVal = $(this).val();
			$('#archivePageInput').val('false');
			window.location = "/listings/?c=1&cat=" + thisName + "&val=" + thisVal;
		});

	<?php } else { ?>

		// industry or category selected on "/listings" page:
		$('#search_ind, #search_cat').on("change", function(e){
			e.preventDefault();
			$('#search-results').html('<div id="searching"><i class="fa fa-refresh fa-spin"></i></div>');
			$('#main-search').submit();
			$('#clear-filters').show();
			setTimeout(function(){
				$('#searching').hide();
			}, 5000);
		});

	<?php } ?>

	});
</script>

<?php //reset vars
$_POST['archivePage'] = $catPage = NULL;


