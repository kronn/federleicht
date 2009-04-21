<?php if ( $this->get('db') instanceof fl_data_access_database ) { ?>

	<?php $has_db_query = file_exists(FL_ABSPATH.'public/php/db_query.php'); // @todo anclickbare SQL-Queries erzeugen ?>

		<h3>Datenbankabfragen</h3>
		<pre>
	<?php var_dump($this->get('db')->export_query_log()); ?>
		</pre>

<?php } ?>
