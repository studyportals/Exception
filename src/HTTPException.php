<?php
/**
 * @file HTTPException.php
 *
 * @author Rob van den Hout <vdhout@studyportals.com>
 * @version 1.0.0
 * @copyright © 2017 StudyPortals B.V., all rights reserved.
 */

namespace StudyPortals\Exception;

/**
 * HTTPException.
 *
 * <p>"I'm sorry, your princess is in another castle." Do not use this interface
 * because you think you need it, I (RobH ;) can almost guarantee that you
 * don't...</p>
 *
 * <p><strong>"Why?"</strong> you might ask: Well, this interface is intended
 * for a couple of our "core" HTTP exceptions. You should <em>never</em> add
 * this interface to any of your own exceptions, instead always extend one of
 * the existing core HTTP exceptions.<br/>
 * Not doing this <strong>will</strong> break the build, at the very least
 * lead to unexpected side-effects. This is especially true when you create
 * exceptions that generate status codes that are not part of the core HTTP
 * exceptions (e.g. "404 Not Found"). You will break the core logic of many of
 * our projects as they rely on that fact that only a limited set of exceptions
 * provide HTTP status codes.<br/>
 * Things like "404 Not Found" are not considered exceptions in those projects
 * and are (and should) thus be handled outside of the generalised exception
 * handling logic.</p>
 *
 * @package StudyPortals.Framework
 * @subpackage Utils
 */
interface HTTPException{

	/**
	 * Get the statuscode of this exception.
	 *
	 * @return integer
	 */

	public function getStatusCode();

	/**
	 * Get the message that belongs to this status code.
	 *
	 * @return string
	 */

	public function getStatusMessage();
}