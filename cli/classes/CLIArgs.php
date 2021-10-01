<?php

    /**
     * Author: Igor Ilić <github@igorilic.net>
     * Date: 2021-08-22
     * Project: Good Food Tracker - API
     */

    /**
     * Class CLIArgs
     */
    class CLIArgs
    {
        public const DRIVER   = 'driver';
        public const HOST     = 'host';
        public const PORT     = 'port';
        public const USERNAME = 'username';
        public const PASSWORD = 'password';
        public const DATABASE = 'database';
        public const FOLDER   = 'folder';

        /**
         * Method used for returning a list of constant variables
         *
         * @return array Returns a list of constant variables
         */
        public static function getConstants(): array
        {
            $oClass = new ReflectionClass(__CLASS__);
            return $oClass->getConstants();
        }
    }
