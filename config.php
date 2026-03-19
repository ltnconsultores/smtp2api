<?php
// Domain name
define("APIMF_DOMAIN","");

// Sets error handling when message sending fails.
// If false, the message will be marked as processed by Postfix, with no further retry, therefore discarding it without sending
// If true, Postfix will defer the message for retry later. However, that does not guarantee message delivery, only retry.
// Note: API authentication errors always defer the message
define("APIMF_ONERROR_DEFER",false);

// Backend API. Currently only MICROSOFT is supported
define("APIMF_BACKEND","MICROSOFT");

// AAD Tenant ID
define("APIMF_TENANTID","");

// AAD App Client ID
define("APIMF_CLIENTID","");

// AAD App Client Secret
define("APIMF_CLIENTSECRET","");
