<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-15
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Utility;

	use Gac\GoodFoodTracker\Core\Exceptions\InvalidFileTypeException;
	use Gac\GoodFoodTracker\Core\Exceptions\UploadFileNotFoundException;
	use Gac\GoodFoodTracker\Core\Exceptions\UploadFileNotSavedException;
	use Gac\GoodFoodTracker\Core\Exceptions\UploadFileTooLargeException;
	use Gac\Routing\Routes;

	class FileHandler
	{
		public const ALLOWED_TYPES_IMAGES    = [ 'jpg', 'jpeg', 'png', 'bmp' ];
		public const ALLOWED_TYPES_DOCUMENTS = [ 'pdf', 'doc', 'docx', 'xls', 'xlsx' ];
		public const ALLOWED_TYPES_ALL       = [ self::ALLOWED_TYPES_IMAGES, self::ALLOWED_TYPES_DOCUMENTS ];

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
		 * @throws UploadFileNotFoundException
		 */
		public static function upload(
			array  $file,
			string $savePath,
			array  $allowedFiledTypes = self::ALLOWED_TYPES_ALL,
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
			if ( $_SERVER["REQUEST_METHOD"] === Routes::POST ) {
				if ( !move_uploaded_file($file["tmp_name"], $uploadPath) ) {
					throw new UploadFileNotSavedException('File upload failed');
				}
			} elseif ( in_array($_SERVER["REQUEST_METHOD"], [ Routes::PATCH, Routes::PUT ]) ) {
				$content = file_get_contents($file["tmp_name"]);

				if ( $content === false ) {
					throw  new UploadFileNotFoundException("File upload failed");
				}

				$handle = fopen($uploadPath, "w");

				if ( $handle === false ) {
					throw new UploadFileNotSavedException("Unable to write file to disk");
				}

				fwrite($handle, $content);
				fclose($handle);
			}

			return $uploadPath;
		}

		public static function set_max_upload_size(int $maxUploadSize) {
			self::$maxUploadSize = $maxUploadSize;
		}
	}
