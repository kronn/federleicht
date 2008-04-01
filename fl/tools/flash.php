<?php
/**
 * Flash-Messages
 *
 * Flash-Messages provide a way to preserve messages across different 
 * HTTP-Requests. This object manages those messages.
 *
 * Typical messages are error and status notifications which can flash
 * (hence the name) upon the next request and inform the user.
 *
 * @package federleicht
 * @subpackage base
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 * @license MIT-Style
 */
class fl_flash {
	/**
	 * message storage
	 *
	 * messages are stored in an array of the following form:
	 * [namespace][][msg]
	 *              [type]
	 *
	 * the array itself is an associative array of namespaces.
	 * [namespace] = array();
	 * 
	 * Each namespaces has an array which contain the actual 
	 * message.
	 * 
	 * Every message is itself an array with the following keys
	 * [msg]        message-text
	 * [type]       message-type
	 *
	 * The type could be used as css-class (at least I do).
	 *
	 * types and namespaces are saved lowercase only 
	 */
	private $messages;

	/**
	 * default message type
	 */
	private $default_type;

	/**
	 * default namespace
	 */
	private $default_namespace;

	/**
	 * constructor
	 */
	public function __construct($type='', $namespace='') {
		$this->set_default_type($type);
		$this->set_default_namespace($namespace);

		$this->messages = $this->load_all_messages();
	}

	/**
	 * destructor
	 */
	public function __destruct() {
		return $this->save_all_messages();
	}

	/**
	 * set default message type
	 *
	 * @param string $type
	 * @return bool
	 */
	public function set_default_type($type='') {
		$set = FALSE;
		$type = ( empty($type) )? 
			'notice':
			$type;

		$this->default_type = strtolower($type);
		$set = TRUE;

		return $set;
	}

	/**
	 * Set default namespace
	 *
	 * @param string $namespace
	 * @return bool
	 */
	public function set_default_namespace($namespace='') {
		$set = FALSE;
		$namespace = ( empty($namespace) )? 
			'global':
			$namespace;

		$this->default_namespace = strtolower($namespace);
		$set = TRUE;

		return $set;
	}

	/**
	 * add a message
	 *
	 * @param string $message
	 * @param string $namespace
	 * @param string $type
	 * @return bool
	 */
	public function add_message($message='', $namespace='', $type='') {
		$added = FALSE;

		if ($message == '') return $added;
		if ($namespace == '') $namespace = $this->default_namespace;
		if ($type == '') $type = $this->default_type;

		if (!isset($this->messages[$namespace])) {
			$this->messages[$namespace] = array();
		}

		$msg = array(
			'msg'=>$message,
			'type'=>$type
		);

		$this->messages[$namespace][] = $msg;
		$added = TRUE;
	
		return $added;
	}

	/**
	 * return messages of a certain namespace
	 *
	 * @param string $namespace
	 * @return array
	 */
	public function get_messages($namespace='') {
		$messages = array();

		if ($namespace == '') $namespace = $this->default_namespace;

		if ( isset($this->messages[$namespace]) ) {
			$messages = $this->messages[$namespace];
		}

		return $messages;
	}

	/**
	 * clear messages of a certain namespace
	 *
	 * @param string $namespace
	 * @return bool
	 */
	public function clear_messages($namespace='') {
		$cleared = FALSE;

		if ($namespace == '') $namespace = $this->default_namespace;

		$this->messages[$namespace] = array();
		$cleared = TRUE;

		return $cleared;
	}

	/* == INTERNALLY USED == */

	/**
	 * save serialized message-array to SESSION
	 *
	 * @todo check save success reliable
	 * @return bool
	 */
	private function save_all_messages() {
		$saved = FALSE;

		$msg = serialize($this->messages);

		$_SESSION['flash_messages'] = $msg;
		$saved = TRUE;

		return $saved;
	}

	/**
	 * load and unserialize messages from SESSION
	 *
	 * @return array
	 */
	private function load_all_messages() {
		$msg = array();

		if ( isset($_SESSION['flash_messages'] )) {
			$msg = unserialize( $_SESSION['flash_messages'] );
		}

		return $msg;
	}
}
?>
