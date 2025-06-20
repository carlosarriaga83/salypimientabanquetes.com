

<?php 
	
	session_start();

	if ($_SESSION['LOGIN'] == 1){
		//header("Location: my-events.php");	
	}else{
		header("Location: sign-in.php");	
	}

	include_once './PHP/MYF1.php';
	
	header('Content-type: html; charset=utf-8');
	
?>
	
<?php include './partials/layouts/layoutTop.php' ?>

<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Hi <?php echo $_SESSION['USER']['NAME'] . '!'; ?></h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Home
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Blank Page</li>
			
        </ul>
    </div>
	
	<?php //print_r($_SESSION); ?>
</div>
<?php include './partials/layouts/layoutBottom.php' ?>