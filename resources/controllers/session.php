<?php
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    error_log("Debug: HTTPS is set to on.");
    $_SERVER['HTTPS'] = 'on';
}
error_log('Session cookie params: ' . json_encode(session_get_cookie_params()));
error_log('Session ID: ' . session_id());
include __DIR__ . '/../../vendor/autoload.php';

use Jumbojett\OpenIDConnectClient;

include __DIR__ . '/../../config.php';
include __DIR__ . '/../../src/MitreIdConnectUtils.php';

if (!isset($_SESSION)) {
    error_log('Debug: Session was not set. Session name is ' . $sessionName);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/' . $sessionName,
        'secure' => ($_SERVER['HTTPS'] === 'on'), // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax', // Adjust if needed
    ]);
    @session_start();
}

if (empty($clientSecret)) {
    $clientSecret = null;
}

$oidc = new OpenIDConnectClient(
    $issuer,
    $clientId,
    $clientSecret
);
$scopes = array_keys($scopesDefine);
$oidc->addScope($scopes);
$oidc->setRedirectURL($redirectUrl);
$oidc->setResponseTypes(['code']);
if (!empty($pkceCodeChallengeMethod)) {
    $oidc->setCodeChallengeMethod($pkceCodeChallengeMethod);
}

if (isset($_SESSION['sub']) && time() - $_SESSION['CREATED'] < $sessionLifetime) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create-refresh-token':
                $_SESSION['action'] = 'create-refresh-token';
                $scopes[] = "offline_access";
                $oidc->addScope($scopes);
                $oidc->addAuthParam(['action' => 'create-refresh-token']);
                $oidc->authenticate();
                break;
            case 'revoke':
                $oidc->revokeToken($_POST['token'], '', $clientId, $clientSecret);
                $_SESSION['action'] = 'revoke';
                if ($_POST['token'] == $_SESSION['refresh_token']) {
                    $_SESSION['refresh_token'] = null;
                }
                break;
            default:
                break;
        }
    }
    if (isset($_SESSION['action']) && $_SESSION['action'] == 'create-refresh-token') {
        $oidc->authenticate();
        $refreshToken = $oidc->getRefreshToken();
        $sub = $oidc->requestUserInfo('sub');
        if ($sub) {
            $accessToken = $_SESSION['access_token'];
            $idToken = $_SESSION['id_token'];
            $_SESSION['refresh_token'] = $refreshToken;
        }
        unset($_SESSION['action']);
    } else {
        $accessToken = $_SESSION['access_token'];
        $idToken = $oidc->getIdToken();
        $refreshToken = $_SESSION['refresh_token'];
        unset($_SESSION['action']);
    }
} else {
    $oidc->authenticate();
    $accessToken = $oidc->getAccessToken();
    $idToken = $oidc->getIdToken();
    $refreshToken = $oidc->getRefreshToken();
    $sub = $oidc->requestUserInfo('sub');
    if ($sub) {
        $_SESSION['sub'] = $sub;
        $_SESSION['access_token'] = $accessToken;
        $_SESSION['id_token'] = $idToken;
        $_SESSION['refresh_token'] = $refreshToken;
        $_SESSION['CREATED'] = time();
    }
}

$openidConfiguration = getMetadata($issuer);
$tokenEndpoint = $openidConfiguration->{'token_endpoint'};
$userInfoEndpoint = $openidConfiguration->{'userinfo_endpoint'};
$introspectionEndpoint = $openidConfiguration->{'introspection_endpoint'};
