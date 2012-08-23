/**
 * JavaScript code for the "List Tables" screen
 *
 * @package TablePress
 * @subpackage Views JavaScript
 * @author Tobias Bäthge
 * @since 1.0.0
 */

jQuery(document).ready( function($) {

	/**
	 * Show a popup box with the table's Shortcode
	 *
	 * @since 1.0.0
	 */
	$( '.tablepress-all-tables' ).on( 'click', '.shortcode a', function( /* event */ ) {
		prompt( tablepress_list.shortcode_popup, $(this).attr( 'title' ) );
		return false;
	} );

	/**
	 * Load a Thickbox with a table preview
	 *
	 * @since 1.0.0
	 */
	$( '.tablepress-all-tables' ).on( 'click', '.table-preview a', function( /* event */ ) {
		var width = $(window).width() - 120,
			height = $(window).height() - 120;
		if ( $( 'body.admin-bar' ).length )
			height -= 28;
		tb_show( $(this).text(), $(this).attr( 'href' ) + 'TB_iframe=true&height=' + height + '&width=' + width, false );
		return false;
	} );

	/**
	 * Submit Bulk Actions only if an action was selected an a table's checkbox was checked
	 *
	 * @since 1.0.0
	 */
	$( '#doaction, #doaction2' ).on( 'click', function() {
		var bulk_action,
			confirm_message,
			num_selected = $( '.tablepress-all-tables' ).find( 'tbody' ).find( 'input:checked' ).length;

		// determine location of clicked bulk action controls
		if ( 'doaction' == this.id )
			bulk_action = 'top';
		else
			bulk_action = 'bottom';

		// check whether an action was selected, and whether tables were selected
		if ( '-1' == $( '#bulk-action-' + bulk_action ).val() )
			return false;
		if ( 0 == num_selected )
			return false;

		// Show AYS prompt for deletion
		if ( 'delete' == $( '#bulk-action-' + bulk_action ).val() ) {
			if ( 1 == num_selected )
				confirm_message = tablepress_common.ays_delete_single_table;
			else
				confirm_message = tablepress_common.ays_delete_multiple_tables;

			if ( ! confirm( confirm_message ) )
				return false;
		}
	} );

} );