<?php $script ='<script>
                        $(".remove-item-btn").on("click", function() {
						//$(this).closest("tr").addClass("d-none")
                        });
						
						//let table = new DataTable("#TBL_DISHES");
						
            </script>';?>



<?php
	
	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);
	
	session_start();
	
	//if ($_SESSION['LOGIN'] != 1){ header("Location: index.php");	}
	
	include_once './PHP/MYF1.php';
	
	//echo GET_TS();die;
	//require_once '/vendor/autoload.php';
	
	// EVENTOS 
	
	$q = sprintf("SELECT * FROM u124132715_SYP.Events");  //echo $q . "\n";
	$R1 = SQL_2_OBJ_V2($q);
	$RESP['R1'] = $R1; //print_r($R1);
	$EVENTOS = $R1['DATA'];
	
	//echo arrayToHtmlTable($EVENTOS, ['ID','NAME','COMENSALES','FECHA','EVENT_LINK']);
	
	//print_r($EVENTOS);die;

	
	header('Content-type: html; charset=utf-8');
?>


<?php include './partials/layouts/layoutTop.php' ?>

        <div class="dashboard-main-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <h6 class="fw-semibold mb-0">Platillos</h6>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Home
                        </a>
                    </li>
                    <li>-</li>
                    <li class="fw-medium">Platillos</li>

                </ul>
            </div>
								
								
								<?php 
									$T_PARAMS 					= [];
									$T_PARAMS['DB']['TABLE'] 	= 'Dishes';
									$T_PARAMS['DB']['COLS'] 	= [ 'NAME', 'DESCRIPCION', 'DISH_PIC', 'OPT_VEGETARIANO', 'OPT_VEGANO', 'Action'];
									
									$T_PARAMS['TABLE']['ID'] 	= 'TBL_DISHES';
									$T_PARAMS['TABLE']['COLS'] 	= [ 'Nombre', 'Descripcion', 'Foto', 'Vegetariano', 'Vegano', 'Action'];
									$T_PARAMS['TABLE']['href'] 	= 'view-dish.php';

									$T_PARAMS['TABLE']['SEARCH_BAR']	 	= true;
									$T_PARAMS['TABLE']['ADD_NEW']['TEXT'] 	= 'Add new';
									$T_PARAMS['TABLE']['ADD_NEW']['href'] 	= 'view-dish.php';
									

									//echo DB_2_TABLE_V3($T_PARAMS ); 

									include './_ELEMENTS/DISHES/LIST/index.php';
									
								?>


        </div>



		
<?php include './partials/layouts/layoutBottom.php' ?>