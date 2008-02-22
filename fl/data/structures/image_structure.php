<?php
/**
 * Datenstruktur fuer Bilddateien
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 * @package federleicht
 * @subpackage base
 */
class image_structure extends data_structure {
	/**
	 * Metadaten einer Bilddatei einlesen und speichern
	 *
	 * @param int $id
	 * @return void
	 */
	protected function set_image_data($id) {
		$path = $this->_imagepath;

		$files = glob(ABSPATH . $path . $id . '*');
		if ( $files === false OR !isset($files[0]) ) {
			$file = $path . 'dummy.jpg';
			$id = 'dummy';
		} else {
			$file = $files[0];
		}
		$extension = substr($file, strrpos($file, '.'));

		list($width, $height) = getimagesize($file);

		$this->set_data(array(
			'src'=>$path . $id . $extension,
			'height'=>$height,
			'width'=>$width,
		));
	}

	/**
	 * HTML zu einem Bild ausgeben
	 *
	 * @return string
	 */
	public function get_image_html() {
		$html = '<img src="/'.$this->get('src').'" width="'.$this->get('width').'" height="'.$this->get('height').'" alt="'.$this->get('alt').'" title="'.$this->get('title').'" />';
		return $html;
	}
}
