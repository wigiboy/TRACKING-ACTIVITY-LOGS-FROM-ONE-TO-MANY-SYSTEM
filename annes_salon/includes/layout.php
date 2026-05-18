<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anne's Salon — <?= htmlspecialchars($page_title ?? 'Dashboard') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="brand">
            <span class="brand-name">Anne's Salon</span>
            <span class="brand-tagline">Est. 2019 &mdash; Beauty &amp; Style</span>
        </a>

        <nav class="main-nav">
            <a href="index.php"         <?= basename($_SERVER['PHP_SELF']) === 'index.php'        ? 'class="active"' : '' ?>>Dashboard</a>
            <a href="stylists.php"      <?= basename($_SERVER['PHP_SELF']) === 'stylists.php'     ? 'class="active"' : '' ?>>Stylists</a>
            <a href="clients.php"       <?= basename($_SERVER['PHP_SELF']) === 'clients.php'      ? 'class="active"' : '' ?>>Clients</a>
            <a href="search.php"        <?= basename($_SERVER['PHP_SELF']) === 'search.php'       ? 'class="active"' : '' ?>>Search</a>
            <a href="activity_logs.php" <?= basename($_SERVER['PHP_SELF']) === 'activity_logs.php'? 'class="active"' : '' ?>>Activity Logs</a>
        </nav>

        <div class="header-user">
            <span class="user-greeting">Hello, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong></span>
            <a href="logout.php" class="btn-logout">Log Out</a>
        </div>
    </div>
</header>

<main class="site-main">
