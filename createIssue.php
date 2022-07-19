<?php
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
//EOF