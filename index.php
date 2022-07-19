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
 * 
 * 
 * @todo       Add Docker yaml and .env file
 */

#########################################################################################################
# Global 
#########################################################################################################

# start sessions
session_start();

//lets check for errors leave this on..
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include_once('config.php'); # All config for system .env can use
include_once('classesFunctions.php'); # Classes and Funcitons
include_once('settings.php'); # All settings for system
include_once('authCheck.php'); # Authorization v3 api Github
include_once('styles_css.php'); # for styling purposes

#########################################################################################################
# VIEW
#########################################################################################################

# if successful show results
if(session('access_token')) {

    include('mainMenu.php'); # main menu 
    include('createIssue.php'); # how to create issues on GraphQL github 
    include('retrieveIssues.php'); # view issues via GraphQL

} else {
        
    # fail result if no session token
      echo '<h3>Not logged in</h3>';
      echo '<p><a href="?action=login">Log In</a></p>';
}




//-----------------------------------------------------------------------------------------------------
//EOF
