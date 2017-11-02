<?php
/**
 * @file Silenced.php
 *
 * @author Rob van den Hout <vdhout@studyportals.com>
 * @version 1.0.0
 * @copyright © 2017 StudyPortals B.V., all rights reserved.
 */

namespace StudyPortals\Framework\Exception;

/**
 * Interface Silenced.
 *
 * <p>Silences objects (c.q. hides their contents) in stack-traces; when applied
 * to instances of {@link Exception} it silences that Exception's entire stack-
 * trace.</p>
 *
 * <p>Any (non-Exception) class that implements this interface will only have
 * its class name displayed as part of an Exception's stack-trace. This feature
 * can be used to filter irrelevant "noise" classes (such as templates and
 * session-handlers) and to limit (but not to fully prevent!) the chance of
 * leaking confidential information through stack-traces.</p>
 *
 * <p><strong>N.B.</strong>: The filtering only applies when the instance is an
 * <em>argument</em> of a function/method call inside the stack-trace. If a
 * method is called directly on a silenced instance all function arguments are
 * shown (unless of course they themselves are silenced). This is by design and
 * should not be changed as doing so will make debugging virtually impossible
 * in some cases.<br>
 * If the above is a problem apply this interface to the Exception containing
 * the unwanted information. This will cause the entire Exception's stack-trace
 * to be silenced (c.q. only include argument types, but no information about
 * the contents of the arguments).
 *
 * @package StudyPortals.Framework
 */
interface Silenced{

}