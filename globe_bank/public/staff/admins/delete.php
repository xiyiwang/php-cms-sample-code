<?php 
    require_once('../../../private/initialize.php'); 

    require_login();

    if(!isset($_GET['id'])) {
        redirect_to(url_for('/staff/admins/index.php'));
    }
    $id = $_GET['id'];
    $admin = find_admin_by_id($id);

    if(is_post_request()) {
        $result = delete_admin($id);
        $_SESSION['message'] = "The admin was deleted successfully.";
        redirect_to(url_for('/staff/admins/index.php'));
    } else {
        $admin = find_admin_by_id($id);
    }
?>

<?php $page_title = 'Delete Admin'; ?>
<?php include(SHARED_PATH . '/staff_header.php'); ?>

<div id="content">

    <a class="back-link" href="<?php echo url_for('/staff/admin/index.php') ?>">&laquo; Back to List</a>
    <p>Are you sure you want to delete this admin?</p>
    <p class="item"><?php echo h($admin['first_name']) . h($admin['last_name']); ?></p>

    <form action="<?php echo url_for('/staff/admins/delete.php?id=' . h(u($id))); ?>" method="post">
        <div id="operations">
            <input type="submit" name="commit" value="Delete Admin">
        </div>
    </form>

</div>

<?php include(SHARED_PATH . '/staff_footer.php'); ?>