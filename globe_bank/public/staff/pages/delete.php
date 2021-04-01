<?php 
    require_once('../../../private/initialize.php'); 
    
    require_login();
    
    if (!isset($_GET['id'])) {
        redirect_to(url_for('/staff/pages/index.php'));
    }
    $id = $_GET['id'];

    $page = find_page_by_id($id);

    if (is_post_request()) {
        $result = delete_page($id);
        $_SESSION['message'] = 'The page was deleted successfully.';
        redirect_to(url_for('staff/subjects/show.php?id=' . h(u($page['subject_id']))));
    }
?>

<?php $page_title = 'Delete Page'; ?>
<?php include(SHARED_PATH . "/staff_header.php");?>

<div id="content">

<a class="back-link" href="<?php echo url_for('staff/subjects/show.php?id=' . h(u($page['subject_id']))); ?>">&laquo; Back to Subject Page</a>
    <p>Are you sure you want to delete this page?</p>
    <p class="item"><?php echo h($page['menu_name']) ?></p>

    <form action="<?php echo url_for('/staff/pages/delete.php?id=' . $page['id']) ?>" method="post">
        <input type="submit" name="commit" value="Delete Page">
    </form>

    
</div>

<?php include(SHARED_PATH . "/staff_footer.php");?>