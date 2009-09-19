<?php
/**
 * HTML-Klasse
 *
 * @package federleicht
 * @subpacke helper
 * @version 0.4
 */
class html {
	protected $data = array();
	protected $eol = PHP_EOL;
	protected $output_xhtml = true;
	protected $var_name = 'fl';

	/**
	 * Konstruktor
	 *
	 * @todo Information ueber Einrueckung oder ein Ausgabe-Objekt uebergeben?
	 * @todo Datenobjekt direkt statt der Referenz auf den View uebergeben.
	 * @param view $view
	 * @param boolean $eol
	 * @param boolean $output_xhtml
	 */
	public function __construct($view = array(), $eol=true, $output_xhtml=true) {
		$this->set_data( $view );

		$this->eol = ( $eol )? PHP_EOL: '';
		$this->output_xhtml = (boolean) $output_xhtml;
	}

	/**
	 * Datenquelle setzen
	 *
	 * @param mixed $data
	 */
	public function set_data($data) {
		if ( $data instanceof fl_view ) {
			$data = $data->get_data_object();
		} elseif ( $data instanceof fl_data_structures_activerecord ) {
			$data = $data->get_data();
		}

		$this->data = $data;
	}

	/**
	 * Variablennamen setzen
	 *
	 * @param string $var_name
	 */
	public function set_var_name($var_name = 'fl') {
		$this->var_name = (string) $var_name;
	}

	/**
	 * HTML ausgeben
	 *
	 * @param string $html
	 */
	protected function output($html) {
		if ( $this->output_xhtml === false ) {
			$html = str_replace(' />', '>', $html);
		}

		echo $html . $this->eol;
	}

	/**
	 * Formulartag erzeugen
	 *
	 * @param string $action
	 * @param string $id
	 * @param string $target
	 * @param string $method
	 * @param string $charset
	 */
	public function form_tag($action, $id='', $target='', $method='post', $charset='UTF-8') {
		$action = ( $action[0] == '/' )? $action: '/'.$action;
		$id = ( $id != '' )? ' id="'.$this->var_name.'_form_'.$id.'" name="'.$id.'" ': '';
		$target = ( $target != '' )? ' target="'.$target.'" ': '';

		$html = '<form action="'.$action.'" method="'.$method.'" '. $id . $target .'accept-charset="'.$charset.'">';
		$this->output($html);
	}
	public function upload_form($action, $id="", $charset='UTF-8') {
		$action = ( $action[0] == '/' )? $action: '/'.$action;
		$id = ( $id != '' )? ' id="'.$this->var_name.'_form_'.$id.'" name="'.$id.'" ': '';

		$html = '<form action="'.$action.'" method="post" '. $id .' enctype="multipart/form-data" accept-charset="'.$charset.'">';
		$this->output($html);
	}
	/**
	 * Formulartag schließen
	 */
	public function form_end() {
		$html = '</form>';
		$this->output($html);
	}

	/**
	 * Dropdown erzeugen
	 *
	 * Es wird ein HTML-Dropdown erzeugt. Die einzelnen Dropdownelemente werden
	 * als kommaseparierte Liste in folgendem Format übergeben:
	 *     1=eins,2=zwei,3=drei
	 *
	 * Dies führt zu einem Dropdown mit drei Elementen. Es wird der Text nach dem
	 * Gleichheitszeichen zur Darstellung verwendet, hier also 
	 *     'eins', 'zwei', 'drei'.
	 * Die übergebenen Werte sind dabei der Text vor dem Gleichheitszeichen:
	 *     1, 2, 3.
	 *
	 * Das Dropdown hat die ID, die im ersten Parameter übergeben wird und 
	 * verwendet diesen auch zur Datenübergabe als Array-Schlüssel.
	 *
	 * Wenn bereits Daten geladen wurden, wird versucht, den entsprechenden 
	 * Wert als vorausgewählt darzustellen.
	 *
	 * Das HTML wird ausgegeben.
	 *
	 * @param string $field  	Eindeutiger Bezeichner für das Datenfeld.
	 * @param mixed $data    Dropdownelemente im Format value=description, kommasepariert
	 * @param string $label   Beschriftung des Dropdowns
	 */
	public function get_dropdown($field, $data = null, $label='') {
		if ( $data === null ) {
			if ( ! $this->data instanceof data_wrapper ) {
				throw new LogicException('Es wurden keine Daten fuer das Formularfeld uebergeben');
			}

			$data = $this->data->get($field.'_data');
		}

		if ( is_string($data) ) {
			$data = $this->string_to_array($data);
		}

		$html = '';

		if ( $label != '') $html .= $this->get_label($this->var_name.'_select_'.$field, $label);

		$html .= '<select name="'.$this->var_name.'['.$field.']" id="'.$this->var_name.'_select_'.$field.'" size="1" class="dropdown">'."\n";

		foreach( $data as $entry ) {
			$id   = $this->get_value('id', $entry);
			$name = $this->get_value('name', $entry);

			$selected = ( $this->get_value($field) == $id )? ' selected="selected"': '';
			$html .= "\t<option value=\"".$id.'"'.$selected.'>'.$name."</option>\n";
		}

		$html .= '</select>';

		$this->output($html);
	}

	/**
	 * Dropdown-String zu Array machen
	 *
	 * @param string $string
	 * @return array
	 */
	protected function string_to_array($string) {
		return fl_converter::string_to_dropdown_array($string);
	}

	/**
	 * Dropdown-Array zu String machen
	 *
	 * @param array $data
	 * @return string
	 */
	protected function array_to_string($data) {
		return fl_converter::dropdown_array_to_string($data);
	}

	/**
	 * Standardwert um Dropdownwerte legen
	 *
	 * @param mixed $options
	 * @param string $default
	 */
	public function default_wrapper($options, $default) {
		$default_entry = array(
			'id'=>'', 'name'=>$default
		);

		if ( is_array($options) ) {
			array_unshift($options, $default_entry);
		}

		return $options;
	}

	/**
	 * Radiobuttons ausgeben
	 *
	 * Radiobuttons werden direkt ausgegeben.
	 * Wrapperfunktion für echo html::return_radios()
	 */
	public function get_radios($field, $options, $tag='p') {
		$html = $this->return_radios($field, $options, $tag);

		$this->output($html);
	}

	/**
	 * Radiobuttons erzeugen
	 *
	 * Es werden Radiobuttons für die in der Variable Options übergebenen 
	 * Möglichkeiten übergeben.
	 *
	 * @todo Funktion lesbarer umschreiben
	 * @param string $field
	 * @param string $options
	 * @param string $tag
	 * @return string
	 */
	public function return_radios($field, $options, $tag='p') {
		$options = explode(',', $options);
		$html = '';

		foreach( $options as $option ) {
			$option = explode('=', $option);
			$option[1] = ( isset($option[1]) )? $option[1]: $option[0];
			$value = $this->get_value('1', $option);

			$selected = ( $this->get_value($field, $this->data) == $option[0] )? ' checked="checked"': '';
			$html .= "\n\t\t\t".'<'.$tag.'><input name="'.$this->var_name.'['.$field.']" id="'.$this->var_name.'_radio_'.$field.'-'.$option[0].'" type="radio" class="radio" value="'.$option[0].'"'.$selected.' />' .$this->get_label($this->var_name.'_radio_'.$field.'-'.$option[0], $value) . '</'.$tag.'>';
		}

		return $html;
	}

	/**
	 * Inputfeld erzeugen
	 *
	 * Das erzeugte HTML wird direkt ausgegeben.
	 *
	 * @param string $field   ID des Formularfelds
	 * @param string $label   zu verwendendes Label
	 * @param array $options  Optionen als assoziatives Array:
	 *                        - type: von text abweichender Typ
	 *                        - size: Länge (inkl. Maximallänge) einstellen
	 */
	public function get_input($field, $label='', $options=array() ) {
		if ( !empty($options) ) {
			$type = ( isset($options['type']) AND !empty($options['type']) )? 
				'type="'.$options['type'].'"': 
				'type="text"';

			$size = ( isset($options['size']) AND !empty($options['size']) )? 
				' maxlength="'.$options['size'].'" size="'.$options['size'].'"':
				'';

			$title = ( isset($options['title']) AND !empty($options['title']) )? 
				' title="'.$options['title'].'"':
				'';

			$class = ( isset($options['class']) AND !emtpy($options['class']) )?
				' class="text '.$options['class'].'"':
				' class="text"';

			$options = $type . $size . $title . $class;
		} else {
			$options = 'type="text" title="'.ucwords($field).'" class="text"';
		}

		if ( strpos($options, 'type="file"') !== false ) {
			return $this->get_filefield($field, $label);
		}

		$html = '';
		if ( $label != '' AND !is_numeric($label) ) {
			$html .= $this->get_label($this->var_name.'_input_'.$field, $label);
		}

		$value = str_replace("'", '"', $this->get_value($field, $this->data));

		$html .= '<input name="'.$this->var_name.'['.$field.']" id="'.$this->var_name.'_input_'.$field.'" '.$options.' value="'.$value.'" />';

		$this->output($html);
	}

	/**
	 * Beliebiges Input-Tag erzeugen
	 *
	 * @param mixed $attr
	 * @return string
	 */
	public function get_input_tag($attr) {
		$attributes = $this->parse_attributes($attr);
		$input_tag = "<input {$attributes}/>";

		return $input_tag;
	}
	public function get_select_tag($attr, $data, $value=null) {
		$attributes = $this->parse_attributes($attr);

		if ( is_string($data) ) {
			$data = $this->string_to_array($data);
		}

		$html = '<select '.$attributes.'size="1" class="dropdown">';

		foreach( $data as $entry ) {
			$id   = $this->get_value('id', $entry);
			$name = $this->get_value('name', $entry);

			$selected = ( $value == $id )? ' selected="selected"': '';
			$html .= '<option value="'.$id.'"'.$selected.'>'.$name.'</option>';
		}

		$html .= '</select>';

		return $html;
	}
	protected function parse_attributes($attr) {
		$attributes = '';
		if ( is_string($attr) ) $attr = fl_converter::string_to_array($attr);

		foreach ( $attr as $key => $value ) {
			if ( $key == 'name' and strpos($value, $this->var_name.'[') === false ) {
				$pos = strpos($value, '[');
				$value = $this->var_name.'['.substr($value, 0, $pos).']'.substr($value, $pos);
			}
			$attributes .= $key.'="'.$value.'" ';
		}

		return $attributes;
	}

	/**
	 * Verstecktes Inputfeld erzeugen
	 *
	 * @param string $field 
	 * @param string $value
	 */
	public function get_hidden($field, $value='') {
		$html = '<input name="'.$this->var_name.'['.$field.']" id="'.$this->var_name.'_hidden_'.$field.'" type="hidden" value="'.$value.'" />';
		$this->output($html);
	}

	/**
	 * Button erzeugen
	 *
	 * @param string $name  ID und name des Buttons
	 * @param string $label optional: Beschriftung
	 * @param string $type  optional: von "submit" abweichender Typ
	 */
	public function get_button($name, $label='', $type='submit', $include_name = false) {
		$label = ( $label === '' )? $name: $label;
		$html_name = ( $include_name ) ? "name=\"fl[$name]\" " : '';

		$html = '<input id="'.$this->var_name.'_button_'.$name.'"'.$html_name.'class="'.$type.'" type="'.$type.'" value="'.$label.'" />';
		$this->output($html);
	}

	/**
	 * Checkbox erzeugen
	 *
	 * @param string $field   ID des Formularfelds
	 * @param string $label   zu verwendendes Label
	 * @return string
	 */
	public function get_checkbox($field, $label='') {
		$html = '';
		$data = $this->get_value($field, $this->data);

		$checked = ( $data == $field OR $data == 1 )?
			' checked="checked"': 
			'';

		$html .= '<input name="'.$this->var_name.'['.$field.']" id="'.$this->var_name.'_checkbox_'.$field.'" class="checkbox" type="checkbox" value="'.$field.'"'.$checked .' /> ';

		if ( $label != '' AND !is_numeric($label) ) {
			$html .= '<label for="'.$this->var_name.'_checkbox_'.$field.'" class="checkbox">'.$label.'</label>';
		}

		$this->output($html);
	}

	/**
	 * Textarea erzeugen
	 *
	 * Es wird ein mehrzeiliges Eingabefeld erzeugt.
	 *
	 * @param string $field
	 * @param string $label
	 * @param array  $options Hash mit Optionen:
	 * 	- rows: Anzahl der Zeilen
	 * 	- cols: Anzahl der Spalten
	 */
	public function get_textarea($field, $label='', $options = array() ) {
		$html = '';
		$rows = ( isset($options['rows']) AND !empty($options['rows']) )? $options['rows']: 5;	
		$cols = ( isset($options['cols']) AND !empty($options['cols']) )? $options['cols']: 30;	

		$value = $this->get_value($field, $this->data);

		if ( $label != '' ) $html .= $this->get_label($this->var_name.'_textarea_'.$field, $label);

		$html .= '<textarea name="'.$this->var_name.'['.$field.']" id="'.$this->var_name.'_textarea_'.$field.'" rows="'.$rows.'" cols="'.$cols.'">'.$value.'</textarea>';

		$this->output($html);
	}

	/**
	 * Ein Dateiuploadfeld erzeugen
	 *
	 * @param string $field
	 * @param string $label
	 */
	public function get_filefield($field, $label = '') {
		$html = '';

		if ( $label != '' ) $html .= $this->get_label($this->var_name.'_file_'.$field, $label);

		$html .= '<input type="file" class="file" id="'.$this->var_name.'_file_'.$field.'" title="'.ucwords($field).'" name="fl_filedata['.$field.']" />';

		$this->output($html);
	}

	/**
	 * Formularvorgabewert oder -beschriftung holen
	 *
	 * @param string $field
	 * @param mixed  $source
	 * @param string $default
	 * @return string
	 */
	public function get_value($field, $source = null, $default = '') {
		if ( $source === null ) {
			$source = $this->data;
		}

		if ( $source instanceof view_data ) {
			$source->set_raw_output(false);
			$source->set_default($default);
			$value = $source->get($field, 'string');
			$source->set_default('');
			return $value;
		} elseif ( $source instanceof data_wrapper ) {
			return $source->is_set($field) ? $source->get($field, $value = true): $default;
		}

		if ( !is_array($source) AND !($source instanceof ArrayAccess) ) {
			$source = (array) $source;
		}

		return isset($source[$field]) ? $source[$field]: $default;
	}

	/**
	 * Label erzeugen
	 *
	 * @param string $id
	 * @param string @value
	 * @return string
	 */
	public function get_label( $id, $value ) {
		$label = '<label for="'.$id.'">'.$value.'</label>';

		return $label;
	}

	public function __toString() {
		return "HTML-Helper (Daten: {$this->data})";
	}
}
