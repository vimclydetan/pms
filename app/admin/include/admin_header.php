<header class="header">

    <section class="flex">
        <a class="logo">Grateful Beginnings Medical Clinic and Lying-in</a>

        <div class="head-icons"> 
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
        </div>

        <div class="drop"> 
            <a href="change_pass.php" class="btn">Change Pass</a>
            <a href="logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">Logout</a>
        </div>
    </section>

</header>


<div class="side-bar">
    <div class="side-image">  
        <img src="../../assets/images/weblogo.png" alt="Admin Profile">
    </div>

    <nav class="navbar">
        <a href="admin_dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>

        <li class="dropdown">
            <a href="#"><i class="fa fa-user"></i><span>Doctors</span></a>
            <ul class="dropdown-menu">
                <li><a href="add_doctor.php">Add Doctor</a></li>
                <li><a href="doctor_list.php">Manage Doctors</a></li>
            </ul>
        </li>

        <li class="dropdown">
            <a href="user_logs.php"><i class="fa fa-user"></i><span>Secretary</span></a>
            <ul class="dropdown-menu">
                <li><a href="add_sec.php">Add Secretary</a></li>
                <li><a href="sec_list.php">Manage Secretary</a></li>
            </ul>
        </li>
        
        <a href="reg_patients_list.php"><i class="fas fa-user"></i><span>Registered Patients</span></a>

        <a href="user_logs.php"><i class="fas fa-history"></i><span>User Logs</span></a>

        <a href="announcement_form.php"><i class="fas fa-bullhorn"></i><span>Add Announcement</span></a>
    </nav>
</div>
