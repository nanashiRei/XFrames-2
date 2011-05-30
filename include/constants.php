<?php

/** Custom Errors **/

/** CONFIG ERRORS **/
define('ERR_CONFIG_INCOMPLETE',1,true);
define('ERR_CONFIG_UNDEFINED_KEY',10,true);
define('ERR_CONFIG_UNDEFINED_SECTION',11,true);

/** PAGE ERRORS **/
define('ERR_PAGE_EMPTY_REQUEST',400,true);
define('ERR_PAGE_NOT_FOUND',404,true);

/** USER ERRORS **/
define('ERR_USER_ALREADY_EXISTS',500,true);
define('ERR_USER_BAD_USERNAME',501,true);
define('ERR_USER_BAD_PASSWORD',502,true);

/** INSTALL ERRORS **/
define('ERR_INSTALL_EMPTY_QUERY',1000,true);

?>