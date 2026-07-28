<?php
$page_title = "Learn More";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learn More - Blood Bank Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{ margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .learn-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }
        .learn-container h1 {
            text-align: center;
            color: #d32f2f;
            font-size: 2.2rem;
            margin-bottom: 2rem;
        }
        .info-card {
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }
        .info-card h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        .info-card ul {
            padding-left: 20px;
            margin-bottom: 0;
        }
        .info-card li {
            margin-bottom: 8px;
            color: #555;
        }
        .form-row {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .form-group { flex: 1; min-width: 250px; }
        .blood-compatibility-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .blood-type-card {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .blood-type-card h3 { margin: 0; font-size: 1.8em; }
        .blood-type-card p { margin: 5px 0; font-size: 0.9em; }
        .process-steps {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .step {
            text-align: center;
            flex: 1;
            min-width: 150px;
            margin: 10px;
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2em;
            margin: 0 auto 10px;
        }
        .step h3 { margin: 5px 0; color: #333; }
        .step p { color: #666; font-size: 0.9em; }
        .myth-fact { margin-top: 15px; }
        .myth {
            background: white;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 3px solid #007bff;
        }
        .myth h4 { margin: 0 0 5px 0; color: #333; }
        .myth p { margin: 0; color: #28a745; font-weight: 500; }
        .faq-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 3px solid #6c757d;
        }
        .faq-item h4 { margin: 0 0 8px 0; color: #d32f2f; }
        .faq-item p { margin: 0; color: #555; }
        .cta-section {
            text-align: center;
            margin-top: 30px;
            padding: 30px;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 12px;
        }
        .cta-section h2 { margin-bottom: 10px; }
        .cta-section p { margin-bottom: 20px; }
        .btn {
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 5px;
        }
        .btn-primary { background: white; color: #dc3545; }
        .btn-outline { background: transparent; border: 2px solid white; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        @media (max-width: 768px) {
            .process-steps { flex-direction: column; }
            .step { margin: 10px 0; }
            .blood-compatibility-grid { grid-template-columns: repeat(2, 1fr); }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="learn-container">
    <h1>Learn More About Blood Donation</h1>
    
    <!-- Why Donate Section -->
    <div class="info-card" style="background: #f8d7da; border-left: 4px solid #dc3545;">
        <h2><i class="fas fa-heart"></i> Why Donate Blood?</h2>
        <ul>
            <li><strong>Save Lives:</strong> One donation can save up to 3 lives</li>
            <li><strong>Health Benefits:</strong> Regular donations reduce heart disease risk</li>
            <li><strong>Free Checkup:</strong> Get free health screening with every donation</li>
            <li><strong>Community Service:</strong> Make a direct impact in your community</li>
        </ul>
    </div>

    <!-- Eligibility Criteria -->
    <div class="info-card" style="background: #d1ecf1; border-left: 4px solid #17a2b8;">
        <h2><i class="fas fa-clipboard-list"></i> Eligibility Criteria</h2>
        <div class="form-row">
            <div class="form-group">
                <ul>
                    <li><strong>Age:</strong> 18-65 years</li>
                    <li><strong>Weight:</strong> Minimum 50 kg</li>
                    <li><strong>Hemoglobin:</strong> Minimum 12.5 g/dL</li>
                </ul>
            </div>
            <div class="form-group">
                <ul>
                    <li><strong>Health:</strong> No illness for past 2 weeks</li>
                    <li><strong>Interval:</strong> 56 days since last donation</li>
                    <li><strong>Pregnancy:</strong> Not pregnant or breastfeeding</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Blood Type Compatibility -->
    <div class="info-card" style="background: #d4edda; border-left: 4px solid #28a745;">
        <h2><i class="fas fa-microscope"></i> Blood Type Compatibility</h2>
        <div class="blood-compatibility-grid">
            <div class="blood-type-card" style="background: #dc3545; color: white;">
                <h3>O-</h3>
                <p>Universal Donor</p>
                <small>Can donate to ANY blood type</small>
            </div>
            <div class="blood-type-card" style="background: #fd7e14;">
                <h3>O+</h3>
                <p>Can donate to: O+, A+, B+, AB+</p>
            </div>
            <div class="blood-type-card" style="background: #ffc107;">
                <h3>A-</h3>
                <p>Can donate to: A-, A+, AB-, AB+</p>
            </div>
            <div class="blood-type-card" style="background: #20c997;">
                <h3>B-</h3>
                <p>Can donate to: B-, B+, AB-, AB+</p>
            </div>
            <div class="blood-type-card" style="background: #17a2b8; color: white;">
                <h3>AB-</h3>
                <p>Can donate to: AB-, AB+</p>
            </div>
            <div class="blood-type-card" style="background: #6f42c1; color: white;">
                <h3>AB+</h3>
                <p>Universal Recipient</p>
                <small>Can receive from ANY blood type</small>
            </div>
        </div>
    </div>

    <!-- Donation Process -->
    <div class="info-card" style="background: #e9ecef; border-left: 4px solid #6c757d;">
        <h2><i class="fas fa-sync-alt"></i> Donation Process</h2>
        <div class="process-steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Registration</h3>
                <p>Fill form and show ID proof</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Screening</h3>
                <p>Health check & questionnaire</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Donation</h3>
                <p>8-10 minutes (450ml)</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <h3>Refreshments</h3>
                <p>Rest & have snacks</p>
            </div>
            <div class="step">
                <div class="step-number">5</div>
                <h3>Certificate</h3>
                <p>Get donor certificate</p>
            </div>
        </div>
    </div>

    <!-- Myths vs Facts -->
    <div class="info-card" style="background: #cce5ff; border-left: 4px solid #007bff;">
        <h2><i class="fas fa-exclamation-circle"></i> Myths vs Facts</h2>
        <div class="myth-fact">
            <div class="myth">
                <h4>❌ Myth: Donating blood is painful</h4>
                <p>✅ Fact: Only slight pinch, less painful than a bee sting</p>
            </div>
            <div class="myth">
                <h4>❌ Myth: I can get infected</h4>
                <p>✅ Fact: Sterile, single-use equipment used every time</p>
            </div>
            <div class="myth">
                <h4>❌ Myth: It makes me weak</h4>
                <p>✅ Fact: Body replaces blood within 24-48 hours</p>
            </div>
            <div class="myth">
                <h4>❌ Myth: Takes too much time</h4>
                <p>✅ Fact: Whole process takes only 45-60 minutes</p>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="info-card" style="background: #f8f9fa; border-left: 4px solid #495057;">
        <h2><i class="fas fa-question-circle"></i> Frequently Asked Questions</h2>
        <div class="faq-item">
            <h4>Q: How often can I donate blood?</h4>
            <p>A: Every 56 days (8 weeks) for whole blood donation</p>
        </div>
        <div class="faq-item">
            <h4>Q: What should I eat before donation?</h4>
            <p>A: Iron-rich foods (spinach, beans) and drink plenty of water</p>
        </div>
        <div class="faq-item">
            <h4>Q: Can I donate if I have tattoos?</h4>
            <p>A: Yes, after 6 months of getting a tattoo</p>
        </div>
        <div class="faq-item">
            <h4>Q: How long does donated blood last?</h4>
            <p>A: Red cells: 42 days, Platelets: 5 days, Plasma: 1 year</p>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="cta-section">
        <h2>Ready to Make a Difference?</h2>
        <p>Your single donation can save multiple lives. Join our community of lifesavers today!</p>
        <div>
            <a href="../Recipient/signup.php" class="btn btn-primary">Request Blood</a>
            <a href="../Donor/signup.php" class="btn btn-outline">Become a Donor</a>
        </div>
    </div>
</div>
</body>
</html>
<?php include 'chatbot.php'; ?>
<?php include 'footer.php'; ?>
