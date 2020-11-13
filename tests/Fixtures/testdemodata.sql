SET @@session.sql_mode = '';

REPLACE INTO `oxuser` (`OXID`, `OXACTIVE`, `OXRIGHTS`, `OXSHOPID`, `OXUSERNAME`, `OXPASSWORD`, `OXPASSSALT`, `OXCUSTNR`, `OXUSTID`, `OXCOMPANY`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXADDINFO`, `OXCITY`, `OXCOUNTRYID`, `OXSTATEID`, `OXZIP`, `OXFON`, `OXFAX`, `OXSAL`, `OXBONI`, `OXCREATE`, `OXREGISTER`, `OXPRIVFON`, `OXMOBFON`, `OXBIRTHDATE`, `OXURL`, `OXUPDATEKEY`, `OXUPDATEEXP`, `OXPOINTS`) VALUES
('e7af1c3b786fd02906ccd75698f4e6b9', 1, 'user', 1, 'user@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 8, '', '', 'User', 'User', 'Street', '13', '', 'City', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '0000-00-00', '', '', 0, 0),
('otheruser', 1, 'user', 1, 'otheruser@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 18, '', '', 'Marc', 'Muster', 'Hauptstr.', '13', '', 'Freiburg', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '0000-00-00', '', '', 0, 0),
('checkoutuser', 1, 'user', 1, 'checkoutuser@oxid-esales.com',  '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2', 'b186f117054b700a89de929ce90c6aef', 88, '', '', 'Marc', 'Muster', 'Hauptstr.', '13', '', 'Freiburg', 'a7c40f631fc920687.20179984', '', '79098', '', '', 'MR', 1000, '2011-02-01 08:41:25', '2011-02-01 08:41:25', '', '', '0000-00-00', '', '', 0, 0);

REPLACE INTO `oxuserbaskets` (`OXID`, `OXUSERID`, `OXTITLE`, `OXPUBLIC`, `OEGQL_PAYMENTID`, `OEGQL_DELIVERYMETHODID`, `OEGQL_DELADDRESSID`) VALUES
('basket_user', 'e7af1c3b786fd02906ccd75698f4e6b9', 'savedbasket', true, null, null, null),
('basket_otheruser', 'otheruser', 'savedbasket', true, null, null, null),
('basket_user_address_payment', 'e7af1c3b786fd02906ccd75698f4e6b9', 'basketPayment', true, 'oxiddebitnote', 'oxidstandard', 'address_user'),
('basket_user_3', 'e7af1c3b786fd02906ccd75698f4e6b9', 'basketPayment', true, null, null, null),
('basket_payment', 'e7af1c3b786fd02906ccd75698f4e6b9', 'basketPaymentMethod', true, 'oxiddebitnote', 'oxidstandard', 'address_user'),
('basket_payment_cost', 'e7af1c3b786fd02906ccd75698f4e6b9', 'basketPaymentCost', true, 'oxidgraphql', '_deliveryset', 'address_user');

REPLACE INTO `oxuserbasketitems` (`OXID`, `OXBASKETID`, `OXARTID`, `OXAMOUNT`, `OXSELLIST`, `OXPERSPARAM`) VALUES
('_test_basket_payment_item_1', 'basket_payment', 'dc5ffdf380e15674b56dd562a7cb6aec', 1, 'N;', ''),
('_test_basket_payment_cost_item_1', 'basket_payment_cost', 'f4f2d8eee51b0fd5eb60a46dff1166d8', 2, 'N;', '');

REPLACE INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXDESC`, `OXADDSUM`, `OXADDSUMTYPE`, `OXADDSUMRULES`, `OXFROMBONI`, `OXFROMAMOUNT`, `OXTOAMOUNT`, `OXVALDESC`, `OXCHECKED`, `OXDESC_1`, `OXVALDESC_1`, `OXDESC_2`, `OXVALDESC_2`, `OXDESC_3`, `OXVALDESC_3`, `OXLONGDESC`, `OXLONGDESC_1`, `OXLONGDESC_2`, `OXLONGDESC_3`, `OXSORT`) VALUES
('oxidgraphql', 1, 'GraphQL', 7.77, 'abs', 0, 0, 0, 1000000, '', 1, 'GraphQL (coconuts)', '', '', '', '', '', '', '', '', '', 700);

REPLACE INTO `oxdeliveryset` (`OXID`, `OXSHOPID`, `OXACTIVE`, `OXACTIVEFROM`, `OXACTIVETO`, `OXTITLE`, `OXTITLE_1`, `OXTITLE_2`, `OXTITLE_3`, `OXPOS`) VALUES
('_deliveryset', 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'graphql set', 'graphql set', '', '', 50),
('_unavailabledeliveryset', 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'unavailable graphql set', 'unavailable graphql set', '', '', 60);

REPLACE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES
('_paymentrelation1', 'oxidgraphql', 'a7c40f631fc920687.20179984', 'oxcountry'),
('_paymentrelation2', 'oxidgraphql', '_deliveryset', 'oxdelset');

REPLACE INTO `oxobject2delivery` (`OXID`, `OXDELIVERYID`, `OXOBJECTID`, `OXTYPE`) VALUES
('_deliveryrelation1', '_deliveryset', 'a7c40f631fc920687.20179984', 'oxdelset'),
('_deliveryrelation2', '_graphqldel', 'a7c40f631fc920687.20179984', 'oxcountry');

REPLACE INTO `oxdelivery` (`OXID`, `OXSHOPID`, `OXACTIVE`, `OXACTIVEFROM`, `OXACTIVETO`, `OXTITLE`, `OXTITLE_1`, `OXTITLE_2`, `OXTITLE_3`,
`OXADDSUMTYPE`, `OXADDSUM`, `OXDELTYPE`, `OXPARAM`, `OXPARAMEND`, `OXFIXED`, `OXSORT`, `OXFINALIZE`, `OXTIMESTAMP`) VALUES
('_graphqldel',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00','Versandkosten für GraphQL: 6,66 Euro','Shipping costs for GraphQL: 6.66 Euro','','','abs',6.66,'p',0,99999,0,2000,1,'2020-07-16 14:21:45'),
('_unavailablegraphqldel',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00','Versandkosten für UA GraphQL: 6,66 Euro','Shipping costs for UA GraphQL: 6.66 Euro','','','abs',6.66,'p',0,99999,0,2000,1,'2020-07-16 14:21:45');

REPLACE INTO `oxdel2delset` (`OXID`, `OXDELID`, `OXDELSETID`) VALUES
('_setrelation1', '_graphqldel', '_deliveryset');

REPLACE INTO `oxaddress` (`OXID`, `OXUSERID`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXCITY`, `OXCOUNTRY`, `OXCOUNTRYID`, `OXSTATEID`, `OXZIP`, `OXSAL`, `OXTIMESTAMP`) VALUES
('address_user', 'e7af1c3b786fd02906ccd75698f4e6b9', 'User Del', 'User Del', 'Street Del', '13', 'City Del', 'Germany', 'a7c40f631fc920687.20179984', '', '79098', 'MR', '2020-07-14 14:12:48'),
('address_otheruser', 'otheruser', 'Marc', 'Muster', 'Hauptstr', '13', 'Freiburg', 'Germany', 'a7c40f631fc920687.20179984', '', '79098', 'MR', '2020-07-14 14:12:48');

REPLACE INTO `oxvoucherseries` (`OXID`, `OXSHOPID`, `OXSERIENR`, `OXDISCOUNT`, `OXDISCOUNTTYPE`, `OXBEGINDATE`, `OXENDDATE`, `OXSERIEDESCRIPTION`, `OXALLOWOTHERSERIES`) VALUES
('basket_payment_cost_voucher', 1, 'basket_payment_cost_voucher', 10.0, 'absolute', '2000-01-01', '2050-12-31', 'basket payment cost voucher', 0);

REPLACE INTO `oxvouchers` (`OXDATEUSED`, `OXORDERID`, `OXUSERID`, `OXRESERVED`, `OXVOUCHERNR`, `OXVOUCHERSERIEID`, `OXDISCOUNT`, `OXID`, `OXTIMESTAMP`, `OEGQL_BASKETID`) VALUES
('2020-10-10',	'',	'',	0,	'basket_payment_cost_voucher',	'basket_payment_cost_voucher',	10.00,	'basket_payment_cost_voucher_1',	'2020-11-16 11:26:01',	'basket_payment_cost');
