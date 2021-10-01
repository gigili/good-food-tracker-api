<?php

    /**
     * Author: Igor Ilić <github@igorilic.net>
     * Date: 2021-08-10
     * Project: Good Food Tracker - API
     */

    namespace Gac\GoodFoodTracker\Modules\Auth\Exceptions;

    use Exception;
    use JetBrains\PhpStorm\Pure;

    class EmailTakenException extends Exception
    {
        /**
         * EmailTakenException constructor.
         */
        #[Pure] public function __construct()
        {
            parent::__construct("Email already in use", 409);
        }
    }
