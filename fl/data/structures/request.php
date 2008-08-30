<?php
/**
 * Daten der Anfrage
 *
 * Ein Datenobjekt, dass allen Informationen zur Anfrage enthält
 *
 * @version 0.2
 * @author Matthias Viehweger <kronn@kronn.de>
 * @package federleicht
 * @subpackage base
 */
class fl_data_structures_request extends fl_data_structures_data {
	/**
	 * Konstruktor
	 *
	 * Es werden die Postdaten und die gewählte Route in das Objekt übernommen
	 *
	 * @param route $route
	 */
	public function __construct($route) {
		$data = array(
			'route'=>$route,
			'all_post'=>$_POST,
			'post'=>(isset($_POST['fl'])? $_POST['fl']: null)
		);

		parent::__construct($data);
	}

	/**
	 * prüfen, ob Postdaten des Frameworks vorliegen.
	 *
	 * @return boolean
	 */
	public function has_postdata() {
		return ( $this->get('post') !== null )? true: false;
	}

	public function get_modul() {
		return $this['route']['modul'];
	}
}
