**SMTP2API**
**by LTN Consultores S.A.C.S.**

SMTP2API is a SMTP gateway that receives messages via SMTP (with Postfix) with basic authentication, and sends that same messages via some API like Microsoft Graph.

Why?

Because both Microsoft and Google have deprecated (and, in some cases, disabled) basic SMTP authentication for their services. So if you have a piece of software that can only send email notifications via authenticated, non-SSL SMTP, then it's likely it will stop working soon, if it hasn't already. This can be critical in some old systems.

# Features

- SASL Authentication
- Support for Microsoft Graph API
- (Future) Support for Google GMail API

# Requirements

- Linux server (sorry, there will never be official support for Windows, as Postfix does not supports it. But WSL can be used transparently)
- Postfix with PCRE support
- Cyrus SASL with Plain, MD5, and SCRAM support
- PHP (any version 5+ should do, but only 7.x and 8.x have been tested). Only CLI needed
- PHP cURL extension (usually built-in the main library)

# Installing

> [!NOTE]
> Below procedure is an example for RHEL 9/10, or similar systems. Other systems might require different procedures.

Install required packages:
```
dnf -y install cyrus-sasl cyrus-sasl-plain cyrus-sasl-md5 cyrus-sasl-scram cyrus-sasl-ntlm postfix postfix-pcre php-cli
```

Copy files from repo:
```
cp apimf.php /etc/postfix/
cp config.php /etc/postfix/
cp main.cf /etc/postfix/
cp master.cf /etc/postfix/
cp pcre-fromowners.cf /etc/postfix/
cp smtpd.conf /etc/sasl2/
```

Create SASL database, set permissions:
```
chmod +x /etc/postfix/apimf.php
touch /etc/sasldb2
chmod 644 /etc/sasldb2
```

Change hostname and domain name in /etc/postfix/main.cf :
```
myhostname = %MYHOSTNAME% # Put server FQDN here
mydomain = $MYDOMAIN% # Put domain name here
```

Now go to [Azure Portal](https://portal.azure.com).
- Go to App Registrations
- Create a new App Registration, with your prefered name
- After creating, note the "Application (client) ID" and "Directory (tenant) ID"
- Go to Manage -> Certificates & secrets, in the left tree menu
- Create a new secret, and note down its Value
> [!WARNING]
> This is your only chance to view the secret. Future visits to the same page will only show the first few characters.
- Go to Manage -> API Permissions
- Click "Add a permission", select "Microsoft Graph"
- Choose "Application permissions" (Delegated permissions can also be used, but that's for a future tutorial)
- Go to "Mail", and, inside, select "Mail.Send". Then click "Add permissions"
- Click on "Grant admin consent for [TENANT NAME]". Confirm by clicking "Yes"

Configure the script:
```
define("APIMF_DOMAIN","%MYDOMAIN%"); // Put domain name here
define("APIMF_BACKEND","MICROSOFT"); // Microsoft is currently the only supported backend
define("APIMF_TENANTID",""); // Directory (tenant) ID
define("APIMF_CLIENTID",""); // Application (client) ID
define("APIMF_CLIENTSECRET",""); // Secret Value
```

Now create your first user:
```
saslpasswd2 -c -u DOMAIN USERNAME
```
It will ask twice for the password.

Please consider the following rules for user creation:
- The full username, which will be used in SMTP authentication, will be USERNAME@DOMAIN
- Such full username should match an existing account in Microsoft 365
- Messages sent over SMTP should have the same address as source address (From:)
- While AAD and SMTP users will have the same full name, their passwords have nothing to do with each other, and it's recommended they never do.

Now you can start Postfix:
```
systemctl start postfix
```
Make sure to open port 25 on your firewall. However, it's recommended to allow connections only from trusted hosts.
