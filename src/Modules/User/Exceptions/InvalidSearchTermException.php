<?php

    /**
     * Author: Igor Ilić <github@igorilic.net>
     * Date: 2021-09-15
     * Project: Good Food Tracker - API
     */

    namespace Gac\GoodFoodTracker\Modules\User\Exceptions;

    use Exception;
    use JetBrains\PhpStorm\Pure;

    class InvalidSearchTermException extends Exception
    {
        /**
         * InvalidSearchTermException constructor.
         */
        #[Pure] public function __construct(?string $message = null, ?int $code = 400)
        {
            parent::__construct($message, $code);
        }
    }
