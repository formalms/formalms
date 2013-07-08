ALTER TABLE `core_user` ADD `facebook_id` VARCHAR( 255 ) NULL ,
ADD `twitter_id` VARCHAR( 255 ) NULL ,
ADD `linkedin_id` VARCHAR( 255 ) NULL ,
ADD `google_id` VARCHAR( 255 ) NULL ;


ALTER TABLE `core_user_temp` ADD `facebook_id` VARCHAR( 255 ) NULL ,
ADD `twitter_id` VARCHAR( 255 ) NULL ,
ADD `linkedin_id` VARCHAR( 255 ) NULL ,
ADD `google_id` VARCHAR( 255 ) NULL ;

ALTER TABLE  `core_user` DROP  `photo`;
