<?php
/**
 * @file PHPErrorException.php
 *
 * @author Rob van den Hout <vdhout@studyportals.com>
 * @version 1.0.0
 * @copyright © 2017 StudyPortals B.V., all rights reserved.
 */

namespace StudyPortals\Exception;

use ErrorException;

/**
 * PHPErrorException.
 *
 * @package StudyPortals.Framework
 */
class PHPErrorException extends ErrorException{

	/**
	 * Get a proper stack trace for the {@link ErrorException}.
	 *
	 * @return array
	 * @see BaseException::getStackTrace()
	 */

	public function getStackTrace(){

		$trace = $this->getTrace();

		// Reverse and remove the call to "error_handler" from the stack

		array_reverse($trace);
		array_shift($trace);

		// If the last call is to "trigger_error", remove it from the stack too

		$entry = reset($trace);

		if(isset($entry['function']) && $entry['function'] == 'trigger_error'){

			array_shift($trace);

			// Try to get rid off the ExceptionHandler::notice() call as well

			$entry = reset($trace);
			$handler = __NAMESPACE__ . '\ThrowableHandler';

			if(isset($entry['function']) && $entry['function'] == 'notice' &&
				isset($entry['class']) && $entry['class'] == $handler){

				array_shift($trace);

				// Update the Exception itself to point to the true origin

				if(isset($entry['file']) && isset($entry['line'])){

					$this->file = $entry['file'];
					$this->line = $entry['line'];
				}
			}
		}

		return $trace;
	}

	/**
	 * Generated a user-readable error page.
	 *
	 * <p>This method automatically selects the correct output type, based upon
	 * the current SAPI.</p>
	 *
	 * @return string
	 * @see BaseException::displayException()
	 */

	public function __toString(){

		// Dump all output buffers

		while(@ob_end_clean()){
			;
		}

		try{

			if(PHP_SAPI == 'cli'){

				return BaseException::displayConsoleException($this);
			}
			else{

				@header("HTTP/1.1 500 Internal Server Error");
				@header('Content-Type: text/html');

				return BaseException::displayException($this);
			}
		}

		catch(\Throwable $e){

			return $e->getMessage();
		}
	}
}