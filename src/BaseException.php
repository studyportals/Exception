<?php
/**
 * @file BaseException.php
 *
 * @author Rob van den Hout <vdhout@studyportals.com>
 * @version 1.0.0
 * @copyright © 2017 StudyPortals B.V., all rights reserved.
 */

namespace StudyPortals\Exception;

use Exception;

/**
 * BaseException.
 *
 * @package StudyPortals.Framework
 */
class BaseException extends Exception implements IBaseException{

	/**
	 * Line-length of text-output on the console.
	 *
	 * @var integer
	 */

	const CONSOLE_WIDTH = 78;

	/**
	 * Create a new Exception.
	 *
	 * @param string $message
	 * @param integer $code
	 * @param \Throwable $Previous
	 */

	public function __construct(
		$message = '', $code = 0, \Throwable $Previous = null){

		// Remove all superfluous white-spaces for increased readability

		$message = preg_replace('/\s+/', ' ', $message);

		parent::__construct($message, $code, $Previous);
	}

	/**
	 * Generated a user-readable error page.
	 *
	 * <p>This method automatically selects the correct output type, based upon
	 * the current SAPI.</p>
	 *
	 * @return string
	 * @see BaseException::displayException()
	 * @see BaseException::displayConsoleException()
	 */

	public function __toString(){

		// Dump all output buffers

		while(@ob_end_clean()){
			;
		}

		try{

			if(PHP_SAPI == 'cli'){

				return self::displayConsoleException($this);
			}

			$statusCode = 500;
			$statusMessage = 'Internal Server Error';

			if($this instanceof HTTPException){

				$statusCode = $this->getStatusCode();
				$statusMessage = $this->getStatusMessage();

			}

			@header("HTTP/1.1 $statusCode $statusMessage");
			@header('Content-Type: text/html');

			return self::displayException($this);
		}

		catch(\Throwable $e){

			return $e->getMessage();
		}
	}

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

	public function getStackTrace(){

		$trace = $this->getTrace();
		array_reverse($trace);

		return $trace;
	}

	/**
	 * Provide an array of all parent classes for the provided Exception.
	 *
	 * @param \Throwable $Throwable
	 *
	 * @return array
	 */

	public static function getExceptionTree(\Throwable $Throwable){

		$exception = get_class($Throwable);

		// Build the "tree"

		for($exception_tree[] = $exception; $exception =
			get_parent_class($exception); $exception_tree[] = $exception){
			;
		}

		$exception_tree = array_reverse($exception_tree);

		if(count($exception_tree) > 1){
			array_shift($exception_tree);
		}

		return $exception_tree;
	}

	/**
	 * Get the "short" version of a fully-qualified class name.
	 *
	 * <p>This method compresses the namespace part of a fully-qualified class
	 * name to only capital letters (so, "StudyPortals\Framework" becomes
	 * "SP\F"). The "actual" class name is left untouched.<br>
	 * This creates short, but still readable, class names (including their
	 * namespace) for use in places were space is limited (c.q. console errors,
	 * file names and lines with many class names).</p>
	 *
	 * @param string $fqn
	 *
	 * @return string
	 */

	public static function getShortName($fqn){

		$fqn_parts = explode('\\', $fqn);
		$final = array_pop($fqn_parts);

		if(empty($fqn_parts)){

			return $final;
		}

		$fqn_caps = preg_replace('/[a-z]+/', '', $fqn_parts);

		return implode('\\', $fqn_caps) . '\\' . $final;

	}

	/**
	 * Display an HTML page with information about the Exception.
	 *
	 * @param \Throwable $Throwable
	 *
	 * @return string
	 */

	public static function displayException(\Throwable $Throwable){

		$muted = false;

		if($Throwable instanceof Silenced){

			$muted = true;
		}

		ob_start();

		?>

		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
			"http://www.w3.org/TR/html4/strict.dtd">

		<!--suppress HtmlUnknownTag -->
		<html>
		<head>
			<title>
				<?php

				if($Throwable instanceof PHPAssertionFailed){

					?>
					Assertion Failed in <?= basename($Throwable->getFile()) ?>
					on line <?= $Throwable->getLine() ?>
					<?php

				}
				else{

					$exception_parts = explode(
						'\\',
						get_class(
							$Throwable
						)
					);
					echo end($exception_parts);

					?> in
					<?= basename($Throwable->getFile()) ?> on line
					<?= $Throwable->getLine() ?>
					<?php

				}

				?>
			</title>
			<script type="text/javascript"
			        src="https://ajax.googleapis.com/ajax/libs/mootools/1.4.5/mootools-yui-compressed.js"></script>
			<script type="text/javascript">

				document.addEvent('domready', function(){

					var toggle = function(){

						var emote = $('Emote');

						var mouth = emote.get('text').charAt(0);
						var eyes = emote.get('text').charAt(1);

						if(eyes === ';'){

							eyes = ':';
						}else{

							eyes = ';';
						}

						emote.set('text', mouth + eyes);
					};

					var blink = function(){

						toggle();
						toggle.delay(120 + (240 * Math.random()));
					};

					var animate = function(){

						blink();

						animate.delay((4200 * Math.random()) + 1200);
					};

					animate.delay(1500);
				});

				document.addEvent('click:relay(span.Arg)', function(event){

					var element = $(event.target).getParent().getElement('code');

					if(element.retrieve('forced')){

						element.store('forced', false);

						element.setStyle('display', 'none');
						element.setStyle('overflow', 'hidden');
					}else{

						element.store('forced', true);

						element.setStyle('display', 'block');
						element.setStyle('overflow', 'scroll');
					}

				});

				var hoverToggle = function(event){

					return function(element){

						if(event === 'mouseover'){

							element.setStyle('display', 'block');
						}

						else
							if(event === 'mouseout'){

								element.setStyle('display', 'none');
							}
					};
				};

				var hoverOver = hoverToggle('mouseover');
				var hoverOut = hoverToggle('mouseout');

				document.addEvent('mouseover:relay(span.Function)', function(event){

					var element = $(event.target).getParent().getElement('table');

					hoverOver(element);
				});

				document.addEvent('mouseout:relay(span.Function)', function(event){

					var element = $(event.target).getParent().getElement('table');

					hoverOut(element);
				});

				document.addEvent('mouseover:relay(span.Arg)', function(event){

					var element = $(event.target).getParent().getElement('code');

					if(element.retrieve('forced')) return;

					hoverOver(element);
				});

				document.addEvent('mouseout:relay(span.Arg)', function(event){

					var element = $(event.target).getParent().getElement('code');

					if(element.retrieve('forced')) return;

					hoverOut(element);
				});

			</script>
			<style type="text/css">
				body {
					font-family: Segoe UI, Arial, sans-serif;
					cursor: default;
				}

				h1 {
					margin-top: 5px;
					font-size: 48px;
				}

				h1 .Emote {
					color: maroon;
					position: relative;
					left: 8px;
					top: 1px;
					font-size: 72px;
					margin-right: 16px;
				}

				h1 em {
					color: graytext;
					font-style: normal;
					font-weight: normal;
				}

				h2 {
					font-size: 20px;
				}

				a {
					text-decoration: none;
				}

				a:hover {
					text-decoration: underline;
					color: blue;
				}

				td {
					vertical-align: top;
				}

				.Hidden {
					display: none;
				}

				span.Boolean {
					font-style: italic;
				}

				.ErrorMessage {
					margin-left: -20px;
					font-size: 12px;
					border-spacing: 20px 5px;
				}

				.ErrorMessage td:nth-child(1),
				.Environment td:nth-child(1) {
					font-weight: bold;
					white-space: nowrap;
				}

				.ErrorMessage ul.ExceptionTree {
					margin-top: 0;
					margin-left: -40px;
					list-style-type: none;
				}

				.ErrorMessage ul.ExceptionTree li {
					float: left;
				}

				.ErrorMessage ul.ExceptionTree li.Parent:after {
					content: "\00bb";
					padding-left: 4px;
					padding-right: 4px;
					font-weight: bold;
					color: graytext;
				}

				.ErrorMessage ul.ExceptionTree li.Exception {
					color: red;
					text-decoration: underline;
					font-weight: bold;
				}

				.ErrorMessage ul.ExceptionTree li.Exception strong {
					color: black;
				}

				.ErrorMessage tr.Message td {
					max-width: 720px;
				}

				ul.TraceItem {
					font-size: 12px;
				}

				ul.TraceItem li.Function {
					float: left;
					list-style-type: circle;
					padding: 0;
					margin-left: -10px;
				}

				ul.TraceItem li.Arg {
					float: left;
					list-style-type: none;
					padding: 0 5px;
				}

				ul.TraceItem .Elem {
					margin-top: 10px;
					margin-left: 10px;
					font-size: 11px;
					padding: 0;
					list-style-type: square;
					display: none;
					color: black;
					position: absolute;
					border: 1px solid black;
					background-color: infobackground;
					border-spacing: 10px 2px;
					max-width: 60%;
					max-height: 40%;
					overflow: hidden;
				}

				ul.TraceItem code.Elem {
					font-family: Lucida Console, monospace;
					margin: 0;
					font-size: 10px;
					padding: 4px 6px;
					white-space: pre;
				}

				ul.TraceItem table td:nth-child(1) {
					color: grey;
				}

				ul.TraceItem li.Arg {
					color: maroon;
				}

				ul.TraceItem li.Arg:after {
					content: ", ";
				}

				ul.TraceItem li.Arg:last-child:after {
					content: none;
				}

				ul.TraceItem li.Arg:hover span.Arg {
					color: red;
					cursor: pointer;
				}

				ul.TraceItem li.Function {
					color: navy;
				}

				ul.TraceItem li.Function:hover span.Function {
					color: blue;
				}

				li.Function:after {
					content: " (";
				}

				ul.TraceItem:after {
					content: " )";
				}
			</style>
		</head>

		<body>

		<?php

		// Assertion

		if($Throwable instanceof PHPAssertionFailed){

			?>
			<h1>
				<strong id="Emote" class="Emote">\:</strong>
				Assertion&middot;Failed
			</h1>
			<?php

		}

		// Exception

		else{

			?>
			<h1>
				<strong id="Emote" class="Emote">):</strong>
				<?php

				$exception_parts = explode(
					'\\',
					get_class(
						$Throwable
					)
				);
				$base = end($exception_parts);

				// Split name at capital letter boundaries

				$base_parts = preg_split(
					'/([A-Z]+[a-z]+)/',
					$base,
					-1,
					PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
				);

				$final = array_pop($base_parts);

				if(!empty($base_parts)){

					echo implode('&middot;', $base_parts)

					?>
					<em>&middot;<?= $final ?></em>
					<?php
				}

				else{

					echo $final;
				}
				?>
			</h1>
			<?php

		}

		?>

		<table class="ErrorMessage">
			<tr>
				<td>Origin:</td>
				<td>
					<?= $Throwable->getFile() ?>
					<strong>&lt;<?= $Throwable->getLine() ?>&gt;</strong>
				</td>
			</tr>
			<?php

			if(!($Throwable instanceof PHPAssertionFailed)){

				?>
				<tr>
					<td>Type:</td>
					<td>

						<ul class="ExceptionTree">

							<?php

							// Exception Tree

							$exception_tree = BaseException::getExceptionTree(
								$Throwable
							);

							foreach($exception_tree as $key => $exception){

								// Exception thrown

								if($key + 1 == count($exception_tree)){

									?>

									<li class="Exception">
										<strong><?= $exception ?></strong>
									</li>

									<?php

								}

								// Parent exceptions

								else{

									?>

									<li class="Parent">
										<?= self::getShortName(
											$exception
										) ?></li>

									<?php

								}
							}

							?>

						</ul>

					</td>
				</tr>
				<?php

			}

			?>

			<?php

			// Assertion

			if($Throwable instanceof PHPAssertionFailed){

				$expression = $Throwable->getExpression();

				if($expression == ''){
					$expression = 'false';
				}

				?>
				<tr>
					<td>Expression:</td>
					<td><?php

						$expression =
							highlight_string("<?php $expression ?>", true);
						$expression = str_replace(
							['&lt;?php&nbsp;', '?&gt;'],
							'',
							$expression
						);

						echo $expression;

						?></td>
				</tr>
				<?php

			}

			// Exception

			else{

				?>
				<tr class="Message">
					<td>Message:</td>
					<td><?= htmlspecialchars(
							$Throwable->getMessage(),
							ENT_COMPAT | ENT_HTML401,
							DEFAULT_CHARSET
						) ?></td>
				</tr>
				<?

			}

			?>

		</table>

		<?php

		// Only show stack-trace on local system (IPv4, IPv6, DNS)

		if(!empty($_SERVER['REMOTE_ADDR']) &&
			in_array(
				$_SERVER['REMOTE_ADDR'],
				['127.0.0.1', '::1', 'localhost']
			)){

			?>

			<h2>Stack Trace</h2>

			<?php

			if($Throwable instanceof BaseException ||
				$Throwable instanceof PHPErrorException){

				/** @noinspection PhpUndefinedMethodInspection */

				$trace = $Throwable->getStackTrace();
			}
			else{

				$trace = array_reverse($Throwable->getTrace());
			}

			foreach($trace as $trace_item){

				if(strpos($trace_item['function'], '{closure}') !== false){

					$trace_item['function'] = '{closure}';
				}

				?>

				<ul class="TraceItem">
					<li class="Function">
						<span class="Function"><?=
							(isset($trace_item['class']) ?
								self::getShortName($trace_item['class']) : '') .
							(isset($trace_item['type']) ? $trace_item['type'] :
								'') .
							(isset($trace_item['function']) ?
								$trace_item['function'] : '')
							?></span>
						<table class="Elem">
							<tr>
								<td>FQN:</td>
								<td><?=
									(isset($trace_item['class']) ?
										$trace_item['class'] : '') .
									(isset($trace_item['type']) ?
										$trace_item['type'] : '') .
									(isset($trace_item['function']) ?
										$trace_item['function'] : '')
									?>()
								</td>
							</tr>
							<tr>
								<td>File:</td>
								<td><?= (isset($trace_item['file']) ?
										basename($trace_item['file']) :
										'') ?></td>
							</tr>
							<tr>
								<td>Line:</td>
								<td><?= (isset($trace_item['line']) ?
										$trace_item['line'] : '') ?></td>
							</tr>
							<tr>
								<td>Directory:</td>
								<td><?= (isset($trace_item['file']) ?
										dirname($trace_item['file']) :
										'') ?></td>
							</tr>
						</table>
					</li>

					<?php

					foreach((array) $trace_item['args'] as $arg){

						$arg_boolean = false;
						$arg_black = '';
						$arg_red = '';

						// String

						if(is_string($arg)){

							$arg_black = '[' . strlen($arg) . ']';
							$arg_red = gettype($arg);
						}

						// Array

						elseif(is_array($arg)){

							if(count($arg) > 0){

								$arg_black = '[' . count($arg) . ']';
								$arg_red = gettype($arg);
							}
							else{

								$arg_black = 'array[0]';
							}
						}

						// Boolean

						elseif(is_bool($arg)){

							$arg_boolean = true;
							$arg_black = ($arg ? 'true' : 'false');
						}

						// NULL

						elseif(is_null($arg)){

							$arg_boolean = true;
							$arg_black = 'null';
						}

						// Number

						elseif(is_int($arg) || is_float($arg)){

							$arg_black = $arg;
						}

						// Object

						elseif(is_object($arg)){

							$arg_black = '';

							// Treat "silent" classes as a scalar value

							if($arg instanceof Silenced){

								$arg_black = BaseException::getShortName(
									get_class($arg)
								);
							}

							if($arg_black == ''){

								$arg_red = BaseException::getShortName(
									get_class($arg)
								);
							}
						}

						// Other

						else{

							$arg_red = gettype($arg);
						}

						?>

						<li class="Arg">

								<span class="<?= ($muted ? '' : 'Arg') ?>">
									<?= $arg_red ?>
								</span><?php

							if($arg_boolean){

								?>

								<span class="Boolean"><?= $arg_black ?></span>

								<?php

							}
							else{

								echo $arg_black;
							}

							// Argument Details

							if($arg_red && !$muted){

								?>

								<code class="Elem"><?= htmlspecialchars(
										print_r($arg, true),
										ENT_COMPAT | ENT_HTML401,
										DEFAULT_CHARSET
									); ?></code>

								<?php
							}

							?>

						</li>

						<?php

					}

					?>

				</ul>
				<br class="Hidden">

				<?php

			}
		}

		?>

		<h2>Environment</h2>

		<table class="ErrorMessage">
			<tr>
				<td>PHP:</td>
				<td><?= PHP_VERSION ?> (<?= PHP_SAPI ?>)</td>
			</tr>
			<?php

			if(isset($_SERVER['SERVER_SOFTWARE'])){

				?>

				<tr>
					<td>Server:</td>
					<td><?= $_SERVER['SERVER_SOFTWARE'] ?></td>
				</tr>

				<?php

			}

			?>
			<tr>
				<td>Time:</td>
				<td><?= date('d-m-Y H:i:s') ?></td>
			</tr>
		</table>

		</body>
		</html>

		<?php

		return ob_get_clean();
	}

	/**
	 * Display a console "page" with information about the Exception.
	 *
	 * @param \Throwable $Throwable
	 *
	 * @return string
	 */

	public static function displayConsoleException(\Throwable $Throwable){

		ob_start();

		echo PHP_EOL . ' ';

		echo(($Throwable instanceof PHPAssertionFailed) ?
			'Assertion Failed' : 'Uncaught ' . self::getShortName(
				get_class(
					$Throwable
				)
			));
		echo ' <' . basename($Throwable->getFile()) . ':' .
			$Throwable->getLine() . '>';

		echo PHP_EOL . PHP_EOL . '  ';

		// Message

		if($Throwable instanceof PHPAssertionFailed){

			$message = $Throwable->getExpression();
			if($message == ''){
				$message = 'false';
			}
		}
		else{

			$message = $Throwable->getMessage();
		}

		echo wordwrap($message, self::CONSOLE_WIDTH - 2, PHP_EOL . '  ');

		echo PHP_EOL . PHP_EOL . ' Stack Trace:' . PHP_EOL . PHP_EOL;

		// Stack trace

		if($Throwable instanceof BaseException ||
			$Throwable instanceof PHPErrorException){

			/** @noinspection PhpUndefinedMethodInspection */

			$trace = $Throwable->getStackTrace();
		}
		else{

			$trace = array_reverse($Throwable->getTrace());
		}

		$trace_empty = [
			'class' => '',
			'type' => '',
			'function' => '',
			'file' => '{unknown}',
			'line' => 0
		];

		foreach($trace as $key => $trace_item){

			$trace_item = array_merge($trace_empty, $trace_item);
			$trace_item['file'] = basename($trace_item['file']);

			if($trace_item['function'] != '{closure}'){

				$trace_item['function'] .= '()';
			}

			$key++;

			echo str_pad("  $key. ", 6, ' ');
			echo self::getShortName($trace_item['class']) .
				$trace_item['type'] .
				$trace_item['function'];
			echo " <{$trace_item['file']}:{$trace_item['line']}>" . PHP_EOL;
		}

		return ob_get_clean();
	}

	/**
	 * Generate an XML-based exception-log.
	 *
	 * <p>Similar to the output of {@link BaseException::displayException()}
	 * but better suited for automated processing. The XML generated by this
	 * method can stored and used for ex-post analysis of the Exception.</p>
	 *
	 * <p>The optional {@link $timestamp} parameter contains the timestamp to
	 * be listed in the log. If omitted, the current time is used. This
	 * parameter is useful to "sync up" the logged timestamp with the one used
	 * in the actual creation of the exception-log based upon this XML output.
	 * </p>
	 *
	 * @param \Throwable $Throwable
	 * @param integer $timestamp
	 *
	 * @return string
	 */

	public static function generateExceptionLog(
		\Throwable $Throwable, $timestamp = null){

		if(empty($timestamp)){

			$timestamp = time();
		}

		if($Throwable instanceof BaseException ||
			$Throwable instanceof PHPErrorException){

			/** @noinspection PhpUndefinedMethodInspection */

			$trace = $Throwable->getStackTrace();
		}
		else{

			$trace = array_reverse($Throwable->getTrace());
		}

		/**
		 * Convenience method for Windows-1252 to UTF-8 iconv().
		 *
		 * @param $string
		 *
		 * @return string
		 */

		$iconv = function($string){

			if(mb_detect_encoding($string,'Windows-1252, UTF-8, ISO-8859-1') === 'UTF-8'){
				
				return $string;
			}
			
			return @iconv('Windows-1252', 'UTF-8//TRANSLIT', $string);
		};

		/**
		 * Convenience method for UTF-8 htmlspecialchars().
		 *
		 * @param $string
		 *
		 * @return string
		 */

		$html_encode = function($string){

			return htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
		};

		$muted = false;

		if($Throwable instanceof Silenced){

			$muted = true;
		}

		ob_start();

		// Construct XML

		echo '<?xml version="1.0" encoding="UTF-8"?>';

		?>
		<exception>

		<thrown timestamp="<?= $timestamp; ?>"><?= date(
				'd-m-Y H:i:s',
				$timestamp
			); ?></thrown>

		<class><?= get_class($Throwable); ?></class>
		<origin line="<?= $Throwable->getLine(); ?>"><?= $html_encode(
				$Throwable->getFile()
			); ?></origin>
		<message><?= $html_encode($Throwable->getMessage()); ?></message>

		<trace muted="<?= ($muted ? 'true' : 'false') ?>">

			<?php
			foreach($trace as $trace_item){

				if(strpos($trace_item['function'], '{closure}') !== false){

					$trace_item['function'] = '{closure}';
				}
				?>

				<call>
					<function><?= $html_encode(
							(isset($trace_item['class']) ?
								$trace_item['class'] : '') .
							(isset($trace_item['type']) ? $trace_item['type'] :
								'') .
							(isset($trace_item['function']) ?
								$trace_item['function'] : '')
						) ?></function>
					<file line="<?= (isset($trace_item['line']) ?
						$trace_item['line'] : ''); ?>">
						<?= (isset($trace_item['file']) ?
							$html_encode($trace_item['file']) : ''); ?>
					</file>
					<arguments>

						<? foreach((array) $trace_item['args'] as $argument){

							$silent = false;

							if($muted){

								$silent = true;
							}
							elseif($argument instanceof Silenced){

								$silent = true;
							}
							?>

							<argument type="<?= gettype($argument); ?>"
							          silent="<?= ($silent ? 'true' :
								          'false') ?>">

								<?php

								if($silent){

									if(is_object($argument)){

										echo $iconv(get_class($argument));
									}
									elseif(is_array($argument)){

										echo $iconv(
											'array[' .
											count($argument) . ']'
										);
									}
									elseif(is_string($argument)){

										echo $iconv(
											'string[' .
											strlen($argument) . ']'
										);
									}
									elseif(is_bool($argument)){

										echo $iconv(
											$argument ?
												'true' : 'false'
										);
									}
									elseif(is_null($argument)){

										echo $iconv('null');
									}
								}
								else{

									echo '<![CDATA[' . $iconv(
											print_r(
												$argument,
												true
											)
										) . ']]>';
								}
								?>

							</argument>

						<?php } ?>

					</arguments>
				</call>

			<?php } ?>

		</trace>

		<get>

			<?php foreach((array) $_GET as $name => $value){ ?>

				<value name="<?= $html_encode($name); ?>">
					<?= '<![CDATA[' . $iconv(print_r($value, true)) . ']]>'; ?>
				</value>

			<?php } ?>

		</get>

		<post>

			<?php foreach((array) $_POST as $name => $value){ ?>

				<value name="<?= $html_encode($name); ?>">
					<?= '<![CDATA[' . $iconv(print_r($value, true)) . ']]>'; ?>
				</value>

			<?php } ?>

		</post>

		<?php if($Throwable instanceof ExternalException){ ?>

			<error>
				<?= $html_encode($Throwable->getExternalData()); ?>
			</error>

		<?php } ?>

		<server>

			<?php foreach((array) $_SERVER as $name => $value){ ?>

				<value name="<?= $html_encode($name); ?>">
					<?= '<![CDATA[' . $iconv(print_r($value, true)) . ']]>'; ?>
				</value>

			<?php } ?>

		</server>

		</exception><?php

		return ob_get_clean();
	}
}