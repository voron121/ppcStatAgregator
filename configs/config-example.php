<?php
/**
 * Config example file for deploing service.
 */

/**
 * Data for connect to server with users databases
 */
define("DB_HOST", "");
define("DB_NAME", "");
define("DB_USER", "");
define("DB_PASSWORD", "");

/**
 * Data for connect to server with admin database
 */
define("ADMIN_DB_HOST", "");
define("ADMIN_DB_NAME", "");
define("ADMIN_DB_USER", "");
define("ADMIN_DB_PASSWORD", "");

/**
 * Data for connect to auth server
 */
define("AUTH_DB_HOST", "");
define("AUTH_DB_NAME", "");
define("AUTH_DB_USER", "");
define("AUTH_DB_PASSWORD", "");

/**
 * Data for connect to log server
 */
define("LOG_DB_HOST", "");
define("LOG_DB_USER", "");
define("LOG_DB_PASSWORD", "");

/**
 * Names logs tables (for users, apis and robots logs)
 */
define("USERS_LOG_DB_NAME", "users_actions_logs");
define("ROBOTS_LOG_DB_NAME", "robots_logs");
define("API_LOG_DB_NAME", "api_logs");

/**
 * Data for connect to DB with cache (DB with reports data)
 */
define("CACHE_DB_HOST", "");
define("CACHE_DB_NAME", "");
define("CACHE_DB_USER", "");
define("CACHE_DB_PASSWORD", "");

/**
 * Default path to log file
 */
define("LOG_FILE", __DIR__ . "/../logs");

/**
 * Default new user group id
 */
define("DEFAULT_NEW_USER_GROUP", 1);

/**
 * Default new user status
 */
define("DEFAULT_NEW_USER_STATUS", "Aprove");

/**
 * Default admin id (hard code for testings)
 */
define("DEFAULT_USER_ID", 1);

/**
 * Limit items on page
 */
define("ITEMS_ON_PAGE_LIMIT", 8);

/**
 * Mail SMTP
 */
define("SMTP_HOST", "");
define("SMTP_PORT", "");
define("SMTP_USER", "");
define("SMTP_PASSWORD", "");

/**
 * Filters config path
 */
define("FILTER_CONFIG_PATH", __DIR__."/filters/");

/**
 * Mail's
 */
define("TECH_SUPPORT_EMAIL", "");
define("ADMIN_EMAIL", "");