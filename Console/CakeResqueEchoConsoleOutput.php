<?php
/**
 * Replacement for ConsoleOutput('php://stdout') which may cause troubles with
 * more sophisticated process handling on unix.
 *
 * Simple uses `echo` to write the messages
 */
class CakeResqueEchoConsoleOutput extends ConsoleOutput {

	/**
	 * Override parent constructor as we don't deal with file handles directly
	 */
	public function __construct() {
		if (DS === '\\' && !(bool)env('ANSICON')) {
			$this->_outputAs = self::PLAIN;
		}
	}

	/**
	 * Override parent to not use the file handler (which we don't use)
	 * @param string $message
	 * @return bool
	 */
	protected function _write($message) {
		echo $message;
		return true;
	}
}
