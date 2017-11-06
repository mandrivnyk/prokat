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
require_once 'Services/PayPal/Type/XSDType.php';

/**
 * AbstractResponseType
 * 
 * Base type definition of a response payload that can carry any type of payload
 * content with following optional elements: - timestamp of response message, -
 * application level acknowledgement, and - application-level errors and warnings.
 *
 * @package Services_PayPal
 */
class AbstractResponseType extends XSDType
{
    /**
     * This value represents the date and time (GMT) when the response was generated by
     * a service provider (as a result of processing of a request).
     */
    var $Timestamp;

    /**
     * Application level acknowledgement code.
     */
    var $Ack;

    /**
     * CorrelationID may be used optionally with an application level acknowledgement.
     */
    var $CorrelationID;

    var $Errors;

    /**
     * This refers to the version of the response payload schema.
     */
    var $Version;

    /**
     * This refers to the specific software build that was used in the deployment for
     * processing the request and generating the response.
     */
    var $Build;

    function AbstractResponseType()
    {
        parent::XSDType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'Timestamp' => 
              array (
                'required' => false,
                'type' => 'dateTime',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Ack' => 
              array (
                'required' => true,
                'type' => 'AckCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'CorrelationID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Errors' => 
              array (
                'required' => false,
                'type' => 'ErrorType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Version' => 
              array (
                'required' => true,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Build' => 
              array (
                'required' => true,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getTimestamp()
    {
        return $this->Timestamp;
    }
    function setTimestamp($Timestamp, $charset = 'iso-8859-1')
    {
        $this->Timestamp = $Timestamp;
        $this->_elements['Timestamp']['charset'] = $charset;
    }
    function getAck()
    {
        return $this->Ack;
    }
    function setAck($Ack, $charset = 'iso-8859-1')
    {
        $this->Ack = $Ack;
        $this->_elements['Ack']['charset'] = $charset;
    }
    function getCorrelationID()
    {
        return $this->CorrelationID;
    }
    function setCorrelationID($CorrelationID, $charset = 'iso-8859-1')
    {
        $this->CorrelationID = $CorrelationID;
        $this->_elements['CorrelationID']['charset'] = $charset;
    }
    function getErrors()
    {
        return $this->Errors;
    }
    function setErrors($Errors, $charset = 'iso-8859-1')
    {
        $this->Errors = $Errors;
        $this->_elements['Errors']['charset'] = $charset;
    }
    function getVersion()
    {
        return $this->Version;
    }
    function setVersion($Version, $charset = 'iso-8859-1')
    {
        $this->Version = $Version;
        $this->_elements['Version']['charset'] = $charset;
    }
    function getBuild()
    {
        return $this->Build;
    }
    function setBuild($Build, $charset = 'iso-8859-1')
    {
        $this->Build = $Build;
        $this->_elements['Build']['charset'] = $charset;
    }
}
