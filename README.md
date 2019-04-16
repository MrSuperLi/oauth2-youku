# oauth2-youku
YouKu OAuth 2.0 support for the PHP League's OAuth 2.0 Client
##Install
You can open a terminal and type in
```shell
composer require mrsuperli/oauth2-youku
```
or require in a composer.json
```json
"require": {
	"mrsuperli/oauth2-youku": "~1.0"
}
```
then run:
```shell
composer update
```
##Useage
```php
session_start();
$provider = new \MrSuperLi\OAuth2\Client\Provider\YouKu([
	'clientId' => '{clientId}',
	'clientSecret' => '{clientSecret}',
	'redirectUri' => 'http://example.com/callback-url',
]);
if (!isset($_GET['code'])) {
	$authUrl = $provider->getAuthorizationUrl();
	$_SESSION['oauth2state'] = $provider->getState();
	header('Location: '.$authUrl);
	exit;
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
	unset($_SESSION['oauth2state']);
	exit('Invalid state');
} else {
	$token = $provider->getAccessToken('authorization_code', [
		'code' => $_GET['code']
	]);

	//fetch userinfo returned by serverside
    $user = $provider->getResourceOwner($token);
    $id = $user->getId();
    $nickname = $user->getNickname();
    $avatar = $user->getAvatar();
    $user = $user->toArray();
    print_r($user);
}
```
###License
The MIT License (MIT). Please see [License](https://github.com/spoonwep/oauth2-qq/blob/master/LICENSE.txt) File for more information.
