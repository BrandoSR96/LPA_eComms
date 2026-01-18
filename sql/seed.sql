-- sql/seed.sql
USE lpaecomms;

-- Usuario admin (reemplaza el hash con password_hash('admin123', PASSWORD_BCRYPT))
INSERT INTO Ipausers (Ipauser_username, Ipauser_password, Ipauser_firstname, Ipauser_lastname, Ipauser_group, Ipainv_status)
VALUES ('admin', '$2y$10$REEMPLAZA_CON_TU_HASH', 'Admin', 'User', 'admin', 'activo');

-- Cliente demo
INSERT INTO Ipaclients (Ipaclient_firstname, Ipaclient_lastname, Ipaclient_address, Ipaclient_phone, Ipaclient_status)
VALUES ('Cliente', 'Demo', 'Av. Siempre Viva 123', '999999999', 'activo');

-- Stock demo
INSERT INTO ipa_stock (Ipastock_name, Ipastock_desc, Ipastock_onhand, Ipastock_price, Ipastock_status)
VALUES ('Producto A', 'Descripción A', 50, 10.50, 'activo');