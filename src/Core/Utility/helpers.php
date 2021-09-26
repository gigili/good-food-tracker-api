<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: API
	 */

	use Gac\GoodFoodTracker\Core\Utility\Logger;
	use JetBrains\PhpStorm\NoReturn;
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;

	if ( !function_exists('dump') ) {
		/**
		 * Method used for printing out the contents of an object or an array on screen
		 *
		 * @param string|array|object $data Array or an object to be printed out on screen
		 * @param bool $asJSON Boolean indicating if the output should be in json format
		 */
		function dump(string|array|object $data, bool $asJSON = true) : void {
			if ( $asJSON === true ) {
				echo json_encode($data);
			} else {
				echo '<pre>';
				print_r($data);
				echo '</pre>';
			}
		}
	}

	if ( !function_exists('dd') ) {
		/**
		 * Method used as a wrapper around the dump method us for printing out the contents
		 * of an object or an array on screen and stopping execution of the code after it
		 *
		 * @param string|array|object $data Array or an object to be printed out on screen
		 * @param bool $asJSON Boolean indicating if the output should be in json format
		 */
		#[NoReturn] function dd(string|array|object $data, bool $asJSON = true) : void {
			dump($data, $asJSON);
			die(1);
		}
	}

	if ( !function_exists('send_email') ) {
		/**
		 * Method used for sending emails from the application
		 *
		 * @param string $to Email or comma separated email list of recipients
		 * @param string $subject Subject of the email
		 * @param string $body Body of the email being sent (supports HTML)
		 * @param string|null $altBody Text only version of the email body
		 * @param array $attachments Array list of files with full paths to them to be attached to the email
		 * @param string|null $from Name of the sender (usually name of the app)
		 * @param string|array|null $emailTemplate If sent as a string, it will look for an email template file in the assets/emails folder. As an array it expects a propery named file for the email template file and args (key => value list) for any email body values to be replaced
		 * @param bool $debug Indicates if the debug will be enabled when sending email to see all the information
		 *
		 * @return bool Returns true or false to indicated if the email was sent successfully or not
		 */
		function send_email(
			string            $to,
			string            $subject,
			string            $body,
			string|null       $altBody = NULL,
			array             $attachments = [],
			string|null       $from = NULL,
			string|array|null $emailTemplate = NULL,
			bool              $debug = false
		) : bool {
			$mail = new PHPMailer(true);

			try {
				//Server settings
				if ( $debug ) $mail->SMTPDebug = SMTP::DEBUG_SERVER;

				$mail->isSMTP();
				$mail->Host = $_ENV['EMAIL_HOST'];
				$mail->SMTPAuth = true;

				if ( isset($_ENV["EMAIL_USERNAME"]) ) {
					$mail->Username = $_ENV['EMAIL_USERNAME'];
				}

				if ( isset($_ENV["EMAIL_PASSWORD"]) ) {
					$mail->Password = $_ENV['EMAIL_PASSWORD'];
				}

				$mail->SMTPSecure = isset($_ENV["EMAIL_USERNAME"]) ? PHPMailer::ENCRYPTION_STARTTLS : "";
				$mail->Port = $_ENV['EMAIL_PORT'];

				//Recipients
				$mail->setFrom($_ENV["EMAIL_SENDER"], $from ?? 'Good Food Tracker');

				if ( str_contains($to, ',') ) {
					$recipients = explode(',', $to);
					foreach ( $recipients as $recipient ) {
						$mail->addAddress($recipient);
					}
				} else {
					$mail->addAddress($to);
				}

				//Attachments
				if ( count($attachments) > 0 ) {
					foreach ( $attachments as $attachment ) {
						$mail->addAttachment($attachment);
					}
				}

				if ( !is_null($emailTemplate) ) {
					$templateFile = is_array($emailTemplate) ? $emailTemplate['file'] ?? 'default' : $emailTemplate;
					$args = is_array($emailTemplate) ? $emailTemplate['args'] ?? [] : [];

					$emailTemplateFile = $_SERVER['DOCUMENT_ROOT'] . "/assets/emails/$templateFile.html";
					if ( file_exists($emailTemplateFile) ) {
						$emailBody = file_get_contents($emailTemplateFile);
						$body = str_replace('{{emailBody}}', $body, $emailBody);

						foreach ( $args as $arg => $value ) {
							/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
							$body = preg_replace("/{{{$arg}}}/", $value, $body);
						}
					}

					$altBody = is_array($emailTemplate) ? $emailTemplate['emailPreview'] ?? $altBody : $altBody;
				}

				//Content
				$mail->isHTML(true);
				$mail->Subject = $subject;
				$mail->Body = $body;

				if ( !is_null($altBody) ) $mail->AltBody = $altBody;
				if ( $mail->send() ) return true;

				return false;
			} catch ( Exception $ex ) {
				Logger::error("Failed to send email: {$ex->getMessage()}");
				return false;
			}
		}
	}
