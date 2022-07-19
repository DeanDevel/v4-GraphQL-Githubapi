<?php

# Add Github Credentials // env is better but for nostalgic reasons
define('GIT_OWNER', ''); //add git Username/Owner:
define('GIT_REPO', ''); //add git Repository Name:
define('OAUTH2_CLIENT_ID', ''); //add client id here
define('OAUTH2_CLIENT_SECRET', ''); //add client secret here
define('GIT_BEARER_TOKEN', ''); //add personal access token
$repoIssueRetrieve = '15'; //how many issues to retrieve

# URL of github api
$authorizeURL = 'https://github.com/login/oauth/authorize';
$tokenURL = 'https://github.com/login/oauth/access_token';
$apiURLBase = 'https://api.github.com/';