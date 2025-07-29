<aside class="sidebar">
    <div class="logo-container">
        <a href="sec_dashboard.php" class="logo">
            <img src="../../assets/images/weblogo.png" alt="Web Logo" style="max-height: 50px; width: auto;"> 
        </a>
        <button class="toggle-btn" id="toggleSidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <ul class="nav-items">
        <li class="nav-item">
            <a href="midwife_dashboard.php" class="nav-link"> 
                <i class="fas fa-home"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="patient_list.php" class="nav-link">
                <i class="fa fa-user"></i>
                <span class="nav-text">Patients</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="appointment_history.php" class="nav-link">
                <i class="fa-solid fa-list"></i>
                <span class="nav-text">Appointment History</span>
            </a>
        </li>
    </ul> 

    <div class="user-profile">
        <a href="change_pass.php" class="avatar" style="text-decoration: none; color: white;">JD</a>
        <div class="user-info">
            <div class="user-name">John Doe</div>
            <div class="user-role">Secretary</div>
        </div>
    </div>

    <div class="logout-container" style="padding: 20px;">
        <a class="logout-btn nav-link" href="logout.php" onclick="return confirm('Logout from this website?');" style="color: #ff6b6b; text-decoration: none; display: flex; align-items: center; gap: 15px;"> 
            <i class="fas fa-sign-out-alt"></i>
            <span class="logout-text">Logout</span>
        </a>
    </div>

</aside>