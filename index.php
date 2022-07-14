<?php
/**
 * index.php
 *
 * Github OAuth API access
 *
 * @category   Basic Issue Tracker using Github API and OAuth to view list of issues and create new issues.
 *             - View Multiple assignees / multiple labels per category e.g: Type / Priority / Clients per issue.
 *             - Create Issue for repository.
 *             - 259 Lines of Code with comments
 *             - 12 Kb on disk filesize
 * @package    Github OAuth Client
 * @author     Dean Dalton <daltonniel80@icloud.com>
 * @copyright  2022 - Dean Dalton
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id:$
 * @link       https://github.com/DeanDevel/github-oauth2-client.php
 * @see        v4/GraphQL, Vanilla PHP 8.1.7 compatible
 * @since      File available since Release 1.0.1
 */
#########################################################################################################
# Global Settings
#########################################################################################################

//-----------------------------------------------------------------------------------------------------
// Settings

# Add Github Credentials
define('GIT_OWNER', ''); //add git Username/Owner:
define('GIT_REPO', ''); //add git Repository Name:
define('GIT_BEARER_TOKEN', ''); //add personal access token
$repoIssueRetrieve = '25'; //how many issues to retrieve

//-----------------------------------------------------------------------------------------------------
// Global

# Get the repo ID to form queries based on node id
$queryRepoData = 'query FindRepo { repository( owner: "'.GIT_OWNER.'", name: "'.GIT_REPO.'" ) { id } }';
$repoDataId = apiRequest($queryRepoData);
$repoID = $repoDataId["data"]["repository"]["id"];

#########################################################################################################
# Function Classes
#########################################################################################################

//-----------------------------------------------------------------------------------------------------
// FUNCTIONS CLASSESS

# Send data to Github GraphQL
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

# sanitize form data
function clean($data)
{
    $data = htmlspecialchars($data);
    $data = stripslashes($data);
    $data = trim($data);
    return $data;
}

#########################################################################################################
# VIEW
#########################################################################################################

//-----------------------------------------------------------------------------------------------------
// Create Issue

# start of create issue
print '<h3>Create Issue</h3>';

# Send the data via GraphQL to Github
if (isset($_POST['submit'])){

    # Sanitize inputs from user
    // prevent XSS
    $title = clean($_POST['title']);
    $body = clean($_POST['body']);
    $client = clean($_POST['client']);
    $priority = clean($_POST['priority']);
    $type = clean($_POST['type']);

    # Graph Request of issue
    $queryRepoData = 'mutation CreateIssue { createIssue ( input: { repositoryId: "'.$repoID.'", title: "'.$title.'", body: "'.$body.'", labelIds: ["'.$client.'","'.$priority.'","'.$type.'"], assigneeIds: ["U_kgDOBdTQNg"]}) { issue { id url } } }';
    $issueCreateData = apiRequest($queryRepoData);

    # Check the information
    if (!isset($issueCreateData["data"]["createIssue"]["issue"]["id"])){
        print '<div class="alertError">Error: '.$issueCreateData["errors"][0]["message"].'</div>';
    } else {
        print '<div class="alertSuccess">Successfully Created Issue</div>';
    }

}

# print out the form
print '<form method="POST"><table border="0" width="100%">
        <tr><td>Title</td><td>Description</td><td>Client</td><td>Priority</td><td>Type</td><td>Select</td></tr>
        <tr>
            <td valign="top">
                <input type="text" name="title" style="width:100%;" placeholder="Please add a Title" required>
            </td>
            <td valign="top">
                <textarea name="body" style="width:100%;" placeholder="Please add a Body Description for this issue ticket"></textarea>
            </td>
            <td valign="top" width="5%">';
                # get info for labels by C:
                print label('client','C:');
            print '</td><td valign="top" width="5%">';
                # get info for labels by P:
                print label('priority','P:');
            print '</td><td valign="top" width="5%">';
                # get info for labels by T:
                    print label('type','T:');
            print '</td><td valign="top" width="5%">
                <input type="submit" name="submit" value="Submit">
            </td>
        </tr>
    </table>
</form><hr />';

//-----------------------------------------------------------------------------------------------------
// Retrieving Data from Git Issues

# start of retrieve of issues
print '<h3>Retrieving ('.$repoIssueRetrieve.') issues (open and closed) from the repository</h3>';

# GraphQL Request of issue
$queryIssues = 'query { repository( owner: "'.GIT_OWNER.'", name: "'.GIT_REPO.'" ) { issues(orderBy: {field: UPDATED_AT, direction: DESC}, first: '.$repoIssueRetrieve.') { edges { cursor node { title body assignees(first: 10) { edges { node { login } } } createdAt updatedAt state labels(first: 50) { edges { node { name } } } } } } } }';
$repoData = apiRequest($queryIssues);

# Get the arrays for each issue
$arrData = $repoData["data"]["repository"]["issues"]["edges"];

# start showing table
print '<table border="1" width="100%">
<tr class="MainMenu">
    <td>Number</td>
    <td>Title</td>
    <td>Description (Body)</td>
    <td>Client<br /><small>Has Multiple</small></td>
    <td>Priority<br /><small>Has Multiple</small></td>
    <td>Type<br /><small>Has Multiple</small></td>
    <td>Assigned To<br /><small>Has Multiple</small></td>
    <td>Status</td>
</tr>';

# Loop through the array
$i=1;foreach ($arrData as $data){

    # set and clear
    $show_client = $show_priority = $show_type = $show_assignee = '';

    # Get the assignees 
    $arrAssignees = $data["node"]["assignees"]["edges"];

    # time to loop the assignee
    foreach($arrAssignees as $dataAssignees){
        $show_assignee .= $dataAssignees["node"]["login"].'<br />'; 
    }

    # Get the labels 
    $arrLabels = $data["node"]["labels"]["edges"];

    # time to loop the labels
    foreach($arrLabels as $dataLabel)
    {
        $labelName = $dataLabel["node"]["name"];
        # check label for pattern T: type for multiple lables
        if (preg_match('/T:/i', $labelName))
            $show_type .= str_replace('T:','',$labelName).'<br />';

        # check label for pattern C: client for multiple lables
        if (preg_match('/C:/i', $labelName))
            $show_client .= str_replace('C:','',$labelName).'<br />';

        # check label for pattern P: priority for multiple lables
        if (preg_match('/P:/i', $labelName))
            $show_priority .= str_replace('P:','',$labelName).'<br />';
    }

    # lets print some stuff
    print '<tr>
        <td>'.$i++.'</td>
        <td>'.$data["node"]["title"].'</td>
        <td>'.$data["node"]["body"].'</td>
        <td valign="top">'.($show_client ?? '').'</td>
        <td valign="top">'.($show_priority ?? '').'</td>
        <td valign="top">'.($show_type ?? '').'</td>
        <td valign="top">'.($show_assignee ?? '').'</td>
        <td>'.$data["node"]["state"].'</td>
    </tr>'; 
} print '</table>';

#########################################################################################################
# Styling
#########################################################################################################

//-----------------------------------------------------------------------------------------------------
// Sass CSS 

# adding some class styling
print '<style>
.MainMenu {
    font-size:20px;
    font-weight:bold;
}
small {
    font-size:10px;
    font-weight:normal;
}
.alertError {
    background-color:#FF5733;
    text-align:center;
}
.alertSuccess {
    background-color:#50C878;
    text-align:center;
}
</style>';

//-----------------------------------------------------------------------------------------------------
//EOF
?>