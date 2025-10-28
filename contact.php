<?php
require_once __DIR__ . '/config.php';

// Initialize variables for form sticky values and messages
$errors = [];
$success = false;
$name = '';
$email = '';
$subject = '';
$message = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // simple server-side trimming
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // basic validation
    if ($name === '') $errors[] = 'Name is required.';
    if ($email === '') $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email format is invalid.';
    if ($subject === '') $errors[] = 'Subject is required.';
    if ($message === '') $errors[] = 'Message is required.';

    if (empty($errors)) {
        // store some extra metadata
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // prepared insert
        $stmt = $mysqli->prepare("INSERT INTO contacts (`name`, `email`, `subject`, `message`, `ip`, `user_agent`) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $errors[] = 'Database error: failed to prepare statement.';
        } else {
            $stmt->bind_param('ssssss', $name, $email, $subject, $message, $ip, $ua);
            if ($stmt->execute()) {
                $success = true;
                // clear form values
                $name = $email = $subject = $message = '';
            } else {
                $errors[] = 'Database error: failed to save message.';
            }
            $stmt->close();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact — EduBot</title>

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/bootstrap-icons.css" rel="stylesheet">
  <link href="css/templatemo-topic-listing.css" rel="stylesheet">
  <link href="css/custom.css" rel="stylesheet">

  <style>
    /* small local tweaks */
    .alert-list { margin-bottom: 1rem; }
  </style>
</head>
<body class="topics-listing-page" id="top">
<main>
  <!-- NAV: simplified, keep same as your index -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.html"><span>EduBot</span></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-lg-5 me-lg-auto">
          <li class="nav-item"><a class="nav-link" href="index.html#section_1">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="index.html#section_2">Browse Topics</a></li>
          <li class="nav-item"><a class="nav-link" href="index.html#section_3">How it works</a></li>
          <li class="nav-item"><a class="nav-link" href="index.html#section_4">FAQs</a></li>
          <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
        </ul>
        <div class="d-none d-lg-block">
          <a href="#top" class="navbar-icon bi-person" title="Go to top" aria-label="Go to top"></a>
        </div>
      </div>
    </div>
  </nav>

  <header class="site-header d-flex flex-column justify-content-center align-items-center">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-5 col-12">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.html">Homepage</a></li>
              <li class="breadcrumb-item active" aria-current="page">Contact Form</li>
            </ol>
          </nav>
          <h2 class="text-white">Contact Form</h2>
        </div>
      </div>
    </div>
  </header>

  <section class="section-padding section-bg">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 col-12">
          <h3 class="mb-4 pb-2">We'd love to hear from you</h3>
        </div>

        <div class="col-lg-6 col-12">
          <!-- feedback area -->
          <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
              Thanks — your message has been sent. We'll get back to you soon.
            </div>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
              <strong>Please fix the following:</strong>
              <ul class="alert-list">
                <?php foreach ($errors as $err): ?>
                  <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form action="contact.php" method="post" class="custom-form contact-form" role="form" novalidate>
            <div class="row">
              <div class="col-lg-6 col-md-6 col-12">
                <div class="form-floating mb-3">
                  <input type="text" name="name" id="name" class="form-control" placeholder="Name" required value="<?php echo e($name); ?>">
                  <label for="name">Name</label>
                </div>
              </div>

              <div class="col-lg-6 col-md-6 col-12"> 
                <div class="form-floating mb-3">
                  <input type="email" name="email" id="email" pattern="[^ @]*@[^ @]*" class="form-control" placeholder="Email address" required value="<?php echo e($email); ?>">
                  <label for="email">Email address</label>
                </div>
              </div>

              <div class="col-lg-12 col-12">
                <div class="form-floating mb-3">
                  <input type="text" name="subject" id="subject" class="form-control" placeholder="Subject" required value="<?php echo e($subject); ?>">
                  <label for="subject">Subject</label>
                </div>

                <div class="form-floating mb-3">
                  <textarea class="form-control" id="message" name="message" placeholder="Tell me about the project" style="height:140px;"><?php echo e($message); ?></textarea>
                  <label for="message">Tell me about the project</label>
                </div>
              </div>

              <div class="col-lg-4 col-12 ms-auto">
                <button type="submit" class="form-control btn btn-primary">Submit</button>
              </div>
            </div>
          </form>
        </div>

        <div class="col-lg-6 col-12">
          <iframe class="google-map mb-3" title="Topic Listing Center map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2595.065641062665!2d-122.4230416990949!3d37.80335401520422!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80858127459fabad%3A0x808ba520e5e9edb7!2sFrancisco%20Park!5e1!3m2!1sen!2sth!4v1684340239744!5m2!1sen!2sth" width="100%" height="250" allowfullscreen loading="lazy"></iframe>

          <h5 class="mt-4 mb-2">Topic Listing Center</h5>
          <p>Bay St &amp;, Larkin St, San Francisco, CA 94109, United States</p>
        </div>

      </div>
    </div>
  </section>
</main>

<footer class="site-footer section-padding">
  <div class="container">
    <div class="row">
      <div class="col-lg-3 col-12 mb-4 pb-2">
        <a class="navbar-brand mb-2" href="index.html"><span>EduBot</span></a>
      </div>

      <div class="col-lg-3 col-md-4 col-6">
        <h6 class="site-footer-title mb-3">Resources</h6>
        <ul class="site-footer-links">
          <li class="site-footer-link-item"><a href="#" class="site-footer-link">Home</a></li>
          <li class="site-footer-link-item"><a href="#" class="site-footer-link">How it works</a></li>
          <li class="site-footer-link-item"><a href="#" class="site-footer-link">FAQs</a></li>
          <li class="site-footer-link-item"><a href="#" class="site-footer-link">Contact</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-4 col-6 mb-4 mb-lg-0">
        <h6 class="site-footer-title mb-3">Information</h6>
        <p class="text-white d-flex mb-1"><a href="tel:+919936254178" class="site-footer-link">+91 99362 54178</a></p>
        <p class="text-white d-flex"><a href="mailto:info@EducationBotcompany.com" class="site-footer-link">info@EducationBotcompany.com</a></p>
      </div>

      <div class="col-lg-3 col-md-4 col-12 mt-4 mt-lg-0 ms-auto">
        <div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">English</button>
          <ul class="dropdown-menu">
            <li><button class="dropdown-item" type="button">Gujarati</button></li>
            <li><button class="dropdown-item" type="button">Hindi</button></li>
            <li><button class="dropdown-item" type="button">Korean</button></li>
          </ul>
        </div>

        <p class="copyright-text mt-lg-5 mt-4">Copyright © 2025 Topic Listing Center. All rights reserved.
          <br><br>Design: <a rel="nofollow noopener noreferrer" href="https://templatemo.com" target="_blank">EducationBot</a></p>
      </div>

    </div>
  </div>
</footer>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/jquery.sticky.js"></script>
<script src="js/custom.js"></script>
</body>
</html>
