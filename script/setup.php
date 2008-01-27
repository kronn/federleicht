<?php
/**
 * Skript, um eine Federleicht-getriebene Webseite 
 * aufzubauen. Es werden die notwendigen Verzeichnisse 
 * angelegt sowie Beispieldateien und Code aus dem 
 * Subversion-Archiv ausgecheckt.
 *
 * @version 0.1
 * @author Matthias Viehweger <kronn@kronn.de>
 * @package federleicht
 * @subpackage script
 */

# Argumente auswerten


# Setup-Objekt erstellen
require_once 'setup/setup.php';
$setup = new setup($appname, $target_path);

# Federleicht aufsetzen
$setup->create_dirs();

if ( $setup->svn_available ) {
	$setup->checkout_federleicht();
	$setup->checkout_config_samples();
} else {
	echo $setup->install_message;
}

echo $setup->welcome_message;
