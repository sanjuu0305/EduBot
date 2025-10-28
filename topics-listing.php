<?php
require_once __DIR__ . '/config.php';

// Pagination settings
$limit = 6; // items per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Optional category filter (by id)
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Count total published topics (optionally filtered)
if ($category_id > 0) {
    $cstmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM topics WHERE status='published' AND category_id = ?");
    $cstmt->bind_param('i', $category_id);
    $cstmt->execute();
    $cres = $cstmt->get_result()->fetch_assoc();
    $total = (int)$cres['cnt'];
    $cstmt->close();

    // fetch page
    $stmt = $mysqli->prepare(
        "SELECT t.id, t.title, t.slug, t.summary, t.views, c.name as category_name
         FROM topics t
         LEFT JOIN categories c ON t.category_id = c.id
         WHERE t.status='published' AND t.category_id = ?
         ORDER BY t.created_at DESC
         LIMIT ?, ?"
    );
    $stmt->bind_param('iii', $category_id, $offset, $limit);
} else {
    $countRes = $mysqli->query("SELECT COUNT(*) AS cnt FROM topics WHERE status='published'");
    $total = (int)$countRes->fetch_assoc()['cnt'];

    $stmt = $mysqli->prepare(
        "SELECT t.id, t.title, t.slug, t.summary, t.views, c.name as category_name
         FROM topics t
         LEFT JOIN categories c ON t.category_id = c.id
         WHERE t.status='published'
         ORDER BY t.created_at DESC
         LIMIT ?, ?"
    );
    $stmt->bind_param('ii', $offset, $limit);
}

$stmt->execute();
$res = $stmt->get_result();
$topics = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// fetch categories for tabs or filter
$cats = [];
$catq = $mysqli->query("SELECT id, name FROM categories ORDER BY name");
if ($catq) {
    $cats = $catq->fetch_all(MYSQLI_ASSOC);
}

// helper for pagination links
$total_pages = max(1, (int)ceil($total / $limit));
$base_url = 'topics-listing.php' . ($category_id ? '?category=' . $category_id : '');

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Topics Listing — EduBot</title>

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/bootstrap-icons.css" rel="stylesheet">
  <link href="css/templatemo-topic-listing.css" rel="stylesheet">
  <style>
    .custom-block-image { object-fit: cover; height: 180px; width: 100%; }
  </style>
</head>
<body class="topics-listing-page" id="top">
<main>
  <!-- NAV (kept simple, adjust if you have header includes) -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.html"><span>EduBot</span></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-lg-5 me-lg-auto">
          <li class="nav-item"><a class="nav-link" href="index.html#section_1">Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="topics-listing.php">Browse Topics</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <header class="site-header d-flex flex-column justify-content-center align-items-center">
    <div class="container"><div class="row align-items-center">
      <div class="col-lg-5 col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.html">Homepage</a></li>
            <li class="breadcrumb-item active" aria-current="page">Topics Listing</li>
          </ol>
        </nav>
        <h2 class="text-white">Topics Listing</h2>
      </div>
    </div></div>
  </header>

  <section class="section-padding">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 col-12 text-center">
          <h3 class="mb-4">Popular Topics</h3>
        </div>

        <!-- Category filter (optional) -->
        <div class="col-12 mb-3 text-center">
          <div class="btn-group" role="group" aria-label="categories">
            <a href="topics-listing.php" class="btn btn-sm btn-outline-secondary<?php echo $category_id === 0 ? ' active' : ''; ?>">All</a>
            <?php foreach ($cats as $c): ?>
              <a href="topics-listing.php?category=<?php echo (int)$c['id']; ?>" class="btn btn-sm btn-outline-secondary<?php echo $category_id === (int)$c['id'] ? ' active' : ''; ?>"><?php echo e($c['name']); ?></a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="col-lg-8 col-12 mt-3 mx-auto">
          <?php if (empty($topics)): ?>
            <div class="alert alert-info">No topics found.</div>
          <?php else: ?>
            <?php foreach ($topics as $t): ?>
              <div class="custom-block custom-block-topics-listing bg-white shadow-lg mb-5">
                <div class="d-flex">
                  <img src="images/topics/default-topic.jpg" class="custom-block-image img-fluid" alt="<?php echo e($t['title']); ?>">
                  <div class="custom-block-topics-listing-info d-flex">
                    <div>
                      <h5 class="mb-2"><?php echo e($t['title']); ?></h5>
                      <p class="mb-0"><?php echo e($t['summary'] ?: substr(strip_tags($t['title']),0,120)); ?></p>
                      <a href="topic-detail.php?slug=<?php echo urlencode($t['slug']); ?>" class="btn custom-btn mt-3 mt-lg-4">Learn More</a>
                    </div>
                    <span class="badge bg-design rounded-pill ms-auto"><?php echo (int)$t['views']; ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <!-- Pagination -->
          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-0">
              <?php
              // build base for page links preserving category
              $qsBase = $category_id ? "category={$category_id}&" : '';
              $prevPage = max(1, $page - 1);
              $nextPage = min($total_pages, $page + 1);
              ?>
              <li class="page-item<?php echo $page <= 1 ? ' disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo $qsBase; ?>page=<?php echo $prevPage; ?>" aria-label="Previous"><span aria-hidden="true">Prev</span></a>
              </li>

              <?php
              // show a window of pages around current
              $start = max(1, $page - 2);
              $end = min($total_pages, $page + 2);
              for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item<?php echo $i === $page ? ' active' : ''; ?>"><a class="page-link" href="?<?php echo $qsBase; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
              <?php endfor; ?>

              <li class="page-item<?php echo $page >= $total_pages ? ' disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo $qsBase; ?>page=<?php echo $nextPage; ?>" aria-label="Next"><span aria-hidden="true">Next</span></a>
              </li>
            </ul>
          </nav>
        </div>

      </div>
    </div>
  </section>

  <!-- Trending section (optional reuse static block or generate dynamic trending) -->
  <section class="section-padding section-bg">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 col-12"><h3 class="mb-4">Trending Topics</h3></div>

        <?php
        // fetch top 2 trending by views
        $tr = $mysqli->query("SELECT id, title, slug, summary, views FROM topics WHERE status='published' ORDER BY views DESC LIMIT 2");
        $trending = $tr ? $tr->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($trending as $item): ?>
          <div class="col-lg-6 col-md-6 col-12 mt-3 mb-4 mb-lg-0">
            <div class="custom-block bg-white shadow-lg">
              <a href="topic-detail.php?slug=<?php echo urlencode($item['slug']); ?>">
                <div class="d-flex">
                  <div>
                    <h5 class="mb-2"><?php echo e($item['title']); ?></h5>
                    <p class="mb-0"><?php echo e($item['summary'] ?: ''); ?></p>
                  </div>
                  <span class="badge bg-finance rounded-pill ms-auto"><?php echo (int)$item['views']; ?></span>
                </div>
                <img src="images/topics/default-topic.jpg" class="custom-block-image img-fluid" alt="">
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <footer class="site-footer section-padding">
    <div class="container">
      <div class="row">
        <div class="col-lg-3 col-12 mb-4 pb-2">
          <a class="navbar-brand mb-2" href="index.html"><span>EduBot</span></a>
        </div>
        <div class="col-lg-3 col-md-4 col-6">
          <h6 class="site-footer-title mb-3">Resources</h6>
        </div>
        <div class="col-lg-3 col-md-4 col-6 mb-4 mb-lg-0">
          <h6 class="site-footer-title mb-3">Information</h6>
        </div>
        <div class="col-lg-3 col-md-4 col-12 mt-4 mt-lg-0 ms-auto">
          <p class="copyright-text mt-lg-5 mt-4">Copyright © 2025 Topic Listing Center. All rights reserved.</p>
        </div>
      </div>
    </div>
  </footer>
</main>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
