<?php


#########################################################################################################
# Function Classes
#########################################################################################################

//-----------------------------------------------------------------------------------------------------
# Middleware Send data to GraphQL Github
function apiRequest($query,$variables = false){

    # encode the GraphQL query and variables if needed
    $json = json_encode(['query' => $query, 'variables' => $variables]);

    # set the curl parameters
    $chObj = curl_init();
    curl_setopt($chObj, CURLOPT_URL, 'https://api.github.com/graphql');
    curl_setopt($chObj, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chObj, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($chObj, CURLOPT_VERBOSE, true);
    curl_setopt($chObj, CURLOPT_POSTFIELDS, $json);

    # Adding Bearer Token to header
    curl_setopt($chObj, CURLOPT_HTTPHEADER,
            array(
                'User-Agent: '.GIT_OWNER,
                'Content-Type: application/json;charset=utf-8',
                'Authorization: bearer '. GIT_BEARER_TOKEN
            )
        );

    # get the response 
    $jsondata = curl_exec($chObj);
    $response = strip_tags($jsondata);
    return json_decode($response, true); //decode response
}


//-----------------------------------------------------------------------------------------------------
# usage pattern T: C: P:
function label($nameSelect, $pattern){
    $queryAllLabelData = 'query { repository( owner: "'.GIT_OWNER.'", name: "'.GIT_REPO.'") { labels(first: 50) { nodes { name id } } } }';
    $showLabelData = apiRequest($queryAllLabelData);
    $labelData = $showLabelData["data"]["repository"]["labels"]["nodes"];   
    # do some pattern matching to get T: C: P:
    $options = '<select name="'.$nameSelect.'">';
    foreach($labelData as $labelValue){
        if (preg_match('/'.$pattern.'/i', $labelValue["name"])){
            $options .= '<option value="'.$labelValue["id"].'">'.$labelValue["name"].'</option>';
        }
    }
    $options .= '</select>';
    return $options;
}


//-----------------------------------------------------------------------------------------------------
# sanitize form data
function clean($data)
{
    $data = htmlspecialchars($data);
    $data = stripslashes($data);
    $data = trim($data);
    return $data;
}


//-----------------------------------------------------------------------------------------------------
# array key existence
function get($key, $default=NULL) {
    return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
}

//-----------------------------------------------------------------------------------------------------
# array key existence
function session($key, $default=NULL) {
    return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}

//-----------------------------------------------------------------------------------------------------
# main old auth function
function oldapiRequest($url, $post=FALSE, $headers=array()) {
    $ch = curl_init($url);
  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Linux useragent'); //change agent string
  
    if($post)
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
  
    $headers[] = 'Accept: application/json';
    //$headers[] = 'Accept: application/vnd.github+json';
    
  
    # add access token to header 
    if(session('access_token'))
      $headers[] = 'Authorization: Bearer ' . session('access_token');
  
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  
    $response = curl_exec($ch);
    return json_decode($response); //decode response
}
  
//-----------------------------------------------------------------------------------------------------
//EOF