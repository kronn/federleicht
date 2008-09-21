<?php
/**
 * Interface für LazyLoad-Klassen
 *
 * Da das Nachladen einer Klasse in der Praxis dem Command-Pattern 
 * ähnelt, heißt die ausführende Methode "execute".
 *
 * @author Matthias Viehweger
 * @version 0.1
 * @package federleicht
 * @subpackage data
 */
interface data_loader {
	public function execute();
}
