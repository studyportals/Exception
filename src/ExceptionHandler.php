<?php

/**
 * @file ExceptionHandler.php
 *
 * <p>Provides a BaseException which can be used as base class for all other
 * exceptions. BaseException's can be echo'd to produce a proper, human-
 * readable, error report and stack-trace.<br>
 * This script further implements a full suite of exception- and error-handling
 * methods which attempt to properly display all uncaught exceptions and errors
 * in a human-readable format.</p>
 *
 * <p>Converts all PHP errors into PHPErrorException's and, when assertions are
 * enabled, will throw a PHPAssertionFailed exception in case an assertion
 * fails.</p>
 *
 * <p>This script respects PHP's error-reporting setting. It also honours the
 * current "assert" settings:</p>
 * <ul>
 * 		<li>When assertions are active and "assert_bail" is enabled, a failed
 * 		assertion will lead to immediate termination of the script.</li>
 * 		<li>If assertions are active, but "assert_bail" is disabled,
 *		PHPAssertionFailed exception is thrown (and hence can be caught to
 *		"handle" the failure).</li>
 *		<li>If FireLogger is present and active it will take over the handling
 *		of assertions, ignoring the "assert_bail" setting.</li>
 *	</ul>
 *
 * <p><strong>Important</strong>: You need to call {@link ExceptionHandler::
 * enable()} to get the above up-and-running. Loading the file alone is not
 * enough.</p>
 *
 * <p>Released under a BSD-style license. For complete license text see
 * http://www.sgraastra.net/code/license/.</p>
 *
 * @author Thijs Putman <thijs@studyportals.com>
 * @author Danko Adamczyk <danko@studyportals.com>
 * @author Rob van den Hout <vdhout@studyportals.com>
 * @copyright © 2004-2009 Thijs Putman, all rights reserved.
 * @copyright © 2010-2016 StudyPortals B.V., all rights reserved.
 * @version 1.10.0
 */

namespace StudyPortals\Exception;

if(!defined('DEFAULT_CHARSET')){

	define('DEFAULT_CHARSET', 'ISO-8859-1');
}

/**
 * Exception Handler Helper.
 *
 * <p>Container class for exception-handler, error-handler and assert-handler
 * and bootstrap logic to get our custom error-handling up and running.
 *
 * @package StudyPortals.Framework
 */

abstract class ExceptionHandler{

	protected static $_enabled = false;

	protected static $_error_folder = null;

	/**
	 * Enable all custom error-, exception- and assert-handling.
	 *
	 * <p>This overwrites the built-in exception-handling, error-handling and
	 * assert logic with the ones provided by the Framework (c.q. as defined in
	 * this file). The optional argument {@link $state} can be used to
	 * revert back to the previous (c.q. built-in) handlers.</p>
	 *
	 * <p><strong>Note:</strong> Repeated calls to this method have no effect;
	 * once enabled it will not re-enable (to ensure the previous handlers can
	 * be consistently restored). If other parts of the code start messing with
	 * the error-handling you're on your own though...<br>
	 * <strong>N.B.</strong>: The {@link $enable_assert} parameter <em>is</em>
	 * processed during repeat-calls to this method, so it is possible to
	 * repeatedly change assert()-evaluation via this method (even though
	 * calling {@link ExceptionHandler::enableAssert()} directly is the
	 * recommended way).</p>
	 *
	 * @param boolean $state
	 * @param boolean $enable_assert
	 * @return void
	 * @uses ExceptionHandler::enableAssert()
	 */

	public static function enable($state = true, $enable_assert = false){

		$state = (bool) $state;

		self::enableAssert((bool) $enable_assert);

		if($state && self::$_enabled || !$state && !self::$_enabled){

			return;
		}

		if($state){

			set_exception_handler(__CLASS__ . '::exception');
			set_error_handler(__CLASS__ . '::error', error_reporting());
			assert_options(ASSERT_CALLBACK, __CLASS__ . '::assert');

			self::$_enabled = true;
		}
		else{

			restore_exception_handler();
			restore_error_handler();

			/*
			 * According to the PHP documentation this should reset to NULL,
			 * but doing so generates an error when evaluating assertions, so
			 * we instead revert to an empty function...
			 */

			assert_options(ASSERT_CALLBACK, function(){});

			self::$_enabled = false;
		}
	}

	/**
	 * Enable (evaluation of) assertions.
	 *
	 * <p>When enabled assertions are evaluated and failing an assertions will
	 * result in either a warning being raised (or exception being thrown) or
	 * execution being halted (handled by {@link ExceptionHandler::assert()};
	 * depending on the value of "assert_bail").</p>
	 *
	 * <p><strong>Note</strong>: Assertions are enabled by default in PHP!
	 * Calling {@link ExceptionHandler::enable()} will call this method and
	 * <strong>disable</strong> assertions. This is done to ensure PHP's error/
	 * assertion "subsystem" is in a consistent state.<br>
	 * If you wish to use assertions without enabling exception-handling,
	 * call this method separately <em>after</em> disabling exceptions to
	 * (re-)enable assertions.</p>
	 *
	 * <p>It is safe to call this method without first calling {@link
	 * ExceptionHandler::enable()}; this method leaves the system in a
	 * consistent state.<br>
	 * <strong>N.B.</strong>: When calling this method without first calling
	 * {@link ExceptionHandler::enable()}, assert()-evaluation gets enabled,
	 * but no action will be taken when an assertion fails! You need to manually
	 * set an assert-callback, or enable either {@link ASSERT_BAIL} or {@link
	 * ASSERT_WARNING} to get feedback on your assertions.</p>
	 *
	 * @param boolean $state
	 * @return void
	 * @see ExceptionHandler::assert()
	 */

	public static function enableAssert($state = true){

		$state = (bool) $state;

		assert_options(ASSERT_ACTIVE, $state);
		assert_options(ASSERT_QUIET_EVAL, false);

		// Set assert-bail and -warning to the opposite of assert-active

		assert_options(ASSERT_BAIL, !$state);
		assert_options(ASSERT_WARNING, !$state);
	}

	/**
	 * This sets the error location to be used when logging lines.
	 *
	 * @param string $error_folder
	 * @return void
	 */

	public static function setErrorFolder($error_folder){

		self::$_error_folder = $error_folder;
	}

	/**
	 * Trigger an "assert-type" notice.
	 *
	 * <p>In some situation we want to evaluate an assertion, but know on beforehand
	 * the expression will evaluate to <em>false</em>. This happens, for example,
	 * in the "default" case of a switch statement. In those situation we (ab)use
	 * the assertion as a "stricter than FireLogger" logging mechanism.</p>
	 *
	 * <p>In those situation it is prudent to trigger an E_USER_NOTICE instead of
	 * an assertion, as this better fulfills the goal of strict (but not fatal)
	 * logging. This method is purely intended as some syntactic "sugar" around the
	 * regular call to {@link trigger_error()}. This is in anticipation of the fact
	 * that in the future we might find an even better way to handle these
	 * situations.</p>
	 *
	 * <p>When the self::enableNoticeLogLine function has been called with a log
	 * file, this function will write a log line. This needs to happen before
	 * calling trigger_error, because on live environments the E_USER_NOTICE
	 * errors are ignored.</p>
	 *
	 * <p>Under normal circumstances (c.q. when used in conjunction with the other
	 * functions offered in {@link ExceptionHandler.php}) this method will generate
	 * a PHPErrorException.</p>
	 *
	 * @param $message
	 * @return void
	 */

	public static function notice($message){

		// Remove all superfluous white-spaces for increased readability

		$message = preg_replace('/\s+/', ' ', $message);

		static::writeLogLine('Notices.log', $message);

		trigger_error($message, E_USER_NOTICE);
	}

	/**
	 * Properly display all uncaught exceptions.
	 *
	 * <p>Automatically switches between a plain-text and HTML exception
	 * depending upon the SAPI.</p>
	 *
	 * @param $Throwable
	 * @return void
	 * @see BaseException::displayException()
	 * @see BaseException::displayConsoleException()
	 */

	public static function exception($Throwable){

		// Dump all output buffers

		while(@ob_end_clean());

		try{

			// Command-line interface

			if(PHP_SAPI == 'cli'){

				$message = BaseException::displayConsoleException($Throwable);
				if(@fwrite(STDERR, $message) === false) echo $message;
			}

			// HTTP/1.1

			else{

				@header("HTTP/1.1 500 Internal Server Error");
				@header('Content-Type: text/html');

				echo BaseException::displayException($Throwable);
			}
		}

			// Exception while displaying the exception

		catch(\Throwable $e){

			$class = get_class($e);
			$message = $e->getMessage();

			echo "Uncaught $class inside exception-handler: \"$message\"";
		}

		exit(1);
	}

	/**
	 * Convert all PHP-errors into exceptions.
	 *
	 * <p>When the FireLogger class exists and is enabled, this function
	 * attempts to utilise it to log all non-fatal errors. This means that in
	 * these cases, non-fatal errors are <strong>not</strong> converted to
	 * Exceptions and also do not affect the original code-flow.</p>
	 *
	 * <p>If assertions are enabled and error_handler() detect it is being
	 * called from a <strong>within</strong> an assertion, execution is
	 * immediately halted. Not doing so has the potential to lead to
	 * <em>very</em> wacky behaviour.</p>
	 *
	 * @param integer $severity
	 * @param string $message
	 * @param string $file
	 * @param integer $line
	 * @return void
	 * @throws PHPErrorException
	 * @see ExceptionHandler::exception()
	 */

	public static function error($severity, $message, $file, $line){

		// Respect the "@" error suppression operator

		if(error_reporting() == 0) return;

		elseif(error_reporting() && $severity){

			$ErrorException = new PHPErrorException(
				$message, 0, $severity, $file, $line);

			// If we're in an assert()-chain, *immediately* terminate execution

			if(assert_options(ASSERT_ACTIVE)){

				foreach($ErrorException->getStackTrace() as $element){

					if(isset($element['function']) &&
						$element['function'] == 'assert'){

						self::exception($ErrorException);
					}
				}
			}

			$recoverable = [
				E_WARNING,
				E_NOTICE,
				E_USER_WARNING,
				E_USER_NOTICE,
				E_STRICT,
				E_DEPRECATED,
				E_USER_DEPRECATED
			];

			// Only for non-fatal errors

			if(in_array($severity, $recoverable)){

				return;
			}

			throw $ErrorException;
		}
	}

	/**
	 * Handle failed assertions.
	 *
	 * <p>Depending on the state of <em>ASSERT_BAIL</em>, this method either
	 * throws a, catchable, PHPAssertionFailed exception, when bail is disabled,
	 * or displays an assertion failed message and terminates execution.</p>
	 *
	 * <p>Alternatively: When FireLogger is available <em>and</em> enabled this
	 * method defers assertion-handling to FireLogger (which results in the
	 * failed assertion getting displayed by FireLogger).</p>
	 *
	 * @param string $file
	 * @param integer $line
	 * @param string $expression
	 * @return void
	 * @throws PHPAssertionFailed
	 * @uses FireLogger::logException()
	 */

	public static function assert($file, $line, $expression){

		$Exception = new PHPAssertionFailed(
			'', 0, null, $file, $line, $expression);

		// Try FireLogger (only if not yet involved)

		if(assert_options(ASSERT_BAIL)){

			// Terminate execution after failed assertion (ASSERT_BAIL)

			self::exception($Exception);
		}
		else{

			// Throw the Exception (can be caught; non-fatal)

			throw $Exception;
		}
	}

	/**
	 * Write a line to a specified log-file in the error folder.
	 *
	 * <p>While the output of {@link BaseException::generateExceptionLog()} is
	 * useful for detailed troubleshooting, it is less than ideal when it
	 * comes to providing summaries/overviews of exceptions that have occurred
	 * over a certain period of time.</br>
	 * So, instead of writing a full stack-trace and environment information
	 * this method writes a single line to a log-file providing some basic
	 * details of the {@link $input}.</p>
	 *
	 * <p>This function will also accept a simple message string as input.
	 * It will not contain any hints on file and location, but at least you can
	 * log your message to a specific file in the error location.</p>
	 *
	 * <p>The format of the log-file generated closely resembles that of PHP's
	 * internal error log. The only difference is the timestamp format for
	 * which I'm unable to find a constant matching the format used by PHP's
	 * error log...</p>
	 *
	 * Returns <em>true</em> on success, <em>false</em> on failure.</p>
	 *
	 * @param string $log_file
	 * @param string/Throwable $input
	 * @param integer $timestamp
	 * @return bool
	 */

	public static function writeLogLine($log_file, $input, $timestamp = null){

		if(is_null(self::$_error_folder)){

			return false;
		}

		// Prevent people from escaping the pre-defined folder
		$log_file = basename($log_file);

		$fp = @fopen(self::$_error_folder . $log_file, 'ab'); // NOSONAR

		if(!$fp){

			return false;
		}

		if(empty($timestamp)){

			$timestamp = time();
		}

		$line = [];
		$line[] = date(\DateTime::ISO8601, $timestamp);

		if($input instanceof \Throwable){

			$message = $input->getMessage();

			if(!($input instanceof BaseException)){

				/**
				 * Remove superfluous whitespace from the message (mostly to
				 * prevent the message from spanning multiple lines, making the
				 * log-file more difficult to analyse).
				 * SP\F\BaseException does this in its constructor; our old
				 * BaseException (and external exceptions) will not, so we do it
				 * here for them...
				 */

				$message = preg_replace('/\s+/', ' ', $message);
			}

			$line[] = BaseException::getShortName(get_class($input));
			$line[] = $message;
			$line[] = $input->getFile();
			$line[] = $input->getLine();

			$line_out = vsprintf('[%s] %s: %s in %s on line %d', $line);
		}
		elseif(is_string($input)){

			$line[] = $input;
			$line_out = vsprintf('[%s] %s', $line);
		}
		else{

			return false;
		}

		// Block until we acquire an exclusive lock

		if(flock($fp, LOCK_EX)){

			fwrite($fp, $line_out . PHP_EOL);

			flock($fp, LOCK_UN);
			fclose($fp);

			return true;
		}
		else{

			return false;
		}
	}
}
