<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
/**
 * @package Services_PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'Services/PayPal/Type/XSDSimpleType.php';

/**
 * AmountType
 *
 * @package Services_PayPal
 */
class AmountType extends XSDSimpleType
{
    function AmountType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:CoreComponentTypes';
        $this->_attributes = array_merge($this->_attributes,
            array (
              'currencyID' => 
              array (
                'name' => 'currencyID',
                'type' => 'ebl:CurrencyCodeType',
                'use' => 'required',
              ),
            ));
    }

}
