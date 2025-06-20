
<?php
	
	

  
?>

<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="index.php" class="sidebar-logo">
            <img src="assets/images/logo.png" alt="site logo" class="light-logo">
            <img src="assets/images/logo-light.png" alt="site logo" class="dark-logo">
            <img src="assets/images/logo-icon.png" alt="site logo" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            
            <li>
                <a href="index.php">
                    <iconify-icon icon="cuida:home-outline" class="menu-icon"></iconify-icon>
                    <span>Home</span>
                </a>
            </li>
            
            
            <li class="sidebar-menu-group-title">Client</li>
            
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="ic:twotone-event" class="menu-icon"></iconify-icon>
                    <span>Eventos</span>
                </a>
                <ul class="sidebar-submenu">
                    <li class="view">
                        <a href="Ver_eventos.php"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Ver eventos</a>
                    </li>
                    <li class="add">
                        <a href="view-event.php"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Nuevo evento</a>
                    </li>

                </ul>
            </li>
					
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mingcute:dish-cover-line" class="menu-icon"></iconify-icon>
                    <span>Platillos</span>
                </a>
                <ul class="sidebar-submenu">
                    <li class="view">
                        <a href="Ver_platillos.php"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Ver platillos</a>
                    </li>
                    <li class="add">
                        <a href="view-dish.php"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Nuevo platillo</a>
                    </li>

                </ul>
            </li>
			

            <li class="sidebar-menu-group-title">Externo</li>


            <li>
                <a href="comensales-list.php">
                    <iconify-icon icon="lsicon:user-crowd-outline" class="menu-icon"></iconify-icon>
                    <span>Comensales</span>
                </a>
            </li>
            
            <li class="sidebar-menu-group-title">Admin</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i class="ri-user-settings-line text-xl me-6 d-flex w-auto"></i>
                    <span>Usuarios</span>
                </a>
                <ul class="sidebar-submenu">
                    <li class="view">
                        <a href="users-list.php"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Users List</a>
                    </li>

                    <li class="admin_add">
                        <a href="view-profile.php?id="><i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Add User</a>
                    </li>

                    <li class="admin_view">
                        <a href="role-access.php"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Roles & Access</a>
                    </li>

                </ul>
                
                <li class="dropdown">
                    <a href="javascript:void(0)" class="">
                        <i class="ri-user-settings-line text-xl me-6 d-flex w-auto"></i>
                        <span>Billing</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="">
                            <a href="memberships.php"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Memberships</a>
                        </li>
                </li>



                </ul>
                
                
            </li>
			
			
        </ul>
    </div>
</aside>

