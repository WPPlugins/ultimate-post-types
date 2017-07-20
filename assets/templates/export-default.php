<div class="wrap">
	<h2><?php _e( 'Export', 'upt' ) ?></h2>
	<table class="form-table">
		<tr>
			<th>Exporting as PHP code</th>
			<td>
				<p><?php _e( 'You can export the PHP code for a post type or taxonomy. Please go to the appropriate listing and click the <b>Export to PHP</b> link', 'upt' ) ?></p>
			</td>
		</tr>
		<tr>
			<th>Exporting as an XML file</th>
			<td>
				<p><?php printf( __( 'You can export post types and taxonomies by using the WordPress <a href="%s">Export</a> tool. The generated XML file can be imported on another site through the Import tool, just like a standard posts XML file.', 'upt' ), admin_url( 'export.php' ) ) ?></p>
				<p><?php printf( __( 'To see how this works, you can read the <a href="%s">Tools Export Screen</a> article at WordPress.org', 'upt' ), 'http://codex.wordpress.org/Tools_Export_Screen' ) ?></p>
			</td>
		</tr>
	</table>
</div>