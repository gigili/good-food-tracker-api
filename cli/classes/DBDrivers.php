<?php

    /**
     * Author: Igor Ilić <github@igorilic.net>
     * Date: 2021-08-22
     * Project: Good Food Tracker - API
     */

    /**
     * Class DBDrivers
     */
    class DBDrivers
    {
        public const PGSQL = 'PostgresDriver';
        public const MYSQL = 'MySQLDriver';
        public const MSSQL = 'MSSQLDriver';

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
