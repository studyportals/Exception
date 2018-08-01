<?php
/**
 * @file PHPAssertionFailed.php
 *
 * @author Rob van den Hout <vdhout@studyportals.com>
 * @version 1.0.0
 * @copyright © 2017 StudyPortals B.V., all rights reserved.
 */

namespace StudyPortals\Exception;

/**
 * PHPAssertionFailed.
 *
 * @package StudyPortals.Framework
 */
class PHPAssertionFailed extends BaseException{

	protected $_expression;

	/**
	 * Construct a new PHPAssertionFailed exception.
	 *
	 * <p>The value for the {@link $message} parameter is ignored. The {@link
	 * $expression} parameter is used to generate an exception message. The
	 * {@link $message} parameter is required for strict compliance with PHP5
	 * coding standards.</p>
	 *
	 * @param string $message
	 * @param integer $code
	 * @param \Throwable $Previous
	 * @param string $file
	 * @param integer $line
	 * @param string $expression
	 */

	public function __construct($message = '', $code = 0,
		\Throwable $Previous = null, $file = '', $line = 0, $expression = ''){

		// Remove all superfluous white-spaces for increased readability

		$this->_expression = preg_replace('/\s+/', ' ', $expression);

		// Initialise the underlying BaseException

		if($expression == ''){

			$expression = 'false';
		}

		parent::__construct(
			"Assertion \"$expression\" failed",
			$code,
			$Previous
		);

		// Update the exception properties so they point to the failed assertion

		if($file != '' && $line > 0){

			$this->line = (int) $line;
			$this->file = $file;
		}
	}

	/**
	 * Get a proper stack trace for the {@link PHPAssertionFailed} exception.
	 *
	 * @return array
	 * @see BaseException::getStackTrace()
	 */

	public function getStackTrace(){

		$trace = parent::getStackTrace();

		// Remove the calls to "assert_handler" and "assert" itself from the trace

		$trace = array_slice($trace, 2);

		return $trace;
	}

	/**
	 * Get the failed expression.
	 *
	 * @return string
	 */

	public function getExpression(){

		return $this->_expression;
	}
}