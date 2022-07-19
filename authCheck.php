<?php
#########################################################################################################
# Auth Check
#########################################################################################################

// Start the login process by sending the user to Github's authorization page
if(get('action') == 'login') {
    // Generate a random hash and store in the session for security
    $_SESSION['state'] = hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR']);
    unset($_SESSION['access_token']);
  
    $params = array(
      'client_id' => OAUTH2_CLIENT_ID,
      'redirect_uri' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'],
      'scope' => 'user',
      'state' => $_SESSION['state']
    );
  
    // Redirect the user to Github's authorization page
    header('Location: ' . $authorizeURL . '?' . http_build_query($params));
    die();
  }

// to kill all Sessions and reset code base 
if(get('action') == 'exit') {
    $_SESSION['state'] = '';
    $_SESSION['access_token'] = '';
    echo '<script>document.location.href="index.php";</script>';
}


// When Github redirects the user back here, there will be a "code" and "state" parameter in the query string
if(get('code')) {
    // Verify the state matches our stored state
    if(!get('state') || $_SESSION['state'] != get('state')) {
      header('Location: ' . $_SERVER['PHP_SELF']);
      die();
    }
  
    // Exchange the auth code for a token
    $token = oldapiRequest($tokenURL, array(
      'client_id' => OAUTH2_CLIENT_ID,
      'client_secret' => OAUTH2_CLIENT_SECRET,
      'redirect_uri' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'],
      'state' => $_SESSION['state'],
      'code' => get('code')
    ));
    $_SESSION['access_token'] = $token->access_token;
  
    header('Location: ' . $_SERVER['PHP_SELF']);
  }

//-----------------------------------------------------------------------------------------------------
//EOF