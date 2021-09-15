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

			if ( !in_array(pathinfo($file['name'], PATHINFO_EXTENSION), $allowedFiledTypes) ) {
				throw new InvalidFileTypeException();
			}

			if ( !is_dir(pathinfo($savePath, PATHINFO_DIRNAME)) ) {
				mkdir(pathinfo($savePath, PATHINFO_DIRNAME), 0755, true);
			}

			$uploadPath = "$savePath{$file['name']}";

			$bytes = file_put_contents($uploadPath, $file['content']);

			if ( $bytes === false ) throw new UploadFileNotSavedException();

			if ( $bytes > self::$maxUploadSize ) {
				if ( file_exists($savePath) ) {
					unlink($savePath);
				}
				throw new UploadFileTooLargeException();
			}

			return $uploadPath;
		}

		public static function set_max_upload_size(int $maxUploadSize) {
			self::$maxUploadSize = $maxUploadSize;
		}
	}