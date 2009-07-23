<?php
/**
 * email
 *
 * A small class to handle mail-creation an -sending
 * It supports text and html-mails (and both) and 
 * can handle attachments.
 *
 * email::parse_template can process mail-templates and 
 * substitude templatetags with given data.
 *
 * @author Matthias Viehweger <matthias.viehweger@kronn.de>
 * @version 0.4
 * @license MIT-License
 */
class email {
	/**
	 * variables
	 */
	var $text = FALSE;
	var $html = FALSE;
	var $attachments = FALSE;
	var $default_charset = 'UTF-8';

	var $config;

	/**
	 * storage for error-messages
	 */
	var $error;

	/**
	 * data-parts of e-mail
	 */
	var $message_header;
	var $message_body;

	/**
	 * contructor
	 */
	function email() {
		ini_set('track_errors', '1');
	}

	/**
	 * set configuration
	 *
	 * @param string $to
	 * @param string $from
	 * @param string $topic
	 * @param string $replyto [optional]
	 */
	function set_config($to, $from, $topic, $replyto='') {
		$this->config = array(
			'to'=>$to,
			'from'=>$from,
			'topic'=>$topic
		);

		if ( $replyto != '' ) $this->config['replyto'] = $replyto;
	}

	/**
	 * get last error-message
	 */
	function get_error() {
		return array_pop($this->error);
	}

	/**
	 * check wether it has to be a mime-mail or not
	 *
	 * @return boolean
	 */
	function must_be_mime() {
		$must_be_mime = FALSE;

		if ( $this->html != FALSE 
			OR $this->attachments != FALSE) {
			$must_be_mime = TRUE;
		}

		return $must_be_mime;
	}

	/**
	 * set text-part
	 *
	 * @param string $txt
	 * @param string $charset
	 */
	function set_text($txt, $charset = null) {
		if ( $charset === null ) {
			$charset = $this->default_charset;
		}

		$this->text = (object) array(
			'content'=>(string) $txt,
			'charset'=>(string) $charset
		);
	}

	/**
	 * set html-part
	 *
	 * @param string $html
	 * @param string $charset
	 */
	function set_html($html, $charset = null) {
		if ( $charset === null ) {
			$charset = $this->default_charset;
		}

		$this->html = (object) array(
			'content'=>(string) $html,
			'charset'=>(string) $charset
		);
	}

	/**
	 * add an attachment
	 *
	 * @param string  $filename
	 * @param string  $mime     [optional]
	 * @return boolean
	 */
	function add_attachment($filename, $mime='') {
		if ( empty($filename) ) {
			$this->error[] = 'No Filename provided';
			return FALSE;
		}
		if ( !is_file($filename) ) {
			$this->error[] = $filename . ' not found.';
			return FALSE;
		}

		if ( $mime == '') {
			// trying to find the mime-type
			if ( function_exists('mime_content_type') ) {
				$mime = mime_content_type($filename);
			} else {
				$this->error[] = 'No mime-type provided and unable to guess it.';
				return FALSE;
			}
		}

		// saving the data
		$file = array(
			'name'=>$filename,
			'mime'=>$mime
		);

		if ( $this->attachments === FALSE ) {
			$this->attachments = array();
		}

		$this->attachments[] = $file;

		return TRUE;
	}

	/**
	 * unset vars
	 */
	function clean_vars() {
		$this->text = FALSE;
		$this->html = FALSE;
		$this->attachments = FALSE;
	}

	/**
	 * parse template
	 *
	 * The template is filled with data. For every key in the 
	 * data-array, a substitution is attempted.
	 *
	 * {NAME} will be replaced with $data['name']
	 *
	 * All remaining templatetags will be removed.
	 *
	 * The resulting string will be returned.
	 *
	 * @param string $template
	 * @param array $data
	 * @return string
	 * @todo put into template class
	 */
	function parse_template($template, $data) {
		$content = $template;

		// substitute templatetags
		foreach( $data as $key => $value) {
			$content = str_replace('{'.strtoupper($key).'}', $value, $content);
		}

		// discard unused templatetags
		$content = preg_replace('/{[-_a-z]*}/i', '', $content);

		return $content;
	}

	/**
	 * composition of mail-headers and body
	 *
	 * the e-mail-composition consists of the following steps:
	 *
	 * - Check wether (or not) a mime-mail is necessary, if yes:
	 *   - check if attachments have to be handled, if yes:
	 *     - set header to multipart/mixed
	 *     - generate and add outer boundary
	 *   - set header to multipart/alternative and generate boundary
	 *   - generate plaintext-part if none set.
	 *   - compose body
	 *   - mark end of body
	 *   - check if attachments have to be handled, if yes:
	 *     - add attachments and boundaries
	 *     - mark end of mail
	 *
	 * - always:
	 *   - save header in message_header
	 *   - save body in message_body
	 *   - clean temporary variables
	 */
	function compose_message() {
		$headers = array();
		$body = '';

		$headers[] = 'From: '.$this->config['from'];
		if ( isset($this->config['replyto']) ) $headers[] = 'Reply-To: '.$this->config['replyto'];

		if ( $this->must_be_mime() ) {
			$headers[] = 'MIME-Version: 1.0';
			$boundary = md5(uniqid(date('d.m.Y \u\m H:i')));

			if ( $this->attachments != FALSE ) {
				//tell e-mail client this e-mail contains more than one part
				$headers[] = 'Content-Type: multipart/mixed; boundary="main'.$boundary.'"';
				$headers[] = '--main'.$boundary;
			}
			//tell e-mail client this e-mail contains alternate versions
			$headers[] = 'Content-Type: multipart/alternative; boundary="sub'.$boundary.'"';

			//plain text part of message
			if ( empty($this->text) ) {
				$this->set_text( strip_tags($this->html->content), $this->html->charset);
			}
			$body = '--sub'.$boundary.PHP_EOL .
				"Content-Type: text/plain; charset=".$this->text->charset.PHP_EOL .
				"Content-Transfer-Encoding: base64".PHP_EOL.PHP_EOL;
			$body .= chunk_split(base64_encode($this->text->content));

			//html part of message
			$body .= PHP_EOL.PHP_EOL.'--sub'.$boundary.PHP_EOL .
				"Content-Type: text/html; charset=".$this->html->charset.PHP_EOL .
				"Content-Transfer-Encoding: base64".PHP_EOL.PHP_EOL;
			$body .= chunk_split(base64_encode($this->html->content));

			//end of message
			$body .= PHP_EOL.'--sub'.$boundary."--".PHP_EOL.PHP_EOL.'.';

			if ($this->attachments != FALSE ) {
				//attachments of message
				$files = $this->attachments;

				foreach ( $files as $file ) {
					$body .= "\r\n--main".$boundary.PHP_EOL.
						"Content-Type: " . $file['mime']. PHP_EOL .
						"Content-Transfer-Encoding: base64".PHP_EOL .
						"Content-Disposition:attachment".PHP_EOL.PHP_EOL;
					$body .= chunk_split(base64_encode( file_get_contents($file['name']) ));
				}
				$body .= '--main'.$boundary."--".PHP_EOL.PHP_EOL.'.';
			}

		} else {
			$body = $this->text->content;

		}

		$this->message_body = $body;
		$this->message_header = implode(PHP_EOL, $headers);

		#$this->clean_vars();
	}

	/**
	 * send mail
	 */
	function send_mail() {
		if ( empty($this->message_body) 
			OR empty($this->message_header) 
			OR empty($this->config) 
		) {
			$this->error[] = 'Incomplete Data.';
			return FALSE;
		}

		$mailed = @mail(
				$this->config['to'], 
				$this->config['topic'], 
				$this->message_body, 
				$this->message_header
			) OR $this->error[] = $php_errormsg;

		return $mailed;
	}

	/**
	 * compose and send an email
	 *
	 * this is a demo and a nice shortcut, too.
	 *
	 * @param array $data    Array with the following keys:
	 *                       - text   messagebody as text/plain
	 *                       - html   messagebody as text/html
	 *                       - css    CSS for the HTML-part
	 * @param array $config  Array with the following keys:
	 *                       - from
	 *                       - replyto
	 *                       - topic
	 *                       - to
	 */
	function compose_and_send($data, $config) {
		$this->set_config(
			$config['to'],
			$config['from'],
			$config['topic'],
			$config['replyto']
		);

		$html  = '<html><head><title>'.$config['topic'].'</title>'."\n".'<style>'."\n";
		$html .= $data['css'];
		$html .= '</style>'."\n".'</head><body><div id="mailwrapper">'."\n";
		$html .= $data['html'];
		$html .= "\n".'</div></body></html>';

		$this->set_text($data['text']);
		$this->set_html($html);

		$this->compose_mail();
		return $this->send_mail();
	}
}
