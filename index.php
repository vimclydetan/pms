<?php
include 'config.php';
session_start();
include 'app/include/connect.php';
date_default_timezone_set('Asia/Manila'); // Adjust based on your location

$announcement = null;
$currentDateTime = date('Y-m-d H:i:s');

$query = "SELECT * FROM announcements 
          WHERE start_date <= ? 
          AND CONCAT(end_date, ' ', end_time) >= ?
          ORDER BY id DESC LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $currentDateTime, $currentDateTime);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $announcement = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Grateful Beginnings Medical Clinic & Lying In - Compassionate healthcare services in Calamba City, Laguna.">
    <meta name="theme-color" content="#e2b6d0">
    <link rel="canonical" href="https://www.gratefulbeginnings.com">
    <title>Grateful Beginnings Medical CLinic and Lying-in</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
   

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "MedicalOrganization",
            "name": "Grateful Beginnings Medical Clinic",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "National Highway, Halang",
                "addressLocality": "Calamba City",
                "addressRegion": "Laguna",
                "postalCode": "4027",
                "addressCountry": "Philippines"
            },
            "telephone": "09325490344",
            "url": "https://www.gratefulbeginnings.com",
            "logo": "assets/images/log-removebg-preview.png",
            "sameAs": [
                "https://www.facebook.com/gratefulbeginnings",
                "https://twitter.com/gratefulbegins",
                "https://www.instagram.com/gratefulbeginningsclinic"
            ]
        }
    </script>
    <script>
        AOS.init();
    </script>

    <style>
        /* === Responsive Typography === */
h1, .hero-section h1 {
    font-size: clamp(1.8rem, 4vw, 3.5rem);
    line-height: 1.2;
}

h2 {
    font-size: clamp(1.5rem, 3.5vw, 2.5rem);
}

p, .lead {
    font-size: clamp(0.9rem, 2.8vw, 1.1rem);
    line-height: 1.6;
}

/* === Container: Fluid Max-Width === */
.container {
    width: 100%;
    padding-right: 1rem;
    padding-left: 1rem;
    margin-right: auto;
    margin-left: auto;
}

@media (min-width: 576px) {
    .container {
        max-width: 540px;
    }
}

@media (min-width: 768px) {
    .container {
        max-width: 720px;
    }
}

@media (min-width: 992px) {
    .container {
        max-width: 960px;
    }
}

@media (min-width: 1200px) {
    .container {
        max-width: 1140px;
    }
}

@media (min-width: 1400px) {
    .container {
        max-width: 1320px;
    }
}
        body { 
            cursor: default; 
        }
        button, a, .nav-link { 
            cursor: pointer; 
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            background: url('assets/images/web-bg.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        section[id] {
            scroll-margin-top: 50px;
        }
        .overlay {
            background-color: rgba(255, 255, 255, 0.95);
            min-height: 100vh;
        }
        .hero-section .btn-outline-light {
            border: 2px solid white;
            color: white;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .hero-section .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)) no-repeat center center fixed;
            background-size: cover;
            text-align: center;
            padding: 6rem 1rem 4rem;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 0;
        }
        
        .hero-section h1 {
            position: relative;
            z-index: 1;
            background: linear-gradient(90deg, #fff, #ffdee9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.3),
                -1px -1px 2px rgba(0, 0, 0, 0.1);
            padding: 5px;        
            font-size: clamp(2rem, 8vw, 4rem);
            color: white;
            margin-bottom: 1rem;
        }

        .hero-section h1, .hero-section .lead {
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .hero-content .lead {
            font-size: clamp(1rem, 3.5vw, 1.3rem);
            color: #fff;
            margin-bottom: 2rem;
        }
        .hero-section img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* makes the image cover the container */
            z-index: 0;
            opacity: 0.3;
            /* optional, to make text more readable */
        }

        .hero-section .container {
            position: relative;
            z-index: 1;
        }
        #bookAppointmentBtn {
            padding: 12px 24px !important;
            font-size: 1rem;
            width: auto;
            min-width: 200px;
        }
        @media (max-width: 576px) {
            #bookAppointmentBtn {
                width: 100%;
                max-width: 300px;
            }
        }
        .btn-book-appointment {
            background: linear-gradient(45deg, #f57c7c, #ff9a9e);
            color: white !important;
            font-weight: 600;
            padding: 10px 15px !important;
            border: none;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(245, 124, 124, 0.3);
            transition: all 0.3s ease !important;
            letter-spacing: 0.5px;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-book-appointment::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
            z-index: -1;
        }

        .btn-book-appointment:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 7px 20px rgba(245, 124, 124, 0.4);
            background: linear-gradient(45deg, #e46d6d, #ff8a8e);
        }

        .btn-book-appointment:hover::before {
            left: 100%;
        }

        .btn-book-appointment i {
            font-size: 1.1em;
            transition: transform 0.3s ease;
        }

        .btn-book-appointment:hover i {
            transform: scale(1.2);
        }
        /* === Nav Links: Always #333 unless active === */
        .navbar-nav .nav-link {
            color: #333 !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            position: relative;
            transition: color 0.3s ease;
        }
        /* Hover: underline lang, kulay manatili #333 */
        .navbar-nav .nav-link:hover {
            color: #333 !important;
        }
        /* Underline effect (centered) */
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: #333;
            transition: width 0.3s ease;
            transform: translateX(-50%);
        }
        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 70%;
        }
  
        /* Active link: White text only */
        .navbar-nav .nav-link.active {
            color: #fff !important;
            font-weight: 600;
        }
        /* === Base Navbar === */
        .navbar-custom {
            background: linear-gradient(90deg, #e2b6d0 0%, #fce3ec 100%);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease; /* Huwag i-transition ang padding */
            min-height: 60px; /* 🔐 Fixed base height */
            padding: 0.8rem 0; /* Starting padding */
        }
        .service-card {
            background: linear-gradient(135deg, #fff 50%, #ffe9edff 100%);
            transition: transform 0.3s;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            height: 100%;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .service-card h5 {
            font-size: 1.2rem;
        }
        .service-card ul {
            font-size: 0.95rem;
        }
        @media (max-width: 768px) {
            .service-card {
                margin-bottom: 1.5rem;
            }
        }

        /* === MODAL STYLING === */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            margin: 2rem auto;
            overflow: hidden;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 20px;
            z-index: 10;
            transition: color 0.2s ease-in-out;
        }

        .close:hover {
            color: red;
        }

        /* Form Inputs */
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0.1rem 0.2rem #2a8ef849;
        }

        .form-label {
            color: #555;
            font-weight: 500;
        }

        /* Buttons */
        .btn-primary, .btn-success {
            border: none;
            font-size: 1.1rem;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .modal-content {
                margin: 1rem;
                max-width: 100%;
            }
        }

        .form-label::after {
            content: ' *';
            color: red;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .modal-content {
                margin: 1rem;
                max-width: 100%;
            }
            .modal-img {
                display: none;
            }
        }

        /* === Footer with Gray Gradient === */
        footer {
            background: linear-gradient(to right, #1c1c1c, #343a40);
            color: #e0e0e0;
            font-family: 'Segoe UI', sans-serif;
            padding: 3rem 0;
            font-size: 0.95rem;
        }

        /* Footer Title */
        .footer-title {
            color: #fff;
            font-weight: 600;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
            font-size: 1.2rem;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 2px;
            background: #e76ea3; /* Soft pink underline para hindi masyadong cold */
        }
        .footer-contact p, .footer-links li {
            margin-bottom: 0.7rem;
        }
        /* Footer Text */
        .footer-text {
            color: #bbb;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Contact Icons */
        .footer-contact p {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #ddd;
        }

        .footer-contact i {
            color: #e76ea3; /* Pink accent para hindi masyadong gray */
        }

        /* Quick Links */
        .footer-link {
            color: #ddd !important;
            text-decoration: none;
            font-size: 0.95rem;
            position: relative;
            transition: color 0.3s ease;
        }

        .footer-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: -2px;
            left: 0;
            background-color: #e76ea3; /* Pink underline */
            transition: width 0.3s ease;
        }

        .footer-link:hover {
            color: #fff !important;
        }

        .footer-link:hover::after {
            width: 100%;
        }

        /* Social Icons */
        .footer-social {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #2c2c2c;
            color: #ddd;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .footer-social:hover {
            background: #e76ea3;
            color: white;
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 6px 12px rgba(231, 110, 163, 0.4);
        }

        /* Divider */
        .footer-divider {
            border-color: #444;
            opacity: 0.4;
            margin: 2rem 0;
        }

        /* Copyright */
        .footer-copyright {
            color: #888;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .footer-custom {
                text-align: center;
            }
            .footer-links, .footer-contact {
                margin-bottom: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
        }
        @media (max-width: 768px) {
            .footer-custom .row > div {
                text-align: center;
            }
            .footer-links, .footer-contact {
                margin-bottom: 1.5rem;
            }
        }
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        #announcementModal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
    </style>
</head>

<body>
    <div class="overlay">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top navbar-custom shadow-sm" aria-label="Main Navigation">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="#">
                    <img src="assets/images/weblogo.png" alt="Grateful Beginnings Logo" width="40" class="me-2">
                    <span class="fw-bold">Grateful Beginnings</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    </ul>
                    <a href="#" id="bookAppointmentBtn" class="btn btn-book-appointment ms-lg-3 mt-2 mt-lg-0" role="button">
                        <i class="fas fa-calendar-check"></i> Book an Appointment
                    </a>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section d-flex align-items-center" aria-labelledby="clinic-heading">
            <img src="assets/images/web_bg.jpg" alt="">
            <div class="container text-white text-center">
                <h1 id="clinic-heading" class="display-4 mb-4 animate__animated animate__fadeInDown">Grateful Beginnings Medical Clinic & Lying In</h1>
                <p class="lead mb-5 animate__animated animate__fadeInUp">Your Trusted Partner for Medical Care and Maternity Services</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="#services" class="btn btn-outline-light btn-lg px-4">Our Services</a>

                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="py-5" aria-labelledby="services-heading">
            <div class="container">
                <h2 id="services-heading" class="text-center mb-5" data-aos="fade-up">Our Services</h2>
                <div class="row g-4">
                    <!-- Service Cards -->
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card service-card p-4 h-100 shadow-sm">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle p-3 me-3" aria-hidden="true">
                                    <i class="fas fa-baby text-white fa-lg" aria-hidden="true"></i>
                                </div>
                                <h5 class="card-title mb-0">Maternity Care</h5>
                            </div>
                            <p class="card-text mt-3">Comprehensive prenatal, delivery, and postpartum care with experienced medical professionals.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="card service-card p-4 h-100 shadow-sm">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle p-3 me-3" aria-hidden="true">
                                    <i class="fas fa-clinic-medical text-white fa-lg" aria-hidden="true"></i>
                                </div>
                                <h5 class="card-title mb-0">General Consultation</h5>
                            </div>
                            <p class="card-text mt-3">Expert medical consultations for patients of all ages with personalized treatment plans.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                        <div class="card service-card p-4 h-100 shadow-sm">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle p-3 me-3" aria-hidden="true">
                                    <i class="fas fa-stethoscope text-white fa-lg" aria-hidden="true"></i>
                                </div>
                                <h5 class="card-title mb-0">General Medical Services</h5>
                            </div>
                            <ul class="list-unstyled mt-3">
                                <li>Transvaginal Ultrasound</li>
                                <li>Pelvic Ultrasound</li>
                                <li>BPS Ultrasound with NST</li>
                                <li>BPS Ultrasound</li>
                                <li>Pap Smear</li>
                                <li>Pregnancy Test</li>
                                <li>IUD Insertion</li>
                                <li>Removal of IUD</li>
                                <li>Implant Insertion</li>
                                <li>Implant Removal</li>
                                <li>Flu Vaccine</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                        <div class="card service-card p-4 h-100 shadow-sm">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle p-3 me-3" aria-hidden="true">
                                    <i class="fas fa-syringe text-white fa-lg" aria-hidden="true"></i>
                                </div>
                                <h5 class="card-title mb-0">HPV Vaccines</h5>
                            </div>
                            <ul class="list-unstyled mt-3">
                                <li><strong>Cervarix</strong></li>
                                <li><strong>Gardasil 4</strong></li>
                                <li><strong>Gardasil 9</strong></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="600">
                        <div class="card service-card p-4 h-100 shadow-sm">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle p-3 me-3" aria-hidden="true">
                                    <i class="fas fa-medkit text-white fa-lg" aria-hidden="true"></i>
                                </div>
                                <h5 class="card-title mb-0">Family Planning & Other Services</h5>
                            </div>
                            <ul class="list-unstyled mt-3">
                                <li>DMPA (3 Months)</li>
                                <li>DMPA (Monthly)</li>
                                <li>Circumcision</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-5 bg-light" aria-labelledby="about-heading">
            <div class="container">
                <h2 id="about-heading" class="text-center mb-5">About Our Clinic</h2>
                <div class="row align-items-center">
                    <div class="col-md-6" data-aos="fade-right">
                        <img src="assets/images/lyingin.jpg" class="img-fluid rounded shadow-sm" alt="Grateful Beginnings Clinic Interior" loading="lazy">
                    </div>
                    <div class="col-md-6 mt-4 mt-md-0" data-aos="fade-left">
                        <p class="lead">Established in July 2018, Grateful Beginnings has been providing quality healthcare services to families in the community.</p>
                        <p>We combine modern medical technology with compassionate care to ensure the best outcomes for our patients. Our team of experienced OBGYNs, midwives, and support staff work together to provide comprehensive healthcare solutions tailored to your needs.</p>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-primary me-2" aria-hidden="true"></i>
                                <span>24/7 Emergency Services</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-primary me-2" aria-hidden="true"></i>
                                <span>Experienced Medical Team</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-primary me-2" aria-hidden="true"></i>
                                <span>Modern Facilities</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-primary me-2" aria-hidden="true"></i>
                                <span>Patient-Centered Approach</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-5 bg-light" aria-labelledby="contact-heading">
            <div class="container">
                <h2 id="contact-heading" class="text-center mb-5">Contact Us</h2>
                <div class="row">
                    <div class="col-md-6" data-aos="fade-right">
                        <p class="lead mb-4">Get in touch with our clinic for appointments and inquiries.</p>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-3">
                                <i class="fas fa-map-marker-alt text-primary me-2" aria-hidden="true"></i>
                                <span>National Highway, Halang, Calamba City Laguna <br>(Landmark: Puregold Calamba)</span>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-phone text-primary me-2" aria-hidden="true"></i>
                                <a href="tel:09325490344" class="text-black text-decoration-none">09325490344</a>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-clock text-primary me-2" aria-hidden="true"></i>
                                <span>Mon - Sat: 8:00 AM - 5:00 PM</span>
                            </li>
                        </ul>
                    </div>
                    <!-- Removed Contact Form -->
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer-custom py-5">
            <div class="container">
                <div class="row">
                    <!-- Clinic Info -->
                    <div class="col-md-4 mb-4">
                        <h5 class="footer-title">Grateful Beginnings</h5>
                        <p class="footer-text">
                            Providing compassionate healthcare services since 2018. 
                            Your family's health is our priority.
                        </p>
                        <div class="footer-contact">
                            <p><i class="fas fa-map-marker-alt text-primary me-2"></i> Brgy. San Isidro, City of San Fernando, Pampanga</p>
                            <p><i class="fas fa-phone-alt text-primary me-2"></i> (045) 123-4567</p>
                            <p><i class="fas fa-envelope text-primary me-2"></i> info@gratefulbeginnings.com</p>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="col-md-4 mb-4">
                        <h5 class="footer-title">Quick Links</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="#services" class="footer-link">Services</a></li>
                            <li><a href="#about" class="footer-link">About Us</a></li>
                            <li><a href="#contact" class="footer-link">Contact</a></li>
                        </ul>
                    </div>

                    <!-- Follow Us -->
                    <div class="col-md-4 mb-4">
                        <h5 class="footer-title">Follow Us</h5>
                        <p class="footer-text mb-3">Stay connected with our latest updates.</p>
                        <div class="d-flex gap-3">
                            <a href="https://www.facebook.com/gratefulbeginnings" class="footer-social" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/gratefulbegins" class="footer-social" aria-label="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.instagram.com/gratefulbeginningsclinic" class="footer-social" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <hr class="footer-divider">

                <div class="row">
                    <div class="col-12 text-center">
                        <p class="small mb-0 footer-copyright">
                            &copy; <?= date('Y') ?> Grateful Beginnings Medical Clinic. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div> <!-- End Overlay -->

    <!-- Login & Register Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="px-4 py-5">
                <!-- Login Form -->
                <form id="loginForm" action="app/login.php" method="POST" style="display: block;">
                    <h4 class="text-center mb-4" style="color: #007bff;">Welcome</h4>
                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <p class="mt-3 text-start">
                        <a href="#" id="forgotPasswordLink" style="color: #007bff; text-decoration: none; font-size: 1rem;">Forgot Password?</a>
                    </p>
                    <button type="submit" class="btn btn-primary w-100 py-2 mt-2" style="background-color: #007bff; border: none; border-radius: 50px; font-weight: 500;">
                        Login
                    </button>
                    <p class="mt-3 text-center">
                        Don't have an account? 
                        <a href="#" id="registerLink" style="color: #007bff; text-decoration: none; font-weight: 500; font-size: 0.8rem;">Register here</a>
                    </p>
                </form>

                <!-- Register Form -->
                <form id="registerForm" action="app/register.php" method="POST" style="display: none;">
                    <h4 class="text-center mb-4" style="color: #007bff;">Create Account</h4>
                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="regName" class="form-label">Full Name</label>
                        <input type="text" class="form-control form-control-lg" id="regName" name="name" placeholder="Surname, Firstname MI." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Province</label>
                        <select id="province" class="form-control form-control-lg" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City/Municipality</label>
                        <select id="city" class="form-control form-control-lg" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Barangay</label>
                        <select id="barangay" class="form-control form-control-lg" required></select>
                    </div>

                    <input type="hidden" name="province_name" id="province_name">
                    <input type="hidden" name="city_name" id="city_name">
                    <input type="hidden" name="barangay_name" id="barangay_name">

                    <div class="mb-3">
                        <label for="regContact" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control form-control-lg" id="regContact" name="contact" placeholder="Enter your contact number" required>
                    </div>
                    <div class="mb-3">
                        <label for="birthdate" class="form-label">Birthdate</label>
                        <input type="date" class="form-control form-control-lg" id="birthdate" name="birthdate" required>
                    </div>
                    <input type="hidden" name="age" id="age">
                    <div class="mb-3">
                        <label for="regEmail" class="form-label">Email</label>
                        <input type="email" class="form-control form-control-lg" id="regEmail" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="regPassword" class="form-label">Password</label>
                        <input type="password" class="form-control form-control-lg" id="regPassword" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="mb-3">
                        <label for="regConfirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control form-control-lg" id="regConfirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 py-2 mt-2" style="background: linear-gradient(45deg, #8bc34a, #a5d6a7); border: none; border-radius: 50px; font-weight: 500;">
                        Register
                    </button>
                    <p class="mt-3 text-center">
                        Already have an account? 
                        <a href="#" id="loginLink" style="color: #007bff; text-decoration: none; font-weight: 500;">Login here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <span class="close">&times;</span>
            <div class="p-4">
                <h4 class="text-center mb-4" style="color: #007bff;">Reset Password</h4>
                <form id="forgotPasswordForm" action="app/forgot_password.php" method="POST">
                    <div class="mb-3">
                        <label for="forgotEmail" class="form-label">Email</label>
                        <input type="email" class="form-control form-control-lg" id="forgotEmail" name="email" placeholder="Enter your email" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2" style="background: linear-gradient(45deg, #e2b6d0, #ff9a9e); border: none; border-radius: 50px; font-weight: 500;">
                        Send Reset Link
                    </button>
                </form>
                <p class="mt-3 text-center">
                    <a href="#" id="backToLoginLink" style="color: #007bff; text-decoration: none;">Back to Login</a>
                </p>
            </div>
        </div>
    </div>

    <?php if ($announcement): ?>
        <!-- Announcement Modal -->
        <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header text-white" style="background-color: #f5bcba;">
                        <h5 class="modal-title text-center w-100" id="announcementModalLabel">Announcement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="background-color:rgb(241, 224, 223);">
                        <p><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></p>
                        <?php if (!empty($announcement['image'])): ?>
                            <img src="<?php echo htmlspecialchars($announcement['image']); ?>" class="img-fluid mt-3 d-block mx-auto" alt="Announcement Image">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
    <?php if ($announcement): ?>
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('announcementModal'));
                myModal.show();
            });
        </script>
    <?php endif; ?>
    <script>
        // Get modal elements
        const loginModal = document.getElementById('loginModal');
        const bookAppointmentBtn = document.getElementById('bookAppointmentBtn');
        const closeBtns = document.querySelectorAll('.close');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const forgotPasswordModal = document.getElementById('forgotPasswordModal');

        // Links
        const registerLink = document.getElementById('registerLink');
        const loginLink = document.getElementById('loginLink');
        const forgotPasswordLink = document.getElementById('forgotPasswordLink');
        const backToLoginLink = document.getElementById('backToLoginLink');

        // Open Login Modal
        bookAppointmentBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.style.display = 'block';
        });

        // Close modals
        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                btn.closest('.modal').style.display = 'none';
            });
        });

        // Close when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === loginModal) loginModal.style.display = 'none';
            if (e.target === forgotPasswordModal) forgotPasswordModal.style.display = 'none';
        });

        // Switch to Register
        registerLink?.addEventListener('click', (e) => {
            e.preventDefault();
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        });

        // Switch to Login
        loginLink?.addEventListener('click', (e) => {
            e.preventDefault();
            registerForm.style.display = 'none';
            loginForm.style.display = 'block';
        });

        // Open Forgot Password
        forgotPasswordLink?.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.style.display = 'none';
            forgotPasswordModal.style.display = 'block';
        });

        // Back to Login
        backToLoginLink?.addEventListener('click', (e) => {
            e.preventDefault();
            forgotPasswordModal.style.display = 'none';
            loginModal.style.display = 'block';
        });
        document.getElementById('birthdate').addEventListener('change', function() {
            const birthdate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthdate.getFullYear();
            const monthDiff = today.getMonth() - birthdate.getMonth();

            // Adjust age if birthday hasn't occurred yet this year
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                age--;
            }

            // Update hidden field and display age
            document.getElementById('age').value = age;
        });

        // Show announcement modal if there's an active announcement
    </script>
    <a href="#" class="btn btn-primary position-fixed" style="bottom: 30px; right: 30px; display: none;" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>
    <script>
        const backToTop = document.getElementById("backToTop");
        window.addEventListener("scroll", () => {
            backToTop.style.display = window.scrollY > 300 ? "block" : "none";
        });

        // Update active nav link based on scroll
        const navLinks = document.querySelectorAll('.nav-link');
        const sections = document.querySelectorAll('section[id]');
        const navbar = document.querySelector('.navbar-custom');
        const initialPadding = 0.8; // rem (≈ 80px height)
        const finalPadding = 0.5;   // rem (≈ 60px height)
        const threshold = 200;      // Fully shrunk after 200px scroll
        let ticking = false;
        // Store initial state
        
        function updateNavbar() {
        const scrollTop = window.scrollY;
        let padding;

        if (scrollTop <= 0) {
            padding = initialPadding; // Full size
        } else if (scrollTop >= threshold) {
            padding = finalPadding;   // Fully shrunk
        } else {
            // Gradual interpolation
            const ratio = scrollTop / threshold;
            padding = initialPadding - (ratio * (initialPadding - finalPadding));
        }

        // Directly set padding
        navbar.style.padding = `${padding}rem 0`;

        // Optional: Add shadow when scrolled
        if (scrollTop > 50) {
            navbar.style.boxShadow = '0 4px 10px rgba(0, 0, 0, 0.15)';
        } else {
            navbar.style.boxShadow = '0 4px 10px rgba(0, 0, 0, 0.1)';
        }

        ticking = false;
    }

        // Function to update active link
        function updateActiveLink() {
            let current = '';
            const scrollPosition = window.scrollY + 100; // Offset for better accuracy

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const sectionBottom = sectionTop + sectionHeight;

                if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                    current = section.getAttribute('id');
                }
            });

            // Remove active class from all links
            navLinks.forEach(link => {
                link.classList.remove('active');
            });

            // Add active class to current link
            if (current) {
                const activeLink = document.querySelector(`a[href="#${current}"]`);
                if (activeLink) activeLink.classList.add('active');
            }

            if (window.scrollY <= 100) {
                navLinks.forEach(link => link.classList.remove('active'));
            }
        }

        // Handle scroll event
        window.addEventListener('scroll', () => {
            // Update active link based on scroll position
            updateActiveLink();

            if (!ticking) {
            requestAnimationFrame(updateNavbar);
            ticking = true;
        }
        }, {
             passive: true 
        });
        // Close mobile menu on link click
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                const navbarCollapse = document.querySelector('.navbar-collapse');
                const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            });
        });
        // Trigger on load
        updateActiveLink();
    </script>

</body>

</html>