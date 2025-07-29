<header class="header">

    <section class="flex">
        <a class="logo">Grateful Beginnings Medical Clinic and Lying-in</a>

        <div class="head-icons"> 
            <div id="menu-btn" class="fas fa-bars toggle-btn"></div>
            <div id="user-btn" class="fas fa-user"></div>
        </div>

        <div class="drop"> 
            <a href="edit_profile.php" class="btn">Change Password</a>
            <a href="logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">Logout</a>
        </div>
    </section>

</header>

<div class="side-bar">
    <div class="side-image">  
        <img src="../assets/images/weblogo.png" alt="Profile">
    </div>

    <nav class="navbar">
        <a href="dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>

        <a href="appointment_form.php"><i class="fas fa-edit"></i><span>Book an appointment</span></a>

        <a href="appointment_history.php"><i class="fa-solid fa-list"></i><span>Appointment History</span></a>

        <a href="view_patient.php"><i class="fa-solid fa-file"></i></i><span>Medical Record</span></a>
    </nav>
</div>