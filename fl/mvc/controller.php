<?php
/**
 * Controller
 *
 * Eine grundlegende Controllerklasse.
 *
 * @package federleicht
 * @subpackage base
 */
class fl_controller {
	/**
	 * Instanzvariablen
	 */
	protected $data = array();

	protected $layout = 'default';
	protected $view;

	protected $responder;

	/**
	 * Referenzen auf externe Objekte und Daten
	 */
	protected $datamodel;
	protected $model;
	protected $functions;
	protected $factory;
	protected $cap;
	protected $request;
	protected $modulepath;

	/**
	 * Konstruktor, speichert Ablaufvariable und Datenbankverbindung
	 *
	 * Wenn in keine Action übergeben wurde, wird die defaultAction
	 * ausgeführt. Diese wird von jedem Controller selbst festgelegt.
	 *
	 * @param data_source_access  $data_access
	 * @param fl_functions $functions
	 * @param fl_model     $model
	 */
	public function __construct(data_source_access $data_access, $functions, $model) {
		$this->datamodel = $data_access;
		$this->functions = $functions;
		$this->factory = $functions->factory;

		$this->model = $model;

		$registry = fl_registry::getInstance();
		$this->request = $registry->get('request');
		$this->cap = $this->request->get('request');
		$this->modulepath = $registry->get('path', 'module');

		$this->responder = $this->factory->create('responder', $this->factory);

		$this->view = $this->cap['action'];
	}

	/**
	 * Datenobjekt (bislang ein Array) holen
	 *
	 * @return fl_data_structures_response
	 */
	public function get_response() {
		$response = $this->factory->get_structure(
			'response',
			array(
				'http_header'=>array(),
				'data'=>$this->data,
				'layout'=>$this->layout,
				'subview'=>$this->view
			)
		);

		return $response;
	}

	/**
	 * Verwaltungsobjekt für Antwortobjekte holen
	 *
	 * @return fl_responder Iterator data_wrapper
	 */
	public function get_responses() {
		return $this->responder;
	}

	/**
	 * Gemeinsame vorangestellte Abläufe
	 *
	 * Falls ein Modul gemeinsame, bei jedem Seitenaufruf wiederkehrende
	 * Aufgaben hat, können diese in der Funktion common definiert werden.
	 *
	 * Diese Funktion kann und soll ggf. von den Modulen überschrieben werden.
	 *
	 * @return bool Erfolgreiche Abarbeitung
	 */
	public function common() {
		return TRUE;
	}

	/**
	 * Alternative Abläufe
	 */
	public function alternate($message = null) {
		if ( $message instanceof Exception ) {
			if ( error_reporting() == 0 ) {
				echo $message->getMessage();
			} else {
				echo '<h2>'.$message->getMessage().'</h2>';
				echo '<h3>Fehler in '.substr($message->getFile(), strlen(ABSPATH)).'('.$message->getLine().') </h3>';
				echo '<pre>';
				$width = ( ceil(count($message->getTrace())/10) );
				foreach ( $message->getTrace() as $num => $trace ) {
					$file = substr($trace['file'], strlen(ABSPATH));
					$args = ( !empty($trace['args']) )?  implode(', ', $trace['args']): '';
					$num = str_pad($num, $width, ' ', STR_PAD_LEFT);

					echo "#$num: <b>$file</b>({$trace['line']}) : {$trace['function']}($args)".PHP_EOL;
				}
				echo '</pre>';

				$has_db_query = file_exists(ABSPATH.'public/php/db_query.php');

				echo '<h3>Anfrage</h3><pre>';
				$resolver = 'public/php/resolver.php';
				$url = $this->request->get_current_url();
				echo (file_exists(ABSPATH.$resolver))?
					'URL: <a href="/'.$resolver.'?request='.$url.'">'.$url.'</a>':
					'URL: ' . $url;
				echo PHP_EOL;
				if ( $this->request->has_postdata() ) {
					var_dump($this->request->post);
				}
				echo '</pre>';

				echo '<h3>Datenbankabfragen</h3><pre>';
				var_dump($this->datamodel->export_query_log());
				echo '</pre>';
			}

			$this->functions->log('Exception: '. $message->getMessage() );
		}

		$this->functions->stop();
	}

	/**
	 * Weiterleitung zur DefaultAction
	 *
	 * Wenn in keine Action übergeben wurde, wird die defaultAction
	 * ausgeführt. Diese wird von jedem Controller selbst festgelegt.
	 *
	 * Außerdem wird der Subview automatisch auf die im Controller
	 * festgelegte Action gesetzt.
	 *
	 * @param string $param
	 */
	public function defaultAction($param) {
		$this->cap['action'] = $this->defaultAction;
		$action = $this->defaultAction;
		$this->view = $this->defaultAction;

		/**
		 * richtigen Wert in Registry speichern
		 */
		$reg = fl_registry::getInstance();
		$request = $reg->get('request');
		$request->request['action'] = $this->defaultAction;
		$reg->set('request', $request);

		$this->$action($param);
	}

	/**
	 * Speichert eine kurze Nachricht für die Darstellung.
	 *
	 * Es kann eine "Wichtigkeit" als $type übergeben werden, diese wird
	 * als CSS-Klasse eingefügt.
	 *
	 * @param string $text      Nachrichtentext
	 * @param string $type      Wichtigkeit, wird als CSS-Klasse eingefügt
	 * @param string $namespace Gültigkeitsbereich
	 */
	protected function flash($text, $type='', $namespace='') {
		$flash = $this->functions->flash;

		return $flash->add_message($text, $namespace, $type);
	}

	/**
	 * Ruft eine andere URL auf.
	 *
	 * @param string  $target
	 * @param boolean $external
	 * @todo externes Template fuer Weiterleitungsfehler verwenden, anstatt hier direkt HTML auszugeben
	 */
	protected function redirect($target='', $external = false) {
		$target = ltrim($target, '/');

		if ( defined('SUBDIR') ) {
			$target = SUBDIR.'/'.$target;
		}
		
		$zieladresse = ( $external )?
			$target:
			'http://'.$_SERVER['HTTP_HOST'].'/'.$target;
		$this->functions->flash->save_messages();

		#if ( headers_sent($file, $line) AND strlen(ob_get_contents()) > 0) {
		if ( headers_sent($file, $line) ) {
			if ( error_reporting() > 0 ) {
				$backtrace = debug_backtrace();
				$html = <<<HTML
<h2>HTTP-Header wurden bereits gesandt</h2>
<p>Die Ausgabe startete hier:</p>
<pre>
Datei: {$file}
Zeile: {$line}
</pre>
<p>Weitere Informationen</p>
<pre>
Anfrage: {$_SERVER['REQUEST_URI']}
Zieladresse: {$zieladresse}
Backtrace: 
{$backtrace}
</pre>
HTML;
				echo $html;
			}

			ob_flush();
			$this->functions->stop(
				'Redirect: <a href="'.$zieladresse.'">'.$zieladresse.'</a>'
			);
		} else {
			$this->functions->log('Redirect: '.$zieladresse, fl_logger::WITHOUT_TIME);
			header('Location: '.$zieladresse);
			ob_flush();
		}
	}
	protected function external_redirect($target=''){
		return $this->redirect($target, true);
	}

	/**
	 * POST-Daten holen
	 *
	 * Die POST-Daten werde geholt und zurückgegeben.
	 * Wenn keine Daten da sind, leite den Browser auf eine
	 * andere Adresse um.
	 *
	 * @param string $target Zieladresse, falls keine Daten vorliegen.
	 * @return array
	 */
	protected function get_postdata($target='') {
		if ( $this->request->has_postdata() ) {
			$postdata = $this->request->post;
		} else {
			$this->redirect($target);
		}

		return $postdata;
	}

	/**
	 * Parameter auswerten
	 *
	 * @param string $params
	 * @return array
	 */
	protected function parse_params($params) {
		if ( strpos($params, '/') ) {
			$params = explode('/', $params);
		} else {
			$params = array($params);
		}

		if ( func_num_args() > 1 ) {
			$args = func_get_args();
			unset($args[0]);

			foreach( $args as $nr => $type ) {
				$keynr = $nr - 1;
				if ( !isset($params[$keynr]) ) {
					$params[$keynr] = null;
				}
				settype($params[$keynr], $type);
			}
		}

		return $params;
	}
	protected function params_to_url($params) {
		$pieces = explode('/', $params);
		array_shift($pieces);
		return ( implode('/', $pieces) );
	}
}
?>
