-- removing news function
DROP TABLE IF EXISTS learning_news;
DELETE FROM core_menu WHERE core_menu.idMenu = 32;