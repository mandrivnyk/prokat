<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
/**
*	functions for affiliate program
*/

function affp_getCustomersNum($_customerID){
	
	$sql = "
		SELECT COUNT(*) FROM ".CUSTOMERS_TABLE."
		WHERE affiliateID = ".intval($_customerID)."
		 AND CHAR_LENGTH(ActivationCode)=0
	";
	list($affiliate_customers) = db_fetch_row(db_query($sql));
	return $affiliate_customers;
}

function affp_getRecruitedCustomers($_customerID, $_offset = 0, $_limit = 0){
	
	$_till = $_offset+$_limit;
	$customers = array();
	$sql = "
		SELECT customerID, Login, first_name, last_name, reg_datetime, ActivationCode FROM ".CUSTOMERS_TABLE."
		WHERE affiliateID = ".intval($_customerID)."
	";
	$result = db_query($sql);
	$i = 0;
	while ($_row = db_fetch_row($result)) {
		
		if ( ($i>=$_offset && $i<$_till && $_till>0) || (!$_till && !$_offset) ){

			$_t = explode(' ', $_row['reg_datetime']);
			$_row['reg_datetime'] = TransformDATEToTemplate($_t[0]);
			$customers["{$_row['customerID']}"] = $_row;
			$customers["{$_row['customerID']}"]['orders_num'] = 0;
			$customers["{$_row['customerID']}"]['currencies'] = array();
		}
		$i++;
	}
	
	if(!count($customers))return array();
	
	$sql = "
		SELECT customerID, currency_code, currency_value, order_amount FROM ".ORDERS_TABLE."
		WHERE customerID IN(".implode(", ", array_keys($customers)).") and statusID = '".CONF_COMPLETED_ORDER_STATUS."'
	";
	$result = db_query($sql);
	while (list($__customerID, $__currency_code, $__currency_value, $__order_amount) = db_fetch_row($result)) {
		
		if(!key_exists($__currency_code, $customers[$__customerID]['currencies']))
			$customers[$__customerID]['currencies'][$__currency_code] = 0;
		$customers[$__customerID]['currencies'][$__currency_code] += floatval(sprintf("%.2f",($__order_amount*$__currency_value)));
		$customers[$__customerID]['orders_num']++;
	}
	
	return $customers;
}

/**
 * remove recruited customer
 *
 * @param integer - customer id
 */
function affp_cancelRecruitedCustomer($_customerID){
	
	$sql = "
		UPDATE `".CUSTOMERS_TABLE."` SET affiliateID = 0 
		WHERE customerID = '".intval($_customerID)."'
	";
	db_query($sql);
}

/**
 * return payments by params
 *
 * @return array
 */
function affp_getPayments($_customerID, $_pID = '', $_from = '', $_till = '', $_order = ''){
	
	$sql = "
		SELECT pID, customerID, Amount, CurrencyISO3, xDate, Description
		FROM ".AFFILIATE_PAYMENTS_TABLE."
		WHERE 1
		".($_pID?" AND pID = '".intval($_pID)."'":"")."
		".($_customerID?" AND customerID = '".intval($_customerID)."'":"")."
		".($_from?" AND xDate>='".mysql_escape_string($_from)."'":"")."
		".($_till?" AND xDate<='".mysql_escape_string($_till)."'":"")."
		".($_order?" ORDER BY ".$_order:"")."
	";
	$result = db_query($sql);
	$payments = array();
	while ($_row = db_fetch_row($result)){
		
		$_row['Amount'] = sprintf("%.2f", $_row['Amount']);
		$_row['CustomerLogin'] = regGetLoginById($_row['customerID']);
		$_row['xDate'] = TransformDATEToTemplate($_row['xDate']);
		$payments[] = $_row;
	}
	return $payments;
}

/**
 * add new payment
 *
 * @param hash $_payment
 * @return new payment id
 */
function affp_addPayment($_payment){
	
	if(isset($_payment['Amount']))$_payment['Amount'] = sprintf("%.2f", $_payment['Amount']);
	$sql = "
		INSERT ".AFFILIATE_PAYMENTS_TABLE."
		(`".implode("`, `", TransformStringToDataBase(array_keys($_payment)))."`)
		VALUES('".implode("', '", TransformStringToDataBase($_payment))."')
	";
	db_query($sql);
	
	if(CONF_AFFILIATE_EMAIL_NEW_PAYMENT){
		
		$Settings = affp_getSettings($_payment['customerID']);
		if(!$Settings['EmailPayments'])return db_insert_id();
		
		$t 		= '';
		$Email 	= '';
		$FirstName = '';
		regGetContactInfo(regGetLoginById($_payment['customerID']), $t, $Email, $FirstName, $t, $t, $t);
		xMailTxt($Email, AFFP_NEW_PAYMENT, 'customer.affiliate.payment_notifi.txt', 
			array(
				'customer_firstname' 	=> $FirstName,
				'_AFFP_NEW_PAYMENT' 	=> str_replace('{MONEY}', $_payment['Amount'].' '.$_payment['CurrencyISO3'],AFFP_MAIL_NEW_PAYMENT)
				));
	}
	return db_insert_id();
}

/**
 * save payment
 *
 * @param array $_payment
 * @return bool
 */
function affp_savePayment($_payment){

	if(isset($_payment['Amount']))$_payment['Amount'] = round($_payment['Amount'], 2);
	if(!isset($_payment['pID'])) return false;
	$_pID = $_payment['pID'];
	unset($_payment['pID']);
	
	foreach ($_payment as $_ind=>$_val)
		$_payment[$_ind] = "`".TransformStringToDataBase($_ind)."`='".TransformStringToDataBase($_val)."'";
	$sql = "
		UPDATE ".AFFILIATE_PAYMENTS_TABLE."
		SET ".implode(", ", $_payment)."
		WHERE pID='".intval($_pID)."'
	";
	db_query($sql);
	return true;
}

/**
 * Delete payment
 *
 * @param integer - payment id
 */
function affp_deletePayment($_pID){
	
	$sql = "
		DELETE FROM `".AFFILIATE_PAYMENTS_TABLE."` WHERE pID='".$_pID."'
	";
	db_query($sql);
}

/**
 * Add commission to customer from order
 *
 * @param integer - order id
 */
function affp_addCommissionFromOrder($_orderID){
	
	$Commission = affp_getCommissionByOrder($_orderID);
	if($Commission['cID'])return 0;

	$Order 			= ordGetOrder( $_orderID );
	
	if($Order['customerID'])
		$RefererID 		= affp_getReferer($Order['customerID']);
	else 
		$RefererID 		= $Order['affiliateID'];	
		
	if(!$RefererID)return 0;
	
	$CustomerLogin = regGetLoginById($Order['customerID']);
	if(!$CustomerLogin)
		$CustomerLogin = $Order['customer_email'];
	
	$Commission 	= array(
		'Amount' 			=> sprintf("%.2f", ($Order['currency_value']*$Order['order_amount']*CONF_AFFILIATE_AMOUNT_PERCENT)/100),
		'CurrencyISO3' 	=> $Order['currency_code'],
		'xDateTime' 		=> date("Y-m-d H:i:s"),
		'OrderID' 			=> $_orderID,
		'CustomerID' 		=> $RefererID,
		'Description' 		=> TransformStringToDataBase(str_replace(array('{ORDERID}', '{USERLOGIN}'), array($_orderID, $CustomerLogin), AFFP_COMMISSION_DESCRIPTION))
	);
	
	do{
	if(CONF_AFFILIATE_EMAIL_NEW_COMMISSION){
		
		$Settings = affp_getSettings($RefererID);
		if(!$Settings['EmailOrders'])break;
		
		$t 				= '';
		$Email 			= '';
		$FirstName 		= '';
		regGetContactInfo(regGetLoginById($RefererID), $t, $Email, $FirstName, $t, $t, $t);
		xMailTxt($Email, AFFP_NEW_COMMISSION, 'customer.affiliate.commission_notifi.txt', 
			array(
				'customer_firstname' => $FirstName,
				'_AFFP_MAIL_NEW_COMMISSION' => str_replace('{MONEY}', $Commission['Amount'].' '.$Commission['CurrencyISO3'],AFFP_MAIL_NEW_COMMISSION)
				));
	}
	}while (0);
	
	affp_addCommission($Commission);
}

/**
 * Add commission to customer from commission array
 *
 * @param array - commission
 */
function affp_addCommission($_Commission){
	
	if(isset($_Commission['Amount']))$_Commission['Amount'] = round($_Commission['Amount'], 2);
	$sql = "
		INSERT `".AFFILIATE_COMMISSIONS_TABLE."`
		(`".implode("`, `", TransformStringToDataBase(array_keys($_Commission)))."`)
		VALUES('".implode("', '",$_Commission)."')
	";
	db_query($sql);
	return db_insert_id();
}

/**
 * Delete commission by cID
 *
 * @param integer cID - commission id
 */
function affp_deleteCommission($_cID){
	
	$sql = "
		DELETE FROM `".AFFILIATE_COMMISSIONS_TABLE."`
		WHERE cID='".intval($_cID)."'
	";
	db_query($sql);
}

/**
 * return commissions by params
 * @param integer $_customerID - customer id
 * @param integer $_cID - commission id
 * @param string $_from - from date in DATETIME format
 * @param string $_till - till date in DATETIME format
 * @param string $_order - order by this->...<-this
 * @return array
 */
function affp_getCommissions($_customerID, $_cID, $_from = '', $_till = '', $_order = ''){
	
	$sql = "
		SELECT cID, customerID, Amount, CurrencyISO3, xDateTime, Description, CustomerID
		FROM ".AFFILIATE_COMMISSIONS_TABLE."
		WHERE 1
		".($_cID?" AND cID = '".intval($_cID)."'":"")."
		".($_customerID?" AND customerID = '".intval($_customerID)."'":"")."
		".($_from?" AND xDateTime>='".mysql_escape_string($_from)."'":"")."
		".($_till?" AND xDateTime<='".mysql_escape_string($_till)."'":"")."
		".($_order?" ORDER BY ".$_order:"")."
	";
	$result = db_query($sql);
	$commissions = array();
	while ($_row = db_fetch_row($result)){
		
		$_row['CustomerLogin'] = regGetLoginById($_row['customerID']);
		$_row['Amount'] = sprintf("%.2f", $_row['Amount']);
		$_t = explode(' ', $_row['xDateTime']);
		$_row['xDateTime'] = TransformDATEToTemplate($_t[0]);
		$commissions[] = $_row;
	}
	return $commissions;
}

/**
 * save commission
 *
 * @param array 
 * @return bool
 */
function affp_saveCommission($_commission){

	if(isset($_commission['Amount']))$_commission['Amount'] = round($_commission['Amount'], 2);
	if(!isset($_commission['cID'])) return false;
	$_cID = $_commission['cID'];
	unset($_commission['cID']);
	
	foreach ($_commission as $_ind=>$_val)
		$_commission[$_ind] = "`".TransformStringToDataBase($_ind)."`='".TransformStringToDataBase($_val)."'";
	$sql = "
		UPDATE ".AFFILIATE_COMMISSIONS_TABLE."
		SET ".implode(", ", $_commission)."
		WHERE cID='".intval($_cID)."'
	";
	db_query($sql);
	return true;
}

/**
 * return commissions(earnings) for customer
 * @param integer - customer id
 * @return array
 */
function affp_getCommissionsAmount($_CustomerID){
	
	$CurrencyAmount = array();
	$sql = "
		SELECT SUM(`Amount`) AS CurrencyAmount, CurrencyISO3 FROM `".AFFILIATE_COMMISSIONS_TABLE."`
		WHERE CustomerID = '".intval($_CustomerID)."'
		GROUP BY `CurrencyISO3`
	";
	$result = db_query($sql);
	while ($_row = db_fetch_row($result)){
		
		$CurrencyAmount[$_row['CurrencyISO3']] = sprintf("%.2f", $_row['CurrencyAmount']);
	}
	return $CurrencyAmount;
}

/**
 * return payments to customer
 * @param integer - customer id
 * @return array
 */
function affp_getPaymentsAmount($_CustomerID){
	
	$PaymentAmount = array();
	$sql = "
		SELECT SUM(`Amount`) AS CurrencyAmount, CurrencyISO3 FROM `".AFFILIATE_PAYMENTS_TABLE."`
		WHERE CustomerID = '".intval($_CustomerID)."'
		GROUP BY `CurrencyISO3`
	";
	$result = db_query($sql);
	while ($_row = db_fetch_row($result)){
		
		$PaymentAmount[$_row['CurrencyISO3']] = sprintf("%.2f", $_row['CurrencyAmount']);
	}
	return $PaymentAmount;
}

/**
 * return settings for customer
 * @param integer - customer id
 * @return array
 */
function affp_getSettings($_CustomerID){
	
	$Settings = array();
	$sql = "
		SELECT affiliateEmailOrders, affiliateEmailPayments FROM `".CUSTOMERS_TABLE."`
		WHERE customerID='".intval($_CustomerID)."'
	";
	list($Settings['EmailOrders'], $Settings['EmailPayments']) = db_fetch_row(db_query($sql));
	return $Settings;
}

/**
 * save settings for customer
 * @param integer 
 * @param integer 
 */
function affp_saveSettings($_CustomerID, $_EmailOrders, $_EmailPayments){
	
	$sql = "
		UPDATE `".CUSTOMERS_TABLE."` 
		SET affiliateEmailOrders = '".intval($_EmailOrders)."', 
			affiliateEmailPayments = '".intval($_EmailPayments)."' 
		WHERE customerID='".intval($_CustomerID)."'
	";
	db_query($sql);
}

/**
 * get customer referer
 * @param integer - customer id
 * @return integer 
 */
function affp_getReferer($_CustomerID){
	
	$sql = "
		SELECT affiliateID FROM `".CUSTOMERS_TABLE."` 
		WHERE customerID='".intval($_CustomerID)."'
	";
	list($affiliateID) = db_fetch_row(db_query($sql));
	return $affiliateID;
}

/**
 * Return array with commission information by order id
 *
 * @param integer $_OrderID
 * @return array
 */
function affp_getCommissionByOrder($_OrderID){
	
	$sql = "
		SELECT cID, customerID, Amount, CurrencyISO3, xDateTime, Description, CustomerID
		FROM ".AFFILIATE_COMMISSIONS_TABLE."
		WHERE OrderID='".intval($_OrderID)."'
	";
	$commission = db_fetch_row(db_query($sql));
	
	if(!$commission['cID']) return $commission;
		
	$commission['CustomerLogin'] = regGetLoginById($commission['customerID']);
	$commission['Amount'] = sprintf("%.2f", $commission['Amount']);
	list($_t) = explode(' ', $commission['xDateTime']);
	$commission['xDateTime'] = TransformDATEToTemplate($_t);
	
	return $commission;
}
?>