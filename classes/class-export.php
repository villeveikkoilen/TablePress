<?php
/**
 * TablePress Table Export Class
 *
 * @package TablePress
 * @subpackage Export/Import
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * TablePress Table Export Class
 * @package TablePress
 * @subpackage Export/Import
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class TablePress_Export {

	/**
	 * File/Data Formats that are available for the export
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $export_formats = array();

	/**
	 * Delimiters for the CSV export
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $csv_delimiters = array();

	/**
	 * Whether ZIP archive support is available in the PHP installation on the server
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $zip_support_available = false;

	/**
	 * Initialize the Export class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// initiate here, because function call not possible outside a class method
		$this->export_formats = array(
			'csv' => __( 'CSV - Character-Separated Values', 'tablepress' ),
			'html' => __( 'HTML - Hypertext Markup Language', 'tablepress' ),
			'xml' => __( 'XML - eXtended Markup Language', 'tablepress' ),
			'json' => __( 'JSON - JavaScript Object Notation', 'tablepress' )
		);
		$this->csv_delimiters = array(
			';' => __( '; (semicolon)', 'tablepress' ),
			',' => __( ', (comma)', 'tablepress' ),
			':' => __( ': (colon)', 'tablepress' ),
			'.' => __( '. (dot)', 'tablepress' ),
			'|' => __( '| (pipe)', 'tablepress' )
		);

		// filter from @see unzip_file() in WordPress
		if ( class_exists( 'ZipArchive' ) && apply_filters( 'unzip_file_use_ziparchive', true ) )
			$this->zip_support_available = true;
	}

	/**
	 * Export a table
	 *
	 * @since 1.0.0
	 *
	 * @param array $table Table to be exported
	 * @param string $export_format Format for the export ('csv', 'html', 'xml', 'json')
	 * @param string $csv_delimiter Delimiter for CSV export
	 * @return string Wrapped string for HTML export
	 */
	public function export_table( $table, $export_format, $csv_delimiter ) {
		switch( $export_format ) {
			case 'csv':
				$output = '';
				foreach ( $table['data'] as $row_idx => $row ) {
					$csv_row = array();
					foreach ( $row as $column_idx => $cell_content ) {
						$csv_row[] = $this->csv_wrap_and_escape( $cell_content, $csv_delimiter );
					}
					$output .= implode( $csv_delimiter, $csv_row );
					$output .= "\r\n";
				}
				break;
			case 'html':
				$output = "<table>\n";
				foreach ( $table['data'] as $row_idx => $row ) {
					$output .= "\t<tr>\n";
					$row = array_map( array( &$this, 'html_wrap_and_escape' ), $row );
					$output .= implode( '', $row );
					$output .= "\t</tr>\n";
				}
				$output .= '</table>';
				break;
			case 'xml':
				$output = '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . "\"?>\n";
				$output .= "<table>\n";
				foreach ( $table['data'] as $row_idx => $row ) {
					$output .= "\t<row>\n";
					$row = array_map( array( &$this, 'xml_wrap_and_escape' ), $row );
					$output .= implode( '', $row );
					$output .= "\t</row>\n";
				}
				$output .= '</table>';
				break;
			case 'json':
				$output = json_encode( $table['data'] );
				break;
			default:
				$output = '';
		}

		return $output;
	}

	/**
	 * Wrap and escape a cell for CSV export
	 *
	 * @since 1.0.0
	 *
	 * @param string $string Content of a cell
	 * @return string Wrapped string for CSV export
	 */
	protected function csv_wrap_and_escape( $string, $delimiter ) {
		$delimiter = preg_quote( $delimiter, '#' ); // escape delimiter for RegExp (e.g. '|')
		if ( preg_match( '#' . $delimiter . '|"|\n|\r#i', $string ) || ' ' == substr( $string, 0, 1 ) || ' ' == substr( $string, -1 ) ) {
			$string = str_replace( '"', '""', $string ); // escape single " as double ""
			$string = '"' . $string . '"'; // wrap string in ""
		}
		return $string;
	}

	/**
	 * Wrap and escape a cell for HTML export
	 *
	 * @since 1.0.0
	 *
	 * @param string $string Content of a cell
	 * @return string Wrapped string for HTML export
	 */
	protected function html_wrap_and_escape( $string ) {
		return "\t\t<td>{$string}</td>\n";
	}

	/**
	 * Wrap and escape a cell for XML export
	 *
	 * @since 1.0.0
	 *
	 * @param string $string Content of a cell
	 * @return string Wrapped string for XML export
	 */
	protected function xml_wrap_and_escape( $string ) {
		if ( $string != htmlspecialchars( $string ) )
			$string = "<![CDATA[{$string}]]>";
		return "\t\t<col>{$string}</col>\n";
	}

} // class TablePress_Export