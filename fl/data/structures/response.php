<?php
/**
 * Datenstruktur für Serverantwort
 *
 * @version 0.1
 * @author Matthias Viehweger <kronn@kronn.de>
 * @package federleicht
 * @subpackage base
 */
class response {
    var $http_header;
    var $data;
    var $layout;
    var $subview;

    /**
     * Konstruktor
     */
    function response() {
    }
}
