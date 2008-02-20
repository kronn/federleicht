<?php
/**
 * Datenstruktur fuer Bilddateien
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.1
 * @package federleicht
 * @subpackage base
 */
class image_structure extends data_structure {
	/**
	 * Metadaten einer Bilddatei einlesen und speichern
	 *
	 * @param int $id
	 */
	protected function set_image_data($id) {
		$file = glob(ABSPATH . self::IMAGEPATH . $id . '*');
		if ( $file === false OR !isset($file[0]) ) {
			$file = self::IMAGEPATH . 'dummy.jpg';
		} else {
			$file = $file[0];
		}
		$extension = substr($file, strrpos($file, '.'));

		list($width, $height) = getimagesize($file);

		$this->set_data(array(
			'src'=>self::IMAGEPATH . $id . $extension,
			'height'=>$height,
			'width'=>$width,
		));
	}

	public function get_image_html() {
		$html = '<img src="/'.$this->get('src').'" width="'.$this->get('width').'" height="'.$this->get('height').'" alt="'.$this->get('alt').'" title="'.$this->get('title').'" />';
		return $html;
	}
}
