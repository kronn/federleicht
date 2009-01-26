<?php $exception = $this->get('exception'); ?>

		<h2><?php echo $exception->getMessage(); ?></h2>

<?php if ( $exception instanceof FederleichtException ) { ?>
		<h3>Details</h3>
		<pre>
<?php echo $exception->getDetails(); ?>

		</pre>

<?php } ?>

		<h3>Fehler in <?php echo substr($exception->getFile(), strlen(FL_ABSPATH)); ?>(<?php echo $exception->getLine(); ?>)</h3>
		<h2><?php echo get_class($exception); ?></h2>
		<pre>
<?php 
$width = ( ceil(count($exception->getTrace())/10) );
$arrays = array();
foreach ( $exception->getTrace() as $num => $trace ) {
	$file = substr($trace['file'], strlen(FL_ABSPATH));
	foreach( $trace['args'] as $key => $arg ) {
		if ( is_array($arg) ) {
			$array_export = var_export($arg, true);
			$array_index = $num . '-' . $key;
			$arrays[] = array( 'index' => $array_index, 'array' => $array_export );
			$trace['args'][$key] = '<span title="'.$array_export.'">Array #'.$array_index.'</span>';
		}
	}
	$args = ( !empty($trace['args']) )?  implode(', ', $trace['args']): '';
	$num = str_pad($num, $width, ' ', STR_PAD_LEFT);

	echo "#$num: <b>$file</b>({$trace['line']}) : {$trace['function']}($args)".PHP_EOL;
}
?>
		</pre>

<h3>Array-Parameter</h3>
<?php if ( count($arrays) > 0 ) { ?>
	<pre>
<?php foreach($arrays as $array) {
	echo $array['index'] . ' => ' . $array['array'] . PHP_EOL;
} ?>
	</pre>
<?php } ?>


