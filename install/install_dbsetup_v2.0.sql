/* XFrames 2.0 Navigation Base */
CREATE  TABLE  `{DATABASE}`.`{TABLEPREFIX}navigation_categories` (  `id` int( 5  )  NOT  NULL  AUTO_INCREMENT ,
`item` varchar( 120  )  NOT  NULL ,
`link` varchar( 300  )  NOT  NULL ,
`requiredFlag` varchar( 250  )  NOT  NULL ,
`hasSub` tinyint( 1  )  NOT  NULL DEFAULT  '0',
`order` int( 2  )  NOT  NULL ,
PRIMARY  KEY (  `id`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8;

/* XFrames 2.0 Navigation Subitems */
CREATE  TABLE  `{DATABASE}`.`{TABLEPREFIX}navigation_subitems` (  `id` int( 5  )  NOT  NULL  AUTO_INCREMENT ,
`catId` int( 5  )  NOT  NULL ,
`item` varchar( 120  )  NOT  NULL ,
`link` varchar( 500  )  NOT  NULL ,
`requiredFlag` varchar( 250  )  NOT  NULL ,
`order` int( 2  )  NOT  NULL ,
PRIMARY  KEY (  `id`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8;

/* XFrames 2.0 Accounts */
CREATE  TABLE  `{DATABASE}`.`{TABLEPREFIX}accounts` (  `id` int( 11  )  NOT  NULL  AUTO_INCREMENT ,
`username` varchar( 20  )  NOT  NULL ,
`password` varchar( 36  )  NOT  NULL ,
`key` varchar( 128  )  NOT  NULL ,
PRIMARY  KEY (  `id`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8;

/* XFrames 2.0 Account Flags */
CREATE  TABLE  `{DATABASE}`.`{TABLEPREFIX}accounts_flags` (  `id` int( 5  )  NOT  NULL ,
`key` varchar( 100  )  NOT  NULL ,
`type` enum(  'value',  'state',  'enum'  )  NOT  NULL DEFAULT  'state',
`data` text NOT  NULL ,
`value` text NOT  NULL ,
PRIMARY  KEY (  `id` ,  `key`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8;