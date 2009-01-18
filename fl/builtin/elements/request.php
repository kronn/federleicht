		<h3>Anfrage</h3>
		<pre>
<?php 
$resolver = 'public/php/resolver.php';
$url = $this->get('request')->get_current_url();

echo (file_exists(FL_ABSPATH.$resolver))?
	'URL: <a href="/'.$resolver.'?request='.$url.'">'.$url.'</a>':
	'URL: ' . $url;
?>

<?php
if ( $this->get('request')->has_postdata() ) {
	var_dump($this->get('request')->post);
}
?>
		</pre>

