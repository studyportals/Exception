<?php
/**
 * @file IBaseException.php
 *
 * @author Rob van den Hout <vdhout@studyportals.com>
 * @version 1.0.0
 * @copyright © 2017 StudyPortals B.V., all rights reserved.
 */

namespace StudyPortals\Exception;

/**
 * BaseException.
 *
 * @package StudyPortals.Framework
 */
interface IBaseException{

	/**
	 * Provide an alternative to (not overwrite-able) Exception::getTrace().
	 *
	 * <p>This method provides an alternative entry-point into the {@link
	 * Exception::getTrace} method, which cannot be overwritten. Also, this
	 * method returns the trace in reverse order, placing the actual point of
	 * failure in the first element of the trace array.</p>
	 *
	 * @return array
	 * @uses Exception::getTrace()
	 */
	public function getStackTrace();
}