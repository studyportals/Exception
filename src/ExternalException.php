<?php
/**
 * @file ExternalException.php
 *
 * @author Rob van den Hout <vdhout@studyportals.com>
 * @version 1.0.0
 * @copyright © 2017 StudyPortals B.V., all rights reserved.
 */

namespace StudyPortals\Framework\Exception;

/**
 * Interface ExternalException.
 *
 * <p>Some exceptions (like most ServiceLayerClientExceptions) are
 * originally thrown in an external source. Because of this, there is not always
 * enough information in the generated exception to pinpoint what the error
 * actually is. For these exceptions the ExternalException interface adds
 * functionality to set and get data provided by the external source.</p>
 *
 * <p>The data passed into the ExternalException will be used inside the
 * BaseException::generateExceptionLog() function to render a more complete
 * exception log.</p>
 *
 * @package StudyPortals.Framework
 */
interface ExternalException{

	/**
	 * Get the external data from the exception.
	 *
	 * @return string
	 */

	public function getExternalData();

	/**
	 * Set the external data.
	 *
	 * @param string $data
	 *
	 * @return void
	 */

	public function setExternalData($data);
}