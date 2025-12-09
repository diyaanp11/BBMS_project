<?php
$page_title = "Contact";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
     <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      margin: 0;
      padding: 0;
    }
    .contact-section {
      text-align: center;
      padding: 40px;
    }
    .cards {
      display: flex;
      justify-content: center;
      gap: 25px;
      flex-wrap: wrap;
      margin-top: 35px;
    }
    .card {
      background: #fff;
      padding: 20px;
      width: 300px;
      height: 200px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s ease-in-out;
    }
    .card:hover {
      transform: scale(1.05);
    }
    .card h3 {
      margin: 10px 0;
      color: #333;
    }
    .card p {
      margin: 5px 0;
      color: #444;
    }
    .social-icons {
      margin-top: 20px;
    }
    .social-icons a {
      text-decoration: none;
      margin: 0 8px;
      font-size: 30px;
      color: rgba(49, 193, 61, 1);
    }
    .social-icons a:hover {
      color: #ffa200ff; 
    }
    .mt-2{
      margin-top: 20px;
      background: white; 
      padding: 3rem; 
      border-radius: 10px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    }
    .mt-2 h2{
      text-align: center;
      color: #dc2626; 
      margin-bottom: 2rem;
    }
    .Info{
      display: grid; 
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
      gap: 2rem;
    }
    .Info-info h4{
      color: #dc2626; 
      margin-bottom: 0.5rem;
    }
  </style>
</head>
<body>
  <div class="contact-section">
    <h2>Contact Us</h2>
    <p>Get in touch with our project team</p>
    <div class="cards">
      <div class="card">
        <h3>Diya Sharma</h3>
        <p><i class="fa-solid fa-envelope"></i> diyaanp11@gmail.com</p>
        <p><i class="fa-solid fa-phone-volume"></i> +977-98XXXXXXX</p>
        <div class="social-icons">
            <a href="https://github.com/diyaanp11"><i class="fa-brands fa-github"></i></a>
            <a href="https://www.facebook.com/diya.n.yaupane.2025"><i class="fa-brands fa-facebook"></i></a>
            <a href="https://www.instagram.com/diya.np_11/"><i class="fa-brands fa-square-instagram"></i></a>
            <a href="https://x.com/Diyaanp11"><i class="fa-brands fa-twitter"></i></a>
        </div>
      </div>
      <div class="card">
        <h3>Kabita Pandey</h3>
        <p><i class="fa-solid fa-envelope"></i> kabitapanday02@gmail.com</p>
        <p><i class="fa-solid fa-phone-volume"></i> +977-98XXXXXXX</p>
        <div class="social-icons">
            <a href=""><i class="fa-brands fa-github"></i></a>
            <a href="https://www.facebook.com/kabita.pandey.670393"><i class="fa-brands fa-facebook"></i></a>
            <a href="https://www.instagram.com/kabita.446/"><i class="fa-brands fa-square-instagram"></i></a>
            <a href=""><i class="fa-brands fa-twitter"></i></i></a>
        </div>
      </div>
    </div>
  </div>
  <!-- Frequently asked question -->
   <section class="mt-2">
    <h2>Frequently Asked Questions</h2>
        <div class="Info">
            <div class="Info-info">
                <h4>Who can donate blood?</h4>
                <p>Generally, healthy individuals aged 18-65, weighing at least 50kg, can donate blood. Specific eligibility criteria apply.</p>
            </div>
            <div  class="Info-info">
                <h4>How often can I donate?</h4>
                <p>You can donate whole blood every 56 days (8 weeks). Platelet donations can be made every 7 days, up to 24 times per year.</p>
            </div>
            <div  class="Info-info">
                <h4>Is blood donation safe?</h4>
                <p>Yes, blood donation is completely safe. We use sterile, single-use equipment and follow strict safety protocols.</p>
            </div>
            <div  class="Info-info">
                <h4>How long does donation take?</h4>
                <p>The entire process takes about 45-60 minutes, with the actual donation taking only 8-10 minutes.</p>
            </div>
        </div>
    </section>
</body>
</html>
<?php include 'footer.php'; ?>