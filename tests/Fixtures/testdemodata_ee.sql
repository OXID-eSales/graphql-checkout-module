SET @@session.sql_mode = '';

REPLACE INTO `oxpricealarm` (`OXID`, `OXSHOPID`, `OXUSERID`, `OXEMAIL`, `OXARTID`, `OXPRICE`, `OXCURRENCY`, `OXLANG`, `OXINSERT`, `OXSENDED`, `OXTIMESTAMP`) VALUES
('_test_wished_price_8_',	2,	'123ad3b5380202966df6ff128e9eecaq',	'user@oxid-esales.com',	'_test_product_5_',	10,	'EUR',	1,	'2020-05-26 00:00:00',	'0000-00-00 00:00:00',	'2020-05-26 11:48:20');

UPDATE `oxarticles` SET `OXMAPID` = 3333, `OXVPE` = 1 WHERE OXID = '_test_product_wished_price_3_';
UPDATE `oxarticles` SET `OXMAPID` = 4444, `OXVPE` = 1 WHERE OXID = '_test_product_wished_price_4_';
UPDATE `oxarticles` SET `OXMAPID` = 1234, `OXVPE` = 1 WHERE OXID = '_test_product_for_rating_5_';
UPDATE `oxarticles` SET `OXMAPID` = 2345, `OXVPE` = 1 WHERE OXID = '_test_product_for_rating_6_';
UPDATE `oxarticles` SET `OXMAPID` = 4567, `OXVPE` = 1 WHERE OXID = '_test_product_for_rating_avg';
UPDATE `oxarticles` SET `OXMAPID` = 1123, `OXVPE` = 1 WHERE OXID='_test_product_for_wish_list';
UPDATE `oxarticles` SET `OXMAPID` = 2123, `OXVPE` = 1 WHERE OXID='_test_product_for_basket';
REPLACE INTO `oxarticles` (`OXID`, `OXMAPID`, `OXSHOPID`, `OXPARENTID`, `OXACTIVE`, `OXHIDDEN`, `OXACTIVEFROM`, `OXACTIVETO`, `OXARTNUM`, `OXEAN`, `OXDISTEAN`, `OXMPN`, `OXTITLE`, `OXSHORTDESC`, `OXPRICE`, `OXBLFIXEDPRICE`, `OXPRICEA`, `OXPRICEB`, `OXPRICEC`, `OXBPRICE`, `OXTPRICE`, `OXUNITNAME`, `OXUNITQUANTITY`, `OXEXTURL`, `OXURLDESC`, `OXURLIMG`, `OXVAT`, `OXTHUMB`, `OXICON`, `OXPIC1`, `OXPIC2`, `OXPIC3`, `OXPIC4`, `OXPIC5`, `OXPIC6`, `OXPIC7`, `OXPIC8`, `OXPIC9`, `OXPIC10`, `OXPIC11`, `OXPIC12`, `OXWEIGHT`, `OXSTOCK`, `OXSTOCKFLAG`, `OXSTOCKTEXT`, `OXNOSTOCKTEXT`, `OXDELIVERY`, `OXINSERT`, `OXTIMESTAMP`, `OXLENGTH`, `OXWIDTH`, `OXHEIGHT`, `OXFILE`, `OXSEARCHKEYS`, `OXTEMPLATE`, `OXQUESTIONEMAIL`, `OXISSEARCH`, `OXISCONFIGURABLE`, `OXVARNAME`, `OXVARSTOCK`, `OXVARCOUNT`, `OXVARSELECT`, `OXVARMINPRICE`, `OXVARMAXPRICE`, `OXVARNAME_1`, `OXVARSELECT_1`, `OXVARNAME_2`, `OXVARSELECT_2`, `OXVARNAME_3`, `OXVARSELECT_3`, `OXTITLE_1`, `OXSHORTDESC_1`, `OXURLDESC_1`, `OXSEARCHKEYS_1`, `OXTITLE_2`, `OXSHORTDESC_2`, `OXURLDESC_2`, `OXSEARCHKEYS_2`, `OXTITLE_3`, `OXSHORTDESC_3`, `OXURLDESC_3`, `OXSEARCHKEYS_3`, `OXBUNDLEID`, `OXFOLDER`, `OXSUBCLASS`, `OXSTOCKTEXT_1`, `OXSTOCKTEXT_2`, `OXSTOCKTEXT_3`, `OXNOSTOCKTEXT_1`, `OXNOSTOCKTEXT_2`, `OXNOSTOCKTEXT_3`, `OXSORT`, `OXSOLDAMOUNT`, `OXNONMATERIAL`, `OXFREESHIPPING`, `OXREMINDACTIVE`, `OXREMINDAMOUNT`, `OXAMITEMID`, `OXAMTASKID`, `OXVENDORID`, `OXMANUFACTURERID`, `OXSKIPDISCOUNTS`, `OXORDERINFO`, `OXPIXIEXPORT`, `OXPIXIEXPORTED`, `OXVPE`, `OXRATING`, `OXRATINGCNT`, `OXMINDELTIME`, `OXMAXDELTIME`, `OXDELTIMEUNIT`, `OXUPDATEPRICE`, `OXUPDATEPRICEA`, `OXUPDATEPRICEB`, `OXUPDATEPRICEC`, `OXUPDATEPRICETIME`, `OXISDOWNLOADABLE`, `OXSHOWCUSTOMAGREEMENT`) VALUES
('_test_product_wp1_',	6666,	1,	'',	1,	0,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	'123',	'',	'',	'',	'Product wp1',	'',	15,	0,	0,	0,	0,	0,	0,	'',	0,	'',	'',	'',	NULL,	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	1,	'',	'',	'0000-00-00',	'2020-05-25',	'2020-05-25 09:25:26',	0,	0,	0,	'',	'',	'',	'',	1,	0,	'',	0,	0,	'',	10,	10,	'',	'',	'',	'',	'',	'',	'Product 5',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'oxarticle',	'',	'',	'',	'',	'',	'',	0,	0,	0,	0,	0,	0,	'',	'0',	'',	'',	0,	'',	0,	'0000-00-00 00:00:00',	1,	0,	0,	0,	0,	'',	0,	0,	0,	0,	'0000-00-00 00:00:00',	0,	1),
('_test_product_wp2_',	7777,	2,	'',	1,	0,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	'213',	'',	'',	'',	'Product wp2',	'',	15,	0,	0,	0,	0,	0,	0,	'',	0,	'',	'',	'',	NULL,	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	1,	'',	'',	'0000-00-00',	'2020-05-25',	'2020-05-25 09:25:26',	0,	0,	0,	'',	'',	'',	'',	1,	0,	'',	0,	0,	'',	10,	10,	'',	'',	'',	'',	'',	'',	'Product 5',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'oxarticle',	'',	'',	'',	'',	'',	'',	0,	0,	0,	0,	0,	0,	'',	'0',	'',	'',	0,	'',	0,	'0000-00-00 00:00:00',	1,	0,	0,	0,	0,	'',	0,	0,	0,	0,	'0000-00-00 00:00:00',	0,	1),
('_test_product_5_',	5555,	2,	'',	1,	0,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	'555',	'',	'',	'',	'Product 5',	'',	15,	0,	0,	0,	0,	0,	0,	'',	0,	'',	'',	'',	NULL,	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	1,	'',	'',	'0000-00-00',	'2020-05-25',	'2020-05-25 09:25:26',	0,	0,	0,	'',	'',	'',	'',	1,	0,	'',	0,	0,	'',	10,	10,	'',	'',	'',	'',	'',	'',	'Product 5',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'oxarticle',	'',	'',	'',	'',	'',	'',	0,	0,	0,	0,	0,	0,	'',	'0',	'',	'',	0,	'',	0,	'0000-00-00 00:00:00',	1,	0,	0,	0,	0,	'',	0,	0,	0,	0,	'0000-00-00 00:00:00',	0,	1);

REPLACE INTO `oxarticles2shop` (`OXSHOPID`, `OXMAPOBJECTID`, `OXTIMESTAMP`) VALUES
(1, 3333, '2020-01-01 00:00:00'),
(1, 4444, '2020-01-01 00:00:00'),
(2, 5555, '2020-01-01 00:00:00'),
(1, 6666, '2020-01-01 00:00:00'),
(2, 7777, '2020-01-01 00:00:00'),
(1, 1234, '2020-01-01 00:00:00'),
(2, 1234, '2020-01-01 00:00:00'),
(1, 2345, '2020-01-01 00:00:00'),
(1, 1123, '2020-01-01 00:00:00'),
(1, 2123, '2020-01-01 00:00:00'),
(1, 4567, '2020-01-01 00:00:00'),
(2, 2123, '2020-01-01 00:00:00');

REPLACE INTO `oxuser` (`OXID`, `OXACTIVE`, `OXRIGHTS`, `OXSHOPID`, `OXUSERNAME`, `OXPASSWORD`, `OXPASSSALT`, `OXCUSTNR`, `OXUSTID`, `OXUSTIDSTATUS`, `OXCOMPANY`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXADDINFO`, `OXCITY`, `OXCOUNTRYID`, `OXSTATEID`, `OXZIP`, `OXFON`, `OXFAX`, `OXSAL`, `OXBONI`, `OXCREATE`, `OXREGISTER`, `OXPRIVFON`, `OXMOBFON`, `OXBIRTHDATE`, `OXURL`, `OXLDAPKEY`, `OXWRONGLOGINS`, `OXUPDATEKEY`, `OXUPDATEEXP`, `OXPOINTS`) VALUES
('245ad3b5380202966df6ff128e9eecaq', 1, 'user', 1, 'otheruser@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 8, '', 1, '', 'Marc', 'Muster', 'Hauptstr.', '13', '', 'Freiburg', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '0000-00-00', '', '', 0, '', 0, 0),
('123ad3b5380202966df6ff128e9eecaq', 1, 'user', 2, 'user@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 8, '', 1, '', 'Marc', 'Muster', 'Hauptstr.', '13', '', 'Freiburg', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '1984-12-22', '', '', 0, '', 0, 0),
('309db395b6c85c3881fcb9b437a73dd6', 1, 'user', 2, 'existinguser@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 8, '', 1, '', 'Marc', 'Muster', 'Hauptstr.', '13', '', 'Freiburg', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '0000-00-00', '', '', 0, '', 0, 0);

REPLACE INTO `oxratings` (`OXID`, `OXSHOPID`, `OXUSERID`, `OXTYPE`, `OXOBJECTID`, `OXRATING`) VALUES
('test_rating_8_', 2, '123ad3b5380202966df6ff128e9eecaq', 'oxarticle', '_test_product_5_', 4);
INSERT INTO oxconfig (OXID, OXSHOPID, OXVARNAME, OXVARTYPE, OXVARVALUE) SELECT
MD5(RAND()), 2, OXVARNAME, OXVARTYPE, OXVARVALUE from oxconfig;

UPDATE `oxshops` SET `OXORDEREMAIL`='reply@myoxideshop.com' WHERE `OXID`='2';

REPLACE INTO `oxaddress` (`OXID`, `OXUSERID`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXCITY`, `OXCOUNTRY`, `OXCOUNTRYID`, `OXZIP`, `OXSAL`, `OXTIMESTAMP`) VALUES
('test_delivery_address_shop_2', '123ad3b5380202966df6ff128e9eecaq', 'Marc2', 'Muster2', 'Hauptstr2', '2', 'Freiburg2', 'Germany2', 'a7c40f631fc920687.20179984', '790982', 'MR', '2020-07-14 14:12:48');

INSERT INTO `oxuserbaskets` (`OXID`, `OXUSERID`, `OXTITLE`, `OXPUBLIC`) VALUES
('_test_shop2_basket_public', '123ad3b5380202966df6ff128e9eecaq', 'buy_these', true);

INSERT INTO `oxuserbasketitems` (`OXID`, `OXBASKETID`, `OXARTID`, `OXAMOUNT`, `OXSELLIST`, `OXPERSPARAM`) VALUES
('_test_shop2_basket_item_1', '_test_shop2_basket_public', '_test_product_for_basket', 1, 'N;', '');

REPLACE INTO `oxorder` (`OXID`, `OXSHOPID`, `OXUSERID`, `OXORDERDATE`, `OXORDERNR`, `OXBILLCOMPANY`, `OXBILLEMAIL`, `OXBILLFNAME`,
 `OXBILLLNAME`, `OXBILLSTREET`, `OXBILLSTREETNR`, `OXBILLADDINFO`, `OXBILLUSTID`, `OXBILLUSTIDSTATUS`, `OXBILLCITY`,
  `OXBILLCOUNTRYID`, `OXBILLSTATEID`, `OXBILLZIP`, `OXBILLFON`, `OXBILLFAX`, `OXBILLSAL`, `OXDELCOMPANY`, `OXDELFNAME`,
  `OXDELLNAME`, `OXDELSTREET`, `OXDELSTREETNR`, `OXDELADDINFO`, `OXDELCITY`, `OXDELCOUNTRYID`, `OXDELSTATEID`, `OXDELZIP`,
   `OXDELFON`, `OXDELFAX`, `OXDELSAL`, `OXPAYMENTID`, `OXPAYMENTTYPE`, `OXTOTALNETSUM`, `OXTOTALBRUTSUM`, `OXTOTALORDERSUM`,
   `OXARTVAT1`, `OXARTVATPRICE1`, `OXARTVAT2`, `OXARTVATPRICE2`, `OXDELCOST`, `OXDELVAT`, `OXPAYCOST`, `OXPAYVAT`, `OXWRAPCOST`,
   `OXWRAPVAT`, `OXGIFTCARDCOST`, `OXGIFTCARDVAT`, `OXCARDID`, `OXCARDTEXT`, `OXDISCOUNT`, `OXEXPORT`, `OXBILLNR`, `OXBILLDATE`,
    `OXTRACKCODE`, `OXSENDDATE`, `OXREMARK`, `OXVOUCHERDISCOUNT`, `OXCURRENCY`, `OXCURRATE`, `OXFOLDER`, `OXTRANSID`, `OXPAYID`,
    `OXXID`, `OXPAID`, `OXSTORNO`, `OXIP`, `OXTRANSSTATUS`, `OXLANG`, `OXINVOICENR`, `OXDELTYPE`, `OXPIXIEXPORT`, `OXTIMESTAMP`,
    `OXISNETTOMODE`) VALUES
('7d090db46a124f48cb7e6836ceef3f66',1,'e7af1c3b786fd02906ccd75698f4e6b9','2011-03-30 10:55:13',1,'bill company','billuser@oxid-esales.com','Marc','Muster','Hauptstr.','13','additional bill info','bill vat id',1,'Freiburg','a7c40f631fc920687.20179984','BW','79098','1234','4567','MR','','','','','','','','','','','','','','7d011a153655ef215558cddd43dc65a8','oxidinvoice',1639.15,2108.39,1950.59,19,311.44,0,0,0,19,0,0,0,0,0,0,'','',157.8,0,'7661','2020-03-31','track_me','2020-08-24 11:11:11','Hier können Sie uns noch etwas mitteilen.',0,'EUR',1,'ORDERFOLDER_NEW','','','','2020-04-01 12:12:12',0,'','OK',0,661,'oxidstandard',0,'2020-08-21 09:39:46',0),
('8c69bc776dd339a83d863c4f64693bb6',1,'e7af1c3b786fd02906ccd75698f4e6b9','2019-08-21 11:41:41',2,'bill company','billuser@oxid-esales.com','Marc','Muster','Hauptstr.','13','additional bill info','bill vat id',1,'Freiburg','a7c40f631fc920687.20179984','BW','79098','1234','4567','MR','','','','','','','','','','','','','','5b4b2226735704859055607e98a257e7','oxidcashondel',25.13,29.9,46.75,19,4.77,0,0,3.9,19,7.5,19,2.95,18.951612903226,2.5,19,'81b40cf076351c229.14252649','asdfasdf',0,0,'7662','2020-08-23','tick','2020-08-24 11:11:12','',0,'EUR',1,'ORDERFOLDER_NEW','','','','0000-00-00 00:00:00',0,'','OK',1,662,'oxidstandard',0,'2020-08-21 09:41:41',0),
('0c99bad495d00254a936ccee2391f763',1,'e7af1c3b786fd02906ccd75698f4e6b9','2020-04-22 14:07:12',3,'bill company','billuser@oxid-esales.com','Marc','Muster','Hauptstr.','13','additional bill info','bill vat id',1,'Freiburg','a7c40f631fc920687.20179984','BW','79098','1234','4567','MR','del company','Marcia','Pattern','Nebenstraße','123','del addinfo','Freiburg','a7c40f631fc920687.20179984','HH','79106','04012345678','04012345679','MRS','c3260a603ed4e2d3b01981cbc05e8dfd','oxidinvoice',226.05,269,269,19,42.95,0,0,0,19,0,0,0,0,0,19,'','',0,0,'7663','2020-08-24','trick','2020-08-24 11:11:13','Hej, greetings to graphQL! ',0,'EUR',1,'ORDERFOLDER_NEW','','','','0000-00-00 00:00:00',0,'','OK',1,663,'oxidstandard',0,'2020-08-21 12:07:12',0),
('0c99bad495d00254a936ccee2391f637',2,'e7af1c3b786fd02906ccd75698f4e6b9','2020-04-22 14:07:12',3,'bill company','billuser@oxid-esales.com','Marc','Muster','Hauptstr.','13','additional bill info','bill vat id',1,'Freiburg','a7c40f631fc920687.20179984','BW','79098','1234','4567','MR','del company','Marcia','Pattern','Nebenstraße','123','del addinfo','Freiburg','a7c40f631fc920687.20179984','HH','79106','04012345678','04012345679','MRS','c3260a603ed4e2d3b01981cbc05e8dfd','oxidinvoice',226.05,269,269,19,42.95,0,0,0,19,0,0,0,0,0,19,'','',0,0,'7663','2020-08-24','trick','2020-08-24 11:11:13','Hej, greetings to graphQL! ',0,'EUR',1,'ORDERFOLDER_NEW','','','','0000-00-00 00:00:00',0,'','OK',1,663,'oxidstandard',0,'2020-08-21 12:07:12',0),
('8c726d3f42ff1a6ea2828d5f309de881',1,'e7af1c3b786fd02906ccd75698f4e6b9','2020-05-23 14:08:55',4,'bill company','billuser@oxid-esales.com','Marc','Muster','Hauptstr.','13','additional bill info','bill vat id',1,'Freiburg','a7c40f631fc920687.20179984','BW','79098','1234','4567','MR','del company','Marcia','Pattern','Nebenstraße','123','del addinfo','Freiburg','a7c40f631fc920687.20179984','HH','79106','04012345678','04012345679','MRS','c66542acd6214dc0fd3f6fd54099a268','oxidinvoice',35.62,42.39,46.29,19,6.77,0,0,3.9,19,0,0,0,0,0,19,'','',0,0,'7664','2020-08-24','track','2020-08-24 11:11:14','please deliver as fast as you can',0,'EUR',1,'ORDERFOLDER_NEW','','','','0000-00-00 00:00:00',0,'','OK',1,664,'oxidstandard',0,'2020-08-21 12:08:55',0);

