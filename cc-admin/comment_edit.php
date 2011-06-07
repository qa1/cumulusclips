<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Flag');
App::LoadClass ('Comment');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.member_edit.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$content = 'comment_edit.tpl';
$page_title = 'Edit Comment';
$data = array();
$Errors = array();
$message = null;



// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/comments.php';
}



### Verify a record was provided
if (isset ($_GET['id']) && is_numeric ($_GET['id']) && $_GET['id'] != 0) {

    ### Retrieve record information
    $comment = new Comment ($_GET['id']);
    if ($comment->found) {
        $video = new Video ($comment->video_id);
    } else {
        header ('Location: ' . ADMIN . '/comments.php');
        exit();
    }

} else {
    header ('Location: ' . ADMIN . '/comments.php');
    exit();
}





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {


    // Validate user fields if anonymous
    if ($comment->user_id == 0) {

        // Validate Name
        if (!empty ($_POST['name']) && !ctype_space ($_POST['name'])) {
            $data['name'] = htmlspecialchars ($_POST['name']);
        } else {
            $Errors['name'] = Language::GetText('error_name');
        }



        // Validate Email
        if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i',$_POST['email'])) {
            $data['email'] = $_POST['email'];
        } else {
            $Errors['email'] = Language::GetText('error_email');
        }



        // Validate Website
        if (!empty ($comment->website) && $_POST['website'] == '') {
            $data['website'] = '';
        } else if (!empty ($_POST['website']) && !ctype_space ($_POST['website'])) {
            $data['website'] = htmlspecialchars ($_POST['website']);
        }

    }



    // Validate status
    if (!empty ($_POST['status']) && !ctype_space ($_POST['status'])) {
        $data['status'] = htmlspecialchars (trim ($_POST['status']));
    } else {
        $Errors['status'] = 'Invalid status';
    }



    // Validate comments
    if (!empty ($_POST['comments']) && !ctype_space ($_POST['comments'])) {
        $data['comments'] = htmlspecialchars (trim ($_POST['comments']));
    } else {
        $Errors['comments'] = Language::GetText('error_comment');
    }



    // Update record if no errors were found
    if (empty ($Errors)) {

        // Perform addional actions based on status change
        if ($data['status'] != $comment->status) {

            // Handle "Approve" action
            if ($data['status'] == 'approved') {
                $comment->Approve (true);
            }

            // Handle "Ban" action
            else if ($data['status'] == 'banned') {
                Flag::FlagDecision ($comment->comment_id, 'comment', true);
            }

        }

        $message = 'Comment has been updated';
        $message_type = 'success';
        $comment->Update ($data);
        Plugin::Trigger ('admin.member_edit.update_member');

    } else {
        $message = Language::GetText('errors_below');
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $Errors);
        $message_type = 'error';
    }

}


// Output Page
include (THEME_PATH . '/admin.layout.tpl');

?>