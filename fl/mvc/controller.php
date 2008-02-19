<?php
/**
 * Controller
 *
 * Eine grundlegende Controllerklasse.
 *
 * @package federleicht
 * @subpackage base
 */
class controller {
	/**
	 * Instanzvariablen
	 */
	var $data = array();
	var $flash_text = '';

	var $layout = 'default';
	var $view;

	/**
	 * Referenzen auf externe Objekte und Daten
	 */
	var $datamodel;
	var $model;
	var $functions;
	var $factory;
	var $cap;
	var $request;
	var $modulepath;

	/**
	 * Konstruktor, speichert Ablaufvariable und Datenbankverbindung
	 *
	 * Wenn in keine Action übergeben wurde, wird die defaultAction
	 * ausgeführt. Diese wird von jedem Controller selbst festgelegt.
	 *
	 * @param data_access  $data_access
	 * @param functions    $functions
	 * @param model        $model
	 */
	function controller(&$data_access, &$functions, $model) {
		$this->datamodel = &$data_access;
		$this->functions = &$functions;
		$this->factory = &$functions->get_factory();

		$this->model = &$model;

		$registry =& registry::getInstance();
		$this->request = $registry->get('request');
		$this->cap = $this->request->route;
		$this->modulepath = $registry->get('path', 'module');

		$this->view = $this->cap['action'];
		$this->flash_text = ( isset($_SESSION['flash'] ) )? $_SESSION['flash']: '';
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
	function common() {
		return TRUE;
	}

	/**
	 * Alternative Abläufe
	 */
	function alternate() {
		echo 'controller->common() fehlgeschlagen';
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
	function defaultAction($param) {
		$this->cap['action'] = $this->defaultAction;
		$action = $this->defaultAction;
		$this->view = $this->defaultAction;

		/**
		 * richtigen Wert in Registry speichern
		 */
		$reg =& registry::getInstance();
		$request = $reg->get('request');
		$request->route['action'] = $this->defaultAction;
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
	function flash($text, $type='', $namespace='') {
		$this->functions->flash->add_message($text, $namespace, $type);
	}

	/**
	 * Ruft eine andere URL auf.
	 *
	 * @param string $target
	 * @todo externes Template fuer Weiterleitungsfehler verwenden
	 */
	function redirect($target='') {
		$target = ltrim($target, '/');

		if ( defined('SUBDIR') ) {
			$target = SUBDIR.'/'.$target;
		}
		
		$zieladresse = 'http://'.$_SERVER['HTTP_HOST'].'/'.$target;

		$this->functions->flash->_flash();

		if ( headers_sent($file, $line) ) {
			if ( error_reporting() > 0 ) {
				$html = <<<HTML
<h2>HTTP-Header wurden bereits gesandt</h2>
<p>Die Ausgabe startete hier:</p>
<pre>
Datei: {$file}
Zeile: {$line}
</pre>
<p>Weitere Informationen</p>
<pre>
Zieladresse: {$zieladresse}
Anfrage: 
HTML;
				echo $html;
				print_r($this->request);
				echo '</pre>';
			}

			$this->functions->stop(
				'<a href="'.$zieladresse.'">'.$zieladresse.'</a>'
			);
		} else {
			header('Location: '.$zieladresse);

		}
	}

	/**
	 * Wrapperfunktion fuer veralteten Funktionsaufruf
	 */
	function goToTarget($target='') {
		trigger_error(
			'Veraltete Methode! Neu: controller->redirect($target)',
			E_USER_NOTICE
		);
		return $this->redirect($target);
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
	function get_postdata($target='') {
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
	function parse_params($params) {
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

	/**
	 * Login überprüfen
	 *
	 * Der Loginstatus wird überprüft.
	 *
	 * Man kann man persönliche Seiten ermöglichen, indem man einen
	 * Benutzernamen übergibt. (Der dazu erforderliche Benutzername
	 * wird dabei üblicherweise im Controller anhand der URL und der
	 * angefragten Daten bestimmt.)
	 *
	 * Um eine Rechteverwaltung zu ermöglichen, kann der Benutzerlevel
	 * aus der Datenbank ausgelesen werden. Dies geschieht standardmäßig.
	 * Wenn der Benutzername übergeben wird und mit dem derzeit
	 * eingeloggten Nutzer übereinstimmt, wird grundsätzlich Erlaubnis
	 * erteilt. Andernfalls zählt der Level.
	 *
	 * Wenn der Benutzer noch nicht eingeloggt ist, wird er zur Loginseite
	 * verwiesen. Wenn der Nutzer nicht die erforderlichen Rechte hat,
	 * wird er auf eine entsprechende Hinweisseite weitergeleitet.
	 *
	 * @param string $from
	 * @param string $username
	 * @param bool   $get_level_from_db
	 * @todo hier entfernen und in Login-Klasse auslagern
	 */
	function check_login($from, $username = '', $get_level_from_db = 'auto') {
		/**
		 * Paranoide Ausgangswerte der Variablen
		 */
		$logged_in = FALSE; // nicht eingeloggt
		$allowed = FALSE; // nicht berechtigt

		$user_level = 255; // Benutzer ist nur Gast
		$need_level = 0; // Eigentumsrechte sind erforderlich


		if ( isset($_SESSION['username']) ) {
			$logged_in = TRUE;
		}

		if ( $username !== '' AND $logged_in AND $username == $_SESSION['username'] ) {
			if  ( $get_level_from_db === 'auto' ) $get_level_from_db = TRUE;
			$allowed = TRUE;
		} elseif ( $username == '' AND $logged_in ) {
			$username = $_SESSION['username'];
		}

		if  ( $get_level_from_db === 'auto' ) $get_level_from_db = TRUE;


		if ( $logged_in AND $get_level_from_db ) {
			$controller = $this->cap['controller'];
			$action = $this->cap['action'];

			$result = $this->datamodel->retrieve('user_access', 'level', 'controller = "'.$controller.'" AND action = "'.$action.'"', '', '1');
			if ( isset($result['level']) ) {
				$need_level = $result['level'];
			} elseif ( error_reporting() > 0 ) { 
				$this->functions->needs('var_dump');
				$err = new varDumper('Federleicht-Controller', 'check_login');
				$msg ='Die Datenbank enthält keine Berechtigungsdaten!'; 
				$err->say($msg);
				$err->sv($this->cap, 'Anfrage');
				$err->sql($this->datamodel->lastSQL, 'Datenbankabfrage');
				$err->sv($result, 'Ergebnis der Datenbankabfrage');

				$table_prefix = $this->datamodel->table_prefix;
				$sql = <<<SQL

INSERT INTO `{$table_prefix}user_access` 
(`id`, `controller`, `action`, `level`) 
VALUES (NULL, "{$controller}", "{$action}", "2");

SQL;
				$err->sv($sql, 'Vorschlag für Datenbankergänzung');

				$err->say('Ausführung wird angehalten');
				trigger_error($msg, E_USER_ERROR );
				$err->stop();
			}

			unset($result);

			$result = $this->datamodel->retrieve('user', 'level', 'name = "'.$username.'"');
			$user_level = $result['level'];
			unset($result);
		}

		if ( $user_level <= $need_level ) {
			$allowed = TRUE;
		}

		if ( $logged_in AND $allowed ) {
			return TRUE;
		} elseif ( $logged_in AND !$allowed ) {
			$this->redirect(ADMINMODULE.'/notallowed');
		} else {
			$this->redirect(ADMINMODULE.'/login/'.$from);
		}
	}
}
?>
