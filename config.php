<?php
// Domain name
define("APIMF_DOMAIN","");

// Sets error handling when message sending fails.
// If false, the message will be marked as processed by Postfix, with no further retry, therefore discarding it without sending
// If true, Postfix will defer the message for retry later. However, that does not guarantee message delivery, only retry.
// Note: API authentication errors always defer the message
define("APIMF_ONERROR_DEFER",false);

// Backend API. Supported options are MICROSOFT and GOOGLE
define("APIMF_BACKEND","MICROSOFT");

// Microsoft-specific parameters
define("APIMF_MS_TENANTID","");	// AAD Tenand ID "Directory (tenant) ID"
define("APIMF_MS_CLIENTID","");	// AAD Client ID "Application (client) ID:"
define("APIMF_MS_CLIENTSECRET","");	// AAD Secret Value

// Google
define("APIMF_GOOGLE_AUTHTOKEN","");	// Authentication token
