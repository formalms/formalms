DELETE rm
FROM core_role_members rm
LEFT JOIN core_role r ON rm.idst = r.idst
WHERE r.idst IS NULL;

ALTER TABLE core_role
ADD COLUMN idPlugin INT(10) NULL,
ADD CONSTRAINT FOREIGN KEY (idPlugin) REFERENCES core_plugin(plugin_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE core_role_members
ADD CONSTRAINT FOREIGN KEY (idst) REFERENCES core_role(idst) ON DELETE CASCADE ON UPDATE CASCADE;
