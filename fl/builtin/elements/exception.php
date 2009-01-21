<?php $exception = $this->get('exception'); ?>

		<h2><?php echo $exception->getMessage(); ?></h2>

<?php if ( $exception instanceof FederleichtException ) { ?>
		<h3>Details</h3>
		<?php echo $exception->getDetails(); ?>

<?php } ?>

		<h3>Fehler in <?php echo substr($exception->getFile(), strlen(FL_ABSPATH)); ?>(<?php echo $exception->getLine(); ?>)</h3>
		<h2><?php echo get_class($exception); ?></h2>
		<pre>
<?php 
$width = ( ceil(count($exception->getTrace())/10) );
foreach ( $exception->getTrace() as $num => $trace ) {
	$file = substr($trace['file'], strlen(FL_ABSPATH));
	$args = ( !empty($trace['args']) )?  implode(', ', $trace['args']): '';
	$num = str_pad($num, $width, ' ', STR_PAD_LEFT);

	echo "#$num: <b>$file</b>({$trace['line']}) : {$trace['function']}($args)".PHP_EOL;
}
?>
		</pre>


