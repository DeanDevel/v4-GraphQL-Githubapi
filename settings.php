<?php

#########################################################################################################
# Settings
#########################################################################################################

//-----------------------------------------------------------------------------------------------------
// Global

# Get the repo ID to form queries based on node id
$queryRepoData = 'query FindRepo { repository( owner: "'.GIT_OWNER.'", name: "'.GIT_REPO.'" ) { id } }';
$repoDataId = apiRequest($queryRepoData);
$repoID = $repoDataId["data"]["repository"]["id"];


//-----------------------------------------------------------------------------------------------------
//EOF