

DELETE FROM `core_menu_under` WHERE `module_name` = "publicadminrules";
DELETE FROM `core_menu_under` WHERE `module_name` = "publicadminmanager";


DELETE FROM `learning_module` WHERE `module_name` = "pusermanagement";
DELETE FROM `learning_module` WHERE `module_name` = "pcourse";
DELETE FROM `learning_module` WHERE `module_name` = "public_report_admin";
DELETE FROM `learning_module` WHERE `module_name` = "public_newsletter_admin";
DELETE FROM `learning_module` WHERE `module_name` = "pcertificate";


DELETE FROM `core_role` WHERE `roleId` = "/framework/admin/publicadminmanager/mod";
DELETE FROM `core_role` WHERE `roleId` = "/framework/admin/publicadminmanager/view";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pusermanagement/view";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pusermanagement/add";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pusermanagement/mod";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pusermanagement/del";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pusermanagement/approve_waiting_user";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/view";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/add";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/mod";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/del";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/moderate";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/subscribe";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_report_admin/view";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_newsletter_admin/view";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_subscribe_admin/approve_waiting_user";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_subscribe_admin/createuser_org_chart";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_subscribe_admin/deluser_org_chart";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_subscribe_admin/edituser_org_chart";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_subscribe_admin/view_org_chart";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcertificate/view";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcertificate/mod";

DELETE FROM `core_group` WHERE `groupid` = "/framework/level/publicadmin";
