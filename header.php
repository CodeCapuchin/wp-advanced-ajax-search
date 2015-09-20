<div id="search-row">
	<form id="main-search" method="post" action="/listings">

		<input type="hidden" name="archivePage" id="archivePageInput" value="<?php echo ( is_archive() || is_search() ) ? 'true' : 'false'; ?>">

		<div class="col-search-text col-sm-5">
			<input type="text" name="search_s" id="search_s" class="text" value="<?php echo isset( $_POST['search_s'] ) ? $_POST['search_s'] : ( isset( $_SESSION['search_s'] ) ? $_SESSION['search_s'] : '' ); ?>" placeholder="Search products, services, companies" form="main-search">
		</div>

		<div class="col-search-industry col-sm-4">
			<select name="search_ind" id="search_ind" form="main-search">
				<option value="">-- Industry --</option>
				<?php foreach( get_terms( 'industry', array( 'hide_empty' => 0 ) ) as $term ) {
					$wpvar = isset( $wp_query->query_vars['industry'] ) ? get_term_by( 'slug', $wp_query->query_vars['industry'], 'industry' )->term_id : null;
					echo '<option value="' . $term->term_id . '" ' . ( $wpvar == $term->term_id ? 'selected' : ( isset( $_POST['search_ind'] ) && $_POST['search_ind'] == $term->term_id ? 'selected' : ( isset( $_SESSION['search_ind'] ) && $_SESSION['search_ind'] == $term->term_id ? 'selected' : '' ) ) ) . '>' . $term->name . '</option>';
				} ?>
			</select>
		</div>

		<div class="col-sm-2 text-right">
			<button type="submit" id="search-btn" class="rounded-small" form="main-search">
				<?php _e( 'Search', APP_TD ); ?>
			</button>
		</div>

		<a id="adv-search-toggle" class="col-sm-1 text-center <?php echo !is_archive() ? 'collapsed' : ''; ?>" data-toggle="collapse" href="#adv-search" aria-expanded="<?php echo is_archive() ? 'true' : 'false'; ?>" aria-controls="adv-search">Advanced Search</a>

	</form>
</div>

<div id="adv-search" class="collapse row <?php echo is_archive() ? 'in' : ''; ?>">
	<div class="container">
		<div class="col-sm-1 text-right">
			<label for="search-category">Filter:</label>
		</div>
		<div class="col-search-category col-sm-10 col-md-4">
			<select name="search_cat" id="search_cat" form="main-search">
				<option value="">-- Category --</option>
				<?php foreach( get_terms( 'listing_category', array( 'hide_empty' => 0 ) ) as $term ) {
					$wpvar = isset( $wp_query->query_vars['listing_category'] ) ? get_term_by( 'slug', $wp_query->query_vars['listing_category'], 'listing_category' )->term_id : null;
					echo '<option value="' . $term->term_id . '" ' . ( $wpvar == $term->term_id ? 'selected' : ( isset( $_POST['search_cat'] ) && $_POST['search_cat'] == $term->term_id ? 'selected' : ( isset( $_SESSION['search_cat'] ) && $_SESSION['search_cat'] == $term->term_id ? 'selected' : '' ) ) ) . '>' . $term->name . '</option>';
				} ?>
			</select>
		</div>
		<div class="col-search-designation col-sm-7 col-md-4">
			<label>Select Designation(s): &nbsp; </label>
			<?php foreach( get_terms( 'designation', array( 'hide_empty' => 0 ) ) as $term ) {
				$inArray = ( isset( $_SESSION['search_des'] ) && in_array( $term->term_id, $_SESSION['search_des'] ) ) ? true : false;
				echo '<a href="/listings?desToggle=' . $term->term_id . '" class="desToggle ' . ( $inArray ? 'toggled' : '' ) . '" title="' . $term->name . '" data-search_des="' . $term->term_id . '">' . file_get_contents( str_replace( home_url(), '.', get_field( 'icon', $term ) ) ) . '</a>&nbsp;';
				echo ( !is_archive() && $inArray ) ? '<input type="hidden" name="search_des[]" value="' . $term->term_id . '" form="main-search">' : '';
			} ?>
		</div>
		<div class="col-search-ownership col-sm-5 col-md-3">
			<?php foreach( get_terms( 'ownership', array( 'hide_empty' => 0 ) ) as $term ) {
				$inArray = ( isset( $_SESSION['search_own'] ) && in_array( $term->term_id, $_SESSION['search_own'] ) ) ? true : false;
				echo '<a href="/listings?ownToggle=' . $term->term_id . '" class="ownToggle ' . ( $inArray ? 'toggled' : '' ) . '" data-search_own="' . $term->term_id . '">' . $term->name . '</a>';
				echo ( !is_archive() && $inArray ) ? '<input type="hidden" name="search_own[]" value="' . $term->term_id . '" form="main-search">' : '';
			}
			$showClear = ( $_SESSION['search_ind'] || $_SESSION['search_cat'] || !empty( $_SESSION['search_des'] ) || !empty( $_SESSION['search_own'] ) ) ? true : false; ?>
			<a href="<?php echo home_url(); ?>/listings/?clear=true" id="clear-filters" title="Clear filters" <?php echo !$showClear ? 'style="display:none;"' : ''; ?>><i class="fa fa-times-circle-o"></i></a>
		</div>
	</div>
</div>