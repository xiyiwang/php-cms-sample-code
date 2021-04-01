<?php 
    require_once('../../../private/initialize.php'); 

    require_login();

    $id = $_GET['id'];
    $admin = find_admin_by_id($id);
?>

<?php $page_title = 'Show Admin'; ?>
<?php include(SHARED_PATH . '/staff_header.php'); ?>

<div id="content">

    <a class="back-link" href="<?php echo url_for('/staff/admins/index.php') ?>">&laquo; Back to List</a>

    <div class="admin-show">
        
        <h1><?php echo h($admin['first_name']) . ' ' . h($admin['last_name']); ?></h1>

        <div class="attributes">
            <dl>
                <dt>First Name</dt>
                <dd><?php echo h($admin['first_name']); ?></dd>
            </dl>
            <dl>
                <dt>Last Name</dt>
                <dd><?php echo h($admin['last_name']); ?></dd>
            </dl>
            <dl>
                <dt>Email</dt>
                <dd><?php echo h($admin['email']); ?></dd>
            </dl>
            <dl>
                <dt>Username</dt>
                <dd><?php echo h($admin['username']); ?></dd>
            </dl>
        </div>

    </div>

</div>

<?php include(SHARED_PATH . '/staff_footer.php'); ?>