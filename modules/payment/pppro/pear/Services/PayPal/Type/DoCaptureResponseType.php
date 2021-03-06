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
require_once 'Services/PayPal/Type/AbstractResponseType.php';

/**
 * DoCaptureResponseType
 *
 * @package Services_PayPal
 */
class DoCaptureResponseType extends AbstractResponseType
{
    var $DoCaptureResponseDetails;

    function DoCaptureResponseType()
    {
        parent::AbstractResponseType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'DoCaptureResponseDetails' => 
              array (
                'required' => true,
                'type' => 'DoCaptureResponseDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getDoCaptureResponseDetails()
    {
        return $this->DoCaptureResponseDetails;
    }
    function setDoCaptureResponseDetails($DoCaptureResponseDetails, $charset = 'iso-8859-1')
    {
        $this->DoCaptureResponseDetails = $DoCaptureResponseDetails;
        $this->_elements['DoCaptureResponseDetails']['charset'] = $charset;
    }
}
