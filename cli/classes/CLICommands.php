<?php

    /**
     * Author: Igor Ilić <github@igorilic.net>
     * Date: 2021-08-22
     * Project: Good Food Tracker - API
     */

    /**
     * Class CLICommands
     */
    class CLICommands
    {
        public const INIT   = 'init';
        public const CREATE = 'create';
        public const UP     = 'up';
        public const DOWN   = 'down';
        public const HELP   = "help";

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
