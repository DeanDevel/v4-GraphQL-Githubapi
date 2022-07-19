<?php

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

//-----------------------------------------------------------------------------------------------------
//EOF