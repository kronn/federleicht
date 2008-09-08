<?php
/**
 * Datenstruktur fuer Bilddateien
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.3
 * @package federleicht
 * @subpackage base
 */
class fl_data_structures_image extends fl_data_structures_data {
	protected $src;
	protected $height;
	protected $width;

	public function __construct(array $data = array()) {
		parent::__construct($data);
		$this->set_image_data();
	}

	/**
	 * Dateipfad ausgeben
	 *
	 * @return string
	 */
	public function get_imagepath() {
		return (string) $this->_imagepath;
	}

	/**
	 * Metadaten einer Bilddatei einlesen und speichern
	 *
	 * @param int $id
	 * @return void
	 */
	protected function set_image_data() {
		$path = $this->_imagepath;
		$id = (int) $this->get('id'); 

		$files = glob(ABSPATH . $path . $id . '*');
		if ( $files === false OR !isset($files[0]) ) {
			$file = $path . 'dummy.jpg';
			$id = 'dummy';
		} else {
			$file = $files[0];
		}
		$extension = substr($file, strrpos($file, '.'));

		list($width, $height) = getimagesize($file);

		if ( !$this->is_set('height') ) {
			$this->set('height', $height);
		}

		if ( !$this->is_set('width') ) {
			$this->set('width', $width);
		}

		$this->set('src', $path . $id . $extension);
	}

	/**
	 * HTML zu einem Bild ausgeben
	 *
	 * @return string
	 */
	public function get_image_html() {
		foreach ( array('height', 'width') as $attr ) {
			$$attr = (($value = $this->get($attr)) > 0 )?
				$attr.'="'.$value.'" ': '';
		}

		$alt = 'alt="'.$this->get('alt').'" ';
		$title = 'title="'.$this->get('title').'" ';

		$attributes = $height.$width.$alt.$title;

		$html = '<img src="/'.$this->get('src').'" '.$attributes.'/>';
		return $html;
	}

	/**
	 * Object zu String umwandeln
	 */
	public function __toString() {
		return $this->get_image_html();
	}
}
