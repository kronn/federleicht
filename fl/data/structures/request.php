<?php
/**
 * Daten der Anfrage
 *
 * Ein Datenobjekt, dass allen Informationen zur Anfrage enthält
 *
 * @version 0.1
 * @author Matthias Viehweger <kronn@kronn.de>
 * @package federleicht
 * @subpackage base
 */
class fl_data_structures_request {
    /**
     * Referenz auf Routenobjekt
     */
    var $route;

    /**
     * Postdaten
     */
    var $all_post;
    var $post;

    /**
     * Konstruktor
     *
     * Es werden die Postdaten und die gewählte Route in das Objekt übernommen
     *
     * @param route $route
     */
    function __construct($route) {
        $this->route = $route;

        $this->all_post = $_POST;
        $this->post = isset($_POST['fl'])? $_POST['fl']: null;
    }

    /**
     * prüfen, ob Postdaten des Frameworks vorliegen.
     *
     * @return boolean
     */
    function has_postdata() {
        return ( $this->post !== null )? true: false;
    }

    /**
     * Getter-Methoden, die übergangsweise Zugriff auf die Werte des Routenobjekts geben
     */
    function get_modul() {
        return $this->route['modul'];
    }
}
