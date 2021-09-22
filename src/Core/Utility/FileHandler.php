<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-15
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Utility;


	use Gac\GoodFoodTracker\Core\Exceptions\InvalidFileTypeException;
	use Gac\GoodFoodTracker\Core\Exceptions\UploadFileNotSavedException;
	use Gac\GoodFoodTracker\Core\Exceptions\UploadFileTooLargeException;

	class FileHandler
	{
		const ALLOWED_TYPES_IMAGES    = [ 'jpg', 'jpeg', 'png', 'bmp' ];
		const ALLOWED_TYPES_DOCUMENTS = [ 'pdf', 'doc', 'docx', 'xls', 'xlsx' ];
		const ALLOWED_TYPES_ALL       = [ self::ALLOWED_TYPES_IMAGES, self::ALLOWED_TYPES_DOCUMENTS ];

		private static int $maxUploadSize = ( 1024 * 1024 * 5 ); //5 MB

		private static array $phpFileUploadErrors = [
			0 => 'There is no error, the file uploaded with success',
			1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
			2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
			3 => 'The uploaded file was only partially uploaded',
			4 => 'No file was uploaded',
			6 => 'Missing a temporary folder',
			7 => 'Failed to write file to disk.',
			8 => 'A PHP extension stopped the file upload.',
		];

		/**
		 * @throws UploadFileTooLargeException
		 * @throws InvalidFileTypeException
		 * @throws UploadFileNotSavedException
		 */
		public static function upload(
			array $file,
			string $savePath,
			array $allowedFiledTypes = self::ALLOWED_TYPES_ALL,
		) : ?string {
			if ( $file["error"] !== 0 ) {
				throw new UploadFileNotSavedException(self::$phpFileUploadErrors[$file["error"]]);
			}

			if ( !in_array(pathinfo($file["name"], PATHINFO_EXTENSION), $allowedFiledTypes) ) {
				throw new InvalidFileTypeException();
			}

			if ( $file['size'] > self::$maxUploadSize ) {
				throw new UploadFileTooLargeException();
			}

			if ( !is_dir($savePath) ) {
				mkdir($savePath, 0755, true);
			}

			$uploadPath = $savePath . basename($file["name"]);

			if ( !move_uploaded_file($file["tmp_name"], $uploadPath) ) {
				$errorMessage = $file["error"] !== 0 ? self::$phpFileUploadErrors[$file["error"]] : "File upload failed";
				throw new UploadFileNotSavedException($errorMessage);
			}

			return $uploadPath;
		}

		public static function set_max_upload_size(int $maxUploadSize) {
			self::$maxUploadSize = $maxUploadSize;
		}
	}