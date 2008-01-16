<?php
/**
 * Absoluten Dateipfad setzen
 *
 * @name ABSPATH
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', realpath(dirname(__FILE__).'/../') . '/');
}
