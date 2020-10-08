SET @@session.sql_mode = '';

REPLACE INTO `oxuser` (`OXID`, `OXACTIVE`, `OXRIGHTS`, `OXSHOPID`, `OXUSERNAME`, `OXPASSWORD`, `OXPASSSALT`, `OXCUSTNR`, `OXUSTID`, `OXUSTIDSTATUS`, `OXCOMPANY`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXADDINFO`, `OXCITY`, `OXCOUNTRYID`, `OXSTATEID`, `OXZIP`, `OXFON`, `OXFAX`, `OXSAL`, `OXBONI`, `OXCREATE`, `OXREGISTER`, `OXPRIVFON`, `OXMOBFON`, `OXBIRTHDATE`, `OXURL`, `OXLDAPKEY`, `OXWRONGLOGINS`, `OXUPDATEKEY`, `OXUPDATEEXP`, `OXPOINTS`) VALUES
('e7af1c3b786fd02906ccd75698f4e6b9', 1, 'user', 1, 'user@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 8, '', 1, '', 'User', 'User', 'Street', '13', '', 'City', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '1984-12-22', '', '', 0, '', 0, 0),
('otheruser', 1, 'user', 1, 'otheruser@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 8, '', 1, '', 'Marc', 'Muster', 'Hauptstr.', '13', '', 'Freiburg', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '1984-12-22', '', '', 0, '', 0, 0),
('user_2', 1, 'user', 2, 'user@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 8, '', 1, '', 'Marc', 'Muster', 'Hauptstr.', '13', '', 'Freiburg', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '1984-12-22', '', '', 0, '', 0, 0);

REPLACE INTO `oxuserbaskets` (`OXID`, `OXUSERID`, `OXTITLE`, `OXPUBLIC`) VALUES
('basket_user', 'e7af1c3b786fd02906ccd75698f4e6b9', 'savedbasket', true),
('basket_user_2', 'user_2', 'savedbasket', true),
('basket_otheruser', 'otheruser', 'savedbasket', true),
('basket_otheruser_2', 'otheruser', 'savedbasket', true);

REPLACE INTO `oxaddress` (`OXID`, `OXUSERID`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXCITY`, `OXCOUNTRY`, `OXCOUNTRYID`, `OXZIP`, `OXSAL`, `OXTIMESTAMP`) VALUES
('address_user', 'e7af1c3b786fd02906ccd75698f4e6b9', 'User Del', 'User Del', 'Street Del', '2', 'City Del', 'Germany', 'a7c40f631fc920687.20179984', '790982', 'MR', '2020-07-14 14:12:48'),
('address_otheruser', 'otheruser', 'Marc2', 'Muster2', 'Hauptstr2', '2', 'Freiburg2', 'Germany2', 'a7c40f631fc920687.20179984', '790982', 'MR', '2020-07-14 14:12:48'),
('address_user_2', 'user_2', 'Marc2', 'Muster2', 'Hauptstr2', '2', 'Freiburg2', 'Germany2', 'a7c40f631fc920687.20179984', '790982', 'MR', '2020-07-14 14:12:48');

REPLACE INTO `oxshops` (`OXID`, `OXACTIVE`, `OXNAME`, `OXEDITION`, `OXVERSION`, `OXORDEREMAIL`, `OXINFOEMAIL`) VALUES
(2, 1, 'Second Shop', 'EE', '6.0.0', 'reply@myoxideshop.com', 'info@myoxideshop.com');

REPLACE INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`, `OXMODULE`)
SELECT MD5(RAND()), 2, oxvarname, oxvartype, oxvarvalue, oxmodule FROM oxconfig
WHERE oxshopid = '1'
  AND oxvarname IN ( 'aLanguages');
