<?php

use JoelButcher\Socialstream\Features;

return [
    'guard' => 'web',
    'middleware' => ['web'],
    'prompt' => 'Or log in with',

    /*
    |--------------------------------------------------------------------------
    | Social login providers
    |--------------------------------------------------------------------------
    |
    | Opt-in, and empty by default. All nine providers used to be enabled
    | unconditionally while config/services.php carried credentials for none of
    | them, so the login page offered nine buttons and every one of them threw
    | DriverMissingConfigurationException — a 500 — when clicked. (Two of them
    | were also both labelled "LinkedIn": linkedin and linkedinOpenId.)
    |
    | A button that cannot work should not be on the page. Enable only what you
    | have credentials for, in .env:
    |
    |   SOCIALSTREAM_PROVIDERS=google,github
    |
    | and add the matching client_id / client_secret / redirect block to
    | config/services.php. With none set, the social section does not render.
    |
    | Supported: bitbucket, facebook, github, gitlab, google, linkedin,
    | linkedin-openid, slack, twitter-oauth-2
    |
    | Before enabling any of them, uncomment `use HasConnectedAccounts;` in
    | app/Models/User.php. Without that trait $user->connectedAccounts is null,
    | and Socialstream's ConnectedAccountsForm calls ->map() on it — which takes
    | /user/profile down with a 500 the moment a provider is switched on.
    |
    | Note that `linkedin` and `linkedin-openid` both render as "LinkedIn"; pick one.
    |
    */
    'providers' => array_values(array_filter(
        array_map('trim', explode(',', (string) env('SOCIALSTREAM_PROVIDERS', '')))
    )),
    'features' => [
        Features::generateMissingEmails(),
        Features::createAccountOnFirstLogin(),
        Features::globalLogin(),
        Features::authExistingUnlinkedUsers(),
        Features::rememberSession(),
        Features::providerAvatars(),
        Features::refreshOAuthTokens(),
    ],
    'home' => '/app',
    'redirects' => [
        'login' => '/app',
        'register' => '/app',
        'login-failed' => '/login',
        'registration-failed' => '/register',
        'provider-linked' => '/user/profile',
        'provider-link-failed' => '/user/profile',
    ]
];
