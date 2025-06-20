<?php $script ='<script>
    // ======================== Upload Image Start =====================
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#imagePreview").css("background-image", "url(" + e.target.result + ")");
                $("#imagePreview").hide();
                $("#imagePreview").fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#imageUpload").change(function() {
        readURL(this);
    });
    // ======================== Upload Image End =====================

    // ================== Password Show Hide Js Start ==========
    function initializePasswordToggle(toggleSelector) {
        $(toggleSelector).on("click", function() {
            $(this).toggleClass("ri-eye-off-line");
            var input = $($(this).attr("data-toggle"));
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }
    // Call the function
    initializePasswordToggle(".toggle-password");
    // ========================= Password Show Hide Js End ===========================
    
    
    $("#copyButton").on("click", function() {
        // Get the input field
        var inputField = $("#link");
        
        // Select the text in the input field
        inputField.select();
        inputField[0].setSelectionRange(0, 99999); // For mobile devices
        
        // Copy the text to the clipboard
        document.execCommand("copy");
        
        // Optional: Provide feedback to the user
        alert("Link copied to clipboard: " + inputField.val());
    });
    
    
    </script>';?>


    
<?php include './partials/layouts/layoutTop.php' ?>

<?php 	
    //error_reporting(E_ALL);
	//ini_set('display_errors', 1);
    
    
    //session_start(); 
    
    include_once './PHP/MYF1.php';
    
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        //echo "Name: " . htmlspecialchars($name); // Use htmlspecialchars to prevent XSS
    }else{
        //$id = $_SESSION['USER']['ID'];
    }
    
	$q = sprintf("SELECT * FROM Events WHERE id = '%s'   ", $id );  // echo $q . "\n";
	$R1 = SQL_2_OBJ_V2($q);
	//print_r($R1);die;
	
	$DB_EVENTO 	= $R1['PL'][0];
    
	$q = sprintf("SELECT * FROM Dishes " );  // echo $q . "\n";
	$R1 = SQL_2_OBJ_V2($q);
	//print_r($R1);die;
	
	$DB_DISH_CATALOGO = $R1['PL']; //print_r($DB_DISH_CATALOGO);die;
	
	// DISHES OPTIONS 
	
		$OPTIONS = sprintf('<option value=""></option>');
		
		foreach($DB_DISH_CATALOGO as $DISH){ // RECORRE CADA PLATILLO DEL CATALOGO
			$OPTIONS .= sprintf('<option value="%s">%s</option>', $DISH['EDIT_ID'], $DISH['NAME']);
		}
	
    // COUNTRY CODE 
    
    if ( !isset($DB_EVENTO['COUNTRY_CODE']) ) { $DB_EVENTO['COUNTRY_CODE'] = '+52';}
    
?>



        <div class="dashboard-main-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <h6 class="fw-semibold mb-0">View Profile</h6>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li>-</li>
                    <li class="fw-medium">View Profile</li>
                </ul>
            </div>

            <div class="row gy-4">
                <div class="col-lg-4">
                    <div class="user-grid-card position-relative border radius-16 overflow-hidden bg-base h-100">
                        <img src="IMAGES/EVENT_RIBON2.jpg" alt="" class="w-100 object-fit-cover">
                        <div class="pb-24 ms-16 mb-24 me-16  mt--0">
                            <div class="text-center border border-top-0 border-start-0 border-end-0" >
                                <img src="assets/images/user-grid/user-grid-img14.png" alt="" class="border br-white border-width-2-px w-200-px h-200-px rounded-circle object-fit-cover" hidden>
                                <h6 class="mb-0 mt-16"><?php echo $DB_EVENTO['NAME']; ?></h6>
                               
                            </div>
                            <div class="mt-24">
                                <h6 class="text-xl mb-16">Datos del evento</h6>
                                <ul>
                                    <li class="d-flex align-items-center gap-1 mb-12">
                                        <span class="w-30 text-md fw-semibold text-primary-light">Nombre</span>
                                        <span class="w-70 text-secondary-light fw-medium">: <?php echo $DB_EVENTO['NAME']; ?></span>
                                    </li>
                                    <li class="d-flex align-items-center gap-1 mb-12">
                                        <span class="w-30 text-md fw-semibold text-primary-light">Comensales</span>
                                        <span class="w-70 text-secondary-light fw-medium">: <?php echo $DB_EVENTO['COMENSALES']; ?></span>
                                    </li>
                                    <li class="d-flex align-items-center gap-1 mb-12">
                                        <span class="w-30 text-md fw-semibold text-primary-light"> Fecha</span>
                                        <span class="w-70 text-secondary-light fw-medium">: <?php echo $DB_EVENTO['FECHA']; ?></span>
                                    </li>
                                    <li class="d-flex align-items-center gap-1 mb-12">
                                        <span class="w-30 text-md fw-semibold text-primary-light"> Teléfono cliente</span>
                                        <span class="w-70 text-secondary-light fw-medium">: <?php echo $DB_EVENTO['PHONE']; ?></span>
                                    </li>
                                    <li class="d-flex align-items-center gap-1 mb-12">
                                        <span class="w-30 text-md fw-semibold text-primary-light"> Link</span>
                                        <span class="w-70 text-secondary-light fw-medium">: <a target="_blank" href="<?php echo $DB_EVENTO['LINK']; ?>"><?php echo $DB_EVENTO['LINK']; ?></a></span>
                                    </li>

                                    <li class="d-flex align-items-center gap-1">
                                        <span class="w-30 text-md fw-semibold text-primary-light"> Notas</span>
                                        <span class="w-70 text-secondary-light fw-medium">: <?php echo $DB_EVENTO['NOTAS']; ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-body p-24">
                            <ul class="nav border-gradient-tab nav-pills mb-20 d-inline-flex" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center px-24 active" id="pills-edit-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-edit-profile" type="button" role="tab" aria-controls="pills-edit-profile" aria-selected="true">
                                        Nuevo evento
                                    </button>
                                </li>
                                <li class="nav-item d-none" role="presentation">
                                    <button class="nav-link d-flex align-items-center px-24" id="pills-change-passwork-tab" data-bs-toggle="pill" data-bs-target="#pills-change-passwork" type="button" role="tab" aria-controls="pills-change-passwork" aria-selected="false" tabindex="-1">
                                        Change Password
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center px-24 d-none" id="pills-notification-tab" data-bs-toggle="pill" data-bs-target="#pills-notification" type="button" role="tab" aria-controls="pills-notification" aria-selected="false" tabindex="-1">
                                        Notification Settings
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-edit-profile" role="tabpanel" aria-labelledby="pills-edit-profile-tab" tabindex="0">
                                    <h6 class="text-md text-primary-light mb-16" hidden>Profile Image</h6>
                                    <!-- Upload Image Start -->
                                    <div class="mb-24 mt-16" hidden>
                                        <div class="avatar-upload">
                                            <div class="avatar-edit position-absolute bottom-0 end-0 me-24 mt-16 z-1 cursor-pointer">
                                                <input type='file' id="imageUpload" accept=".png, .jpg, .jpeg" hidden>
                                                <label for="imageUpload" class="w-32-px h-32-px d-flex justify-content-center align-items-center bg-primary-50 text-primary-600 border border-primary-600 bg-hover-primary-100 text-lg rounded-circle">
                                                    <iconify-icon icon="solar:camera-outline" class="icon"></iconify-icon>
                                                </label>
                                            </div>
                                            <div class="avatar-preview">
                                                <div id="imagePreview">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Upload Image End -->
                                    <form id="FRM_PROFILE" action="#" FRM="FRM_PROFILE" DB="Events">
                                    <input type="text" name="EDIT_ID" value="<?php echo $DB_EVENTO['ID']; ?>" readonly>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="mb-20">
                                                    <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8">Nombre <span class="text-danger-600">*</span></label>
                                                    <input type="text" class="form-control radius-8" id="name" placeholder="Nombre del evento"  name="NAME"  value="<?php echo $DB_EVENTO['NAME']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-20">
                                                    <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8">Comansales <span class="text-danger-600">*</span></label>
                                                    <input type="number" class="form-control radius-8" id="comensales" placeholder="Número de comensales"  name="COMENSALES"  value="<?php echo $DB_EVENTO['COMENSALES']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-20">
                                                    <label for="date" class="form-label fw-semibold text-primary-light text-sm mb-8">Fecha <span class="text-danger-600">*</span></label>
                                                    <input type="date" class="form-control radius-8" id="fecha" placeholder="Fecha del evento" name="FECHA" value="<?php echo $DB_EVENTO['FECHA']; ?>" required>
                                                </div>
                                            </div>

                                            
                                            <div class="col-sm-6">
                                                <div class="mb-20">
                                                     <label for="date" class="form-label fw-semibold text-primary-light text-sm mb-8">Telefono de contacto <span class="text-danger-600"></span></label>
                                                    <div class="form-mobile-field">
                                                        <select class="form-select" name="COUNTRY_CODE">
                                                            <option value="<?php echo $DB_EVENTO['COUNTRY_CODE']; ?>" selected><?php echo $DB_EVENTO['COUNTRY_CODE']; ?></option>
                                                            <option value="+52">MEX +52</option>
                                                            <option value="+1">USA +1</option>
                                                            <option value="+1">CAN +1</option>
                                                            <option value="+54">ARG +54</option>
                                                            <option value="+61">AUS +61</option>
                                                            <option value="+43">AUT +43</option>
                                                            <option value="+32">BEL +32</option>
                                                            <option value="+55">BRA +55</option>
                                                            <option value="+359">BGR +359</option>
                                                            <option value="+226">BFA +226</option>
                                                            <option value="+257">BDI +257</option>
                                                            <option value="+855">KHM +855</option>
                                                            <option value="+1">CIV +225</option>
                                                            <option value="+56">CHL +56</option>
                                                            <option value="+86">CHN +86</option>
                                                            <option value="+57">COL +57</option>
                                                            <option value="+506">CRI +506</option>
                                                            <option value="+45">DNK +45</option>
                                                            <option value="+20">EGY +20</option>
                                                            <option value="+251">ETH +251</option>
                                                            <option value="+358">FIN +358</option>
                                                            <option value="+33">FRA +33</option>
                                                            <option value="+49">DEU +49</option>
                                                            <option value="+30">GRC +30</option>
                                                            <option value="+995">GEO +995</option>
                                                            <option value="+49">DEU +49</option>
                                                            <option value="+233">GHA +233</option>
                                                            <option value="+350">GIB +350</option>
                                                            <option value="+30">GRC +30</option>
                                                            <option value="+220">GMB +220</option>
                                                            <option value="+45">DNK +45</option>
                                                            <option value="+502">GTM +502</option>
                                                            <option value="+504">HND +504</option>
                                                            <option value="+36">HUN +36</option>
                                                            <option value="+354">ISL +354</option>
                                                            <option value="+91">IND +91</option>
                                                            <option value="+62">IDN +62</option>
                                                            <option value="+353">IRL +353</option>
                                                            <option value="+972">ISR +972</option>
                                                            <option value="+39">ITA +39</option>
                                                            <option value="+81">JPN +81</option>
                                                            <option value="+962">JOR +962</option>
                                                            <option value="+254">KEN +254</option>
                                                            <option value="+996">KGZ +996</option>
                                                            <option value="+855">KHM +855</option>
                                                            <option value="+965">KWT +965</option>
                                                            <option value="+371">LVA +371</option>
                                                            <option value="+961">LBN +961</option>
                                                            <option value="+266">LSO +266</option>
                                                            <option value="+231">LBR +231</option>
                                                            <option value="+218">LBY +218</option>
                                                            <option value="+423">LIE +423</option>
                                                            <option value="+370">LTU +370</option>
                                                            <option value="+352">LUX +352</option>
                                                            <option value="+261">MDG +261</option>
                                                            <option value="+265">MWI +265</option>
                                                            <option value="+60">MYS +60</option>
                                                            <option value="+52">MEX +52</option>
                                                            <option value="+230">MUS +230</option>
                                                            <option value="+264">NAM +264</option>
                                                            <option value="+977">NPL +977</option>
                                                            <option value="+31">NLD +31</option>
                                                            <option value="+64">NZL +64</option>
                                                            <option value="+47">NOR +47</option>
                                                            <option value="+92">PAK +92</option>
                                                            <option value="+507">PAN +507</option>
                                                            <option value="+51">PER +51</option>
                                                            <option value="+63">PHL +63</option>
                                                            <option value="+48">POL +48</option>
                                                            <option value="+351">PRT +351</option>
                                                            <option value="+1">CAN +1</option>
                                                            <option value="+40">ROU +40</option>
                                                            <option value="+7">RUS +7</option>
                                                            <option value="+250">RWA +250</option>
                                                            <option value="+1">USA +1</option>
                                                            <option value="+421">SVK +421</option>
                                                            <option value="+386">SVN +386</option>
                                                            <option value="+65">SGP +65</option>
                                                            <option value="+221">SEN +221</option>
                                                            <option value="+232">SLE +232</option>
                                                            <option value="+421">SVK +421</option>
                                                            <option value="+386">SVN +386</option>
                                                            <option value="+46">SWE +46</option>
                                                            <option value="+41">CHE +41</option>
                                                            <option value="+66">THA +66</option>
                                                            <option value="+228">TGO +228</option>
                                                            <option value="+993">TKM +993</option>
                                                            <option value="+992">TJK +992</option>
                                                            <option value="+256">UGA +256</option>
                                                            <option value="+380">UKR +380</option>
                                                            <option value="+971">ARE +971</option>
                                                            <option value="+256">UGA +256</option>
                                                            <option value="+255">TZA +255</option>
                                                            <option value="+678">VUT +678</option>
                                                            <option value="+58">VEN +58</option>
                                                            <option value="+84">VNM +84</option>
                                                            <option value="+681">WLF +681</option>
                                                            <option value="+967">YEM +967</option>
                                                            <option value="+260">ZMB +260</option>
                                                            <option value="+263">ZWE +263</option>
                                                            <!-- Add more countries as needed -->
                                                        </select>
                                                        <input type="text" name="PHONE" class="form-control text-center" placeholder="10 digitos" value="<?php echo $DB_EVENTO['PHONE']; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            
                                            <div class="col-sm-12">
                                                <div class="mb-20">
                                                    <label for="number" class="form-label fw-semibold text-primary-light text-sm mb-8">Link</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-base"> https:// </span>
                                                        <input type="text" class="form-control radius-8" id="link" placeholder="Link se genera al guardar el evento." name="LINK" value="<?php echo $DB_EVENTO['LINK']; ?>"  readonly>
                                                        <button id="copyButton" type="button" class="input-group-text bg-base">
                                                            <iconify-icon icon="lucide:copy"></iconify-icon> Copy
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-sm-12">
                                                <div class="mb-20">
                                                    <label for="desc" class="form-label fw-semibold text-primary-light text-sm mb-8">Notas</label>
                                                    <textarea class="form-control radius-8" id="desc" placeholder="Write description..." name="NOTAS" ><?php echo $DB_EVENTO['NOTAS']; ?></textarea>
                                                </div>
                                            </div>
											
											

											<div id="COURSES" class="row">

												<?php	echo GENERAR_EVENT_COURSES($DB_EVENTO, $DB_DISH_CATALOGO); ?>


                                             </div>

                                             <div class="row">
                                                <div class="d-flex align-items-center justify-content-center gap-3 mb-12">
                                                    <button type="button" onclick="AGREGAR_TIEMPO();" class="border border-success-600 bg-hover-success-200 text-success-600 text-md px-56 py-11 radius-8">
                                                        Agregar tiempo
                                                    </button>

                                                </div>


                                                <div class="d-flex align-items-center justify-content-center gap-3 mt-12">
                                                    <a href="./Ver_eventos.php">
                                                        <button type="button"  class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8">
                                                            Regresar
                                                        </button>
                                                    </a>
                                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8" onclick="" >
                                                        Save
                                                    </button>
                                                </div>
                                            </div>

                                        </div>

                                    </form>
                                



                                <div class="tab-pane fade" id="pills-notification" role="tabpanel" aria-labelledby="pills-notification-tab" tabindex="0">
                                    <div class="form-switch switch-primary py-12 px-16 border radius-8 position-relative mb-16">
                                        <label for="companzNew" class="position-absolute w-100 h-100 start-0 top-0"></label>
                                        <div class="d-flex align-items-center gap-3 justify-content-between">
                                            <span class="form-check-label line-height-1 fw-medium text-secondary-light">Company News</span>
                                            <input class="form-check-input" type="checkbox" role="switch" id="companzNew">
                                        </div>
                                    </div>
                                    <div class="form-switch switch-primary py-12 px-16 border radius-8 position-relative mb-16">
                                        <label for="pushNotifcation" class="position-absolute w-100 h-100 start-0 top-0"></label>
                                        <div class="d-flex align-items-center gap-3 justify-content-between">
                                            <span class="form-check-label line-height-1 fw-medium text-secondary-light">Push Notification</span>
                                            <input class="form-check-input" type="checkbox" role="switch" id="pushNotifcation" checked>
                                        </div>
                                    </div>
                                    <div class="form-switch switch-primary py-12 px-16 border radius-8 position-relative mb-16">
                                        <label for="weeklyLetters" class="position-absolute w-100 h-100 start-0 top-0"></label>
                                        <div class="d-flex align-items-center gap-3 justify-content-between">
                                            <span class="form-check-label line-height-1 fw-medium text-secondary-light">Weekly News Letters</span>
                                            <input class="form-check-input" type="checkbox" role="switch" id="weeklyLetters" checked>
                                        </div>
                                    </div>
                                    <div class="form-switch switch-primary py-12 px-16 border radius-8 position-relative mb-16">
                                        <label for="meetUp" class="position-absolute w-100 h-100 start-0 top-0"></label>
                                        <div class="d-flex align-items-center gap-3 justify-content-between">
                                            <span class="form-check-label line-height-1 fw-medium text-secondary-light">Meetups Near you</span>
                                            <input class="form-check-input" type="checkbox" role="switch" id="meetUp">
                                        </div>
                                    </div>
                                    <div class="form-switch switch-primary py-12 px-16 border radius-8 position-relative mb-16">
                                        <label for="orderNotification" class="position-absolute w-100 h-100 start-0 top-0"></label>
                                        <div class="d-flex align-items-center gap-3 justify-content-between">
                                            <span class="form-check-label line-height-1 fw-medium text-secondary-light">Orders Notifications</span>
                                            <input class="form-check-input" type="checkbox" role="switch" id="orderNotification" checked>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php include './partials/layouts/layoutBottom.php' ?>

<script>	
	$(document).ready(function() {
    
        hideAlertAfterTime($('.alert'), 5000);
        
        
        
        $(document).on('submit','#FRM_PROFILE', function VALIDAR(e) { 
        //$('#formulario').on('submit', function (e) {
          if (e.isDefaultPrevented()) {
            // handle the invalid form...
          } else {
            // everything looks good!
            e.preventDefault(); //prevent submit
            SAVE_CHANGES(this);
            
          }
        });
        
        $(document).on('submit','#FRM_PROFILE_PWD', function VALIDAR(e) { 
        //$('#formulario').on('submit', function (e) {
          if (e.isDefaultPrevented()) {
            // handle the invalid form...
          } else {
            // everything looks good!
            e.preventDefault(); //prevent submit
            var PWD = $('input[name="PWD"]').val();
            var PWD2 = $('input[name="PWD2"]').val();
            //$('input[name="PWD"]').val(PWD1);
            
            debugger;
            if ( PWD == PWD2){
                SAVE_CHANGES(this);
            }
          }
        });
        
            
    });

    function AGREGAR_TIEMPO(){

        var count = $('div#COURSE').length;

        var OBJ = {};
        OBJ['idx'] = count + 1;
        var result = POST_API(OBJ, '_ELEMENTS/EVENTS/new_course/');
        debugger;

        $("div#COURSES").append(result.DATOS);
    }

    $(document).on('click',"#AGREGAR_TIEMPO", function AGREGAR_TIEMPO(){

        //$("#FRM_EVENT input").val("");
    });

    $(document).on('click',".remove-item-btn", function REMOVE(){

        $(this).closest("#COURSE").remove();
    });

    $(document).on('change','input[name="EDIT_ID"]', function LINK(){
        
        var id = $('input[name="EDIT_ID"]').val();
        debugger;
        $('input[name="LINK"]').val( 'https://salypimientabanquetes.com/IO3/?e_id=' + id );
        debugger;
        
    });


</script>