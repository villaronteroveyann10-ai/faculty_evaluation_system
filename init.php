<?php
/**
 * includes/init.php
 * Loaded at the top of every page: starts the session,
 * connects to the database and loads the helper functions.
 */
session_start();
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/functions.php';
