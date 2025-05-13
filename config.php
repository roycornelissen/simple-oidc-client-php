<?php

// index.php interface configuration
$title = "Generate Tokens";
$img = "https://clickhelp.co/images/feeds/blog/2016.05/keys.jpg";
$scopeInfo = "This service requires the following permissions for your account:";

// Client configuration
$issuer = "https://login.microsoftonline.com/96e49a13-05af-4172-8df0-b4b3cf98dd20/v2.0";  // Microsoft Azure AD
$clientId = "94f61305-3f49-4228-aad3-1bd6fcc8116d";
$clientSecret = "";  // comment if you are using PKCE
// $pkceCodeChallengeMethod = "S256";   // uncomment to use PKCE
$redirectPage = "auth.php";  // select between "refreshtoken.php" and "auth.php"
$redirectUrl = "https://minikube.local/sample-oidc/" . $redirectPage;
// add scopes as keys and a friendly message of the scope as value
$scopesDefine = array(
    'openid' => 'log in using your identity',
    'email' => 'read your email address',
    'profile' => 'read your basic profile info',
);
// refreshtoken.php interface configuration
$refreshTokenNote = "NOTE: New refresh tokens expire in 12 months.";
$accessTokenNote = "NOTE: New access tokens expire in 1 hour.";
$manageTokenNote = "You can manage your refresh tokens in the following link: ";
$manageTokens = $issuer . "manage/user/services";
$sessionName = "sample-oidc";  // This value must be the same with the name of the parent directory
$sessionLifetime = 60 * 60;  // must be equal to access token validation time in seconds
$bannerText = "";
$bannerType = "info";  // Select one of "info", "warning", "error" or "success"
$allowIntrospection = false;
$enableActiveTokensTable = false;  // This option works only for MITREid Connect based OPs
$showIdToken = true;
