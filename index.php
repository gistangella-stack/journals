<?php
declare(strict_types=1);

/**
 * index.php — Homepage for a university-published academic journal.
 *
 * All content below is stored in arrays for clarity. Replace each array
 * with a PDO query / CMS call when moving to production.
 */

/* ------------------------------------------------------------------
 |  Helpers
 * ------------------------------------------------------------------ */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatDate(string $iso, string $format = 'j F Y'): string
{
    $ts = strtotime($iso);
    return $ts ? date($format, $ts) : $iso;
}

function excerpt(string $text, int $words = 28): string
{
    $text  = trim(strip_tags($text));
    $parts = preg_split('/\s+/', $text) ?: [];
    if (count($parts) <= $words) {
        return $text;
    }
    return implode(' ', array_slice($parts, 0, $words)) . '…';
}

/* ------------------------------------------------------------------
 |  University & journal configuration
 * ------------------------------------------------------------------ */
$university = [
    'name'     => 'Ebonyi State University of ICT, Science and Technology',
    'short'    => 'UICTO',
    'press'    => 'Research Unit',
    'address'  => 'Oferekpe Izzi',
    'phone'    => '+2348068571089',
    'library'  => '',
   'research' => '',
    'pressUrl' => '',
];

$journal = [
    'title'     => 'UICTO JOURNALS',
    'abbr'      => 'UICTO',
    'tagline'   => '',
    'issn'      => 'comming soon',
    'eissn'     => 'coming soon',
    'founded'   => 2025,
    'editor'    => 'Prof. John-Mary Ani',
    'email'     => 'uicto@edu,ng',
];

$nav = [
    'Home'            => 'index.php',
    'About'           => 'aboutthejournal.php',
    'Editorial Board' => 'editorial-board.php',
    'Current Issue'   => 'current.php',
    'Archives'        => 'achieves.php',
    'Authors'     => 'authors.php',
    'Contact'         => 'contactus.php',
];

/* ------------------------------------------------------------------
 |  Current issue
 * ------------------------------------------------------------------ */
$currentIssue = [
    'volume'    => 1,
    'number'    => 2,
    'year'      => (int) date('Y'),
    'season'    => 'Rainy season',
    'theme'     => 'Information System and AI-Driven University',
    'published' => date('Y-m-d', strtotime('-12 days')),
];

/* ------------------------------------------------------------------
 |  Featured article (hero highlight)
 * ------------------------------------------------------------------ */
$featured = [
    'type'     => 'Featured Research',
    'title'    => 'Adaptive Urban Cooling: A Multi-Agent Model of Green Infrastructure',
    'authors'  => 'R. Adeyemi, H. Lindström, M. Tanaka, and J. O. Whitfield',
    'abstract' => 'Cities facing intensifying heatwaves require coordinated interventions across '
                . 'parks, roofs, and water systems. We present a multi-agent simulation of green '
                . 'infrastructure adoption and show that moderate, well-timed investment yields '
                . 'up to 4.1 °C of localised cooling at a fraction of the cost of hard engineering.',
    'doi'      => '10.5555/njis.2026.27.2.045',
    'pages'    => '45–72',
    'pdf'      => '#',
];

/* ------------------------------------------------------------------
 |  Latest articles
 * ------------------------------------------------------------------ */
$articles = [
    [
        'type'     => 'Research Article',
        'title'    => 'Machine Learning for Early Detection of Crop Blight in Smallholder Farms',
        'authors'  => 'T. Nwosu, S. Ibrahim, L. Petrov',
        'abstract' => 'A lightweight convolutional model deployed on low-cost edge devices identifies '
                    . 'blight infections up to nine days earlier than manual inspection in a two-season trial.',
        'doi'      => '10.5555/njis.2026.27.2.073',
        'pages'    => '73–94',
        'pdf'      => '#',
    ],
    [
        'type'     => 'Research Article',
        'title'    => 'Oral Histories of Post-Industrial Regeneration: A Digital Archive',
        'authors'  => 'C. Beaumont, F. Al-Rashid',
        'abstract' => 'We describe a participatory digital archive documenting community memory in three '
                    . 'former mining towns, and reflect on the ethics of open-access oral history.',
        'doi'      => '10.5555/njis.2026.27.2.095',
        'pages'    => '95–118',
        'pdf'      => '#',
    ],
    [
        'type'     => 'Review',
        'title'    => 'A Decade of CRISPR Ethics: What Have We Actually Decided?',
        'authors'  => 'N. Sørensen, A. K. Mukherjee',
        'abstract' => 'A systematic review of 210 policy documents reveals persistent divergence between '
                    . 'national frameworks, with implications for international collaboration.',
        'doi'      => '10.5555/njis.2026.27.2.119',
        'pages'    => '119–144',
        'pdf'      => '#',
    ],
    [
        'type'     => 'Short Communication',
        'title'    => 'On the Reproducibility of University Lab Notebooks in the Open-Science Era',
        'authors'  => 'P. J. Hollander',
        'abstract' => 'An audit of 120 publicly deposited lab notebooks finds that fewer than one in three '
                    . 'contain sufficient metadata for independent reproduction.',
        'doi'      => '10.5555/njis.2026.27.2.145',
        'pages'    => '145–152',
        'pdf'      => '#',
    ],
];

/* ------------------------------------------------------------------
 |  Journal metrics (stats strip)
 * ------------------------------------------------------------------ */
$stats = [
    ['value' => '4.1',  'label' => 'Impact Factor'],
    ['value' => '0.30', 'label' => 'Acceptance Rate'],
    ['value' => '7',   'label' => 'Days to First Decision'],
    ['value' => '15', 'label' => 'Articles Published'],
];

/* ------------------------------------------------------------------
 |  Editorial board (highlights)
 * ------------------------------------------------------------------ */
$board = [
    ['name' => 'Prof. Ernest Ituma Egba', 'role' => 'Editor-in-Chief',        'affil' => 'Faculty of Engineering'],
    ['name' => 'Ani John-Mary',        'role' => 'Deputy Editor',          'affil' => 'Department of Managment Sciences'],
    ['name' => 'Dr Nweso',    'role' => 'Associate Editor',       'affil' => 'Department of Computer Science'],
    ['name' => 'Dr. Chinagolum Ituma',        'role' => 'Associate Editor',       'affil' => 'Department of Computer Science'],
    ['name' => 'Prof. Okorie Nwite',     'role' => 'Statistics Editor',      'affil' => 'Department of Mathematics'],
    ['name' => 'Prof. Martin Ogayi',       'role' => 'Ethics Editor',          'affil' => 'Centre for Entreprenuaship'],
];

/* ------------------------------------------------------------------
 |  Announcements
 * ------------------------------------------------------------------ */
$announcements = [
    [
        'date'  => date('Y-m-d', strtotime('-1 days')),
        'title' => 'A shift of the UICTO Result Managment System to a decentralized system',
        'body'  => 'Abstracts due 5 October. Guest-edited by the Centre for Development Informatics.',
        'tag'   => 'Call for Papers',
    ],
    [
        'date'  => date('Y-m-d', strtotime('-5 days')),
        'title' => 'UICTO in Edge Computing Era',
        'body'  => 'All articles from Volume 2 onward are now deposited in the University repository.',
        'tag'   => 'Call for papers',
    ],
    [
        'date'  => date('Y-m-d', strtotime('-3 days')),
        'title' => 'New face of the University website via Blochchain Technology.',
        'body'  => 'Abstracts due 9 October. Guest-edited by the Centre for Development Informatics.',
        'tag'   => 'Call for papers',
    ],
	
];

/* ------------------------------------------------------------------
 |  Indexing & partnerships
 * ------------------------------------------------------------------ */
$indexedIn = ['Scopus', 'Web of Science', 'DOAJ', 'Google Scholar', 'HERAN'];

$issueYear = $currentIssue['year'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($journal['title']) ?> / <?= e($university['name']) ?></title>
<meta name="description" content="<?= e($journal['tagline']) ?>">

		



<style>
    :root {
        --navy:     #0f2540;
        --navy-2:   #16375e;
        --gold:     #b8860b;
        --gold-l:   #e7c66b;
        --ink:      #14213d;
        --ink-soft: #4a5568;
        --line:     #e2e8f0;
        --bg:       #f6f8fb;
        --card:     #ffffff;
        --accent:   #1d4ed8;
        --radius:   10px;
        --max:      1200px;
    }
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
        margin: 0;
        font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: var(--ink);
        background: var(--navy));
        line-height: 1.65;
        font-size: 16px;
    }
    a { color: var(--accent); text-decoration: none; }
    a:hover { text-decoration: underline; }
    h1, h2, h3, h4 { font-family: Georgia, "Times New Roman", serif; line-height: 1.25; margin: 0 0 .5em; }
    .wrap { max-width: var(--max); margin: 0 auto; padding: 0 22px; }

    /* ---------- Top utility bar ---------- */
    .topbar {
        background: var(--navy-2);
        color: #fff;
        font-size: .9rem;
        border-bottom: 3px solid var(--gold);
    }
    .topbar .wrap {
        display: flex; flex-wrap: wrap; gap: 16px;
        justify-content: space-between; align-items: center;
        padding-top: 9px; padding-bottom: 9px;
    }
    .topbar a { color: #dbe4ee; }
    .topbar .links { display: flex; flex-wrap: wrap; gap: 18px; }

    /* ---------- Masthead ---------- */
    header.site { background: var(--card); border-bottom: 1px solid var(--line); }
    .masthead {
        display: flex; flex-wrap: wrap; gap: 22px;
        align-items: center; justify-content: space-between;
        padding: 24px 0 22px;
    }
    .brand { display: flex; align-items: center; gap: 18px; }
    .crest {
        width: 744px; height: 74px; flex: 0 0 74px;
        
        background: radial-gradient(circle at 30% 25%, var(--navy-2), var(--navy));
       
        color: var(--gold-l);
        display: grid; place-items: center;
        font-family: Georgia, serif; font-weight: 700; font-size: 1.1rem;
        letter-spacing: .5px;
    }
    .brand-text .uni {
        font-size: .78rem; font-weight: 800; letter-spacing: 1.6px;
        text-transform: uppercase; color: var(--navy);
    }
    .brand-text h1 { font-size: 1.5rem; margin: 2px 0 2px; }
    .brand-text .tagline { color: var(--ink-soft); font-size: .86rem; margin: 0; }

    .masthead-actions { display: flex; flex-direction: column; gap: 12px; align-items: flex-end; }
    @media (max-width: 720px) { .masthead-actions { align-items: stretch; } }
    .search { display: flex; gap: 8px; }
    .search input[type="search"] {
        padding: 9px 12px; border: 1px solid var(--line);
        border-radius: var(--radius); font: inherit; font-size: .9rem;
        min-width: 240px; background: #fff;
    }
    .search button {
        padding: 9px 18px; border: 0; border-radius: var(--radius);
        background: var(--navy); color: #fff; font: inherit; font-size: .9rem;
        cursor: pointer;
    }
    .search button:hover { background: var(--navy-2); }

    /* ---------- Nav ---------- */
    nav.main { background: var(--navy-2); }
    nav.main ul {
        list-style: none; margin: 0; padding: 0;
        display: flex; flex-wrap: wrap;
    }
    nav.main a {
        display: block; padding: 15px 20px;
        color: #e3ebf5; font-size: .9rem; font-weight: 500;
        border-bottom: 3px solid transparent;
        transition: background .15s ease, border-color .15s ease;
    }
    nav.main a:hover,
    nav.main a[aria-current="page"] {
        background: rgba(255,255,255,.08);
        border-bottom-color: var(--gold-l);
        text-decoration: none;
    }

    /* ---------- Layout ---------- */
    .layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 330px;
        gap: 36px;
        padding: 36px 0 54px;
    }
    @media (max-width: 940px) { .layout { grid-template-columns: 1fr; } }
    section { margin-bottom: 44px; }

    .section-head {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 14px; border-bottom: 2px solid var(--line);
        padding-bottom: 10px; margin-bottom: 22px;
    }
	
	.section-head2 {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 14px; border-bottom: 2px solid var(--line);
        padding-bottom: 10px; margin-bottom: 22px;
    }
    .section-head h2 { font-size: 1.3rem; margin: 0; }
    .section-head .more { font-size: .84rem; white-space: nowrap; }

    /* ---------- Stats strip ---------- */
    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px;
        margin-bottom: 40px;
    }
    .stat {
        background: var(--card);
        border: 1px solid var(--line);
        border-top: 3px solid var(--gold);
        border-radius: var(--radius);
        padding: 18px 20px;
        text-align: center;
    }
    .stat .num {
        font-family: Georgia, serif;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--navy);
        line-height: 1.1;
    }
    .stat .lbl {
        display: block;
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: 1.1px;
        color: var(--ink-soft);
        margin-top: 6px;
    }

    /* ---------- Hero / current issue ---------- */
    .hero {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 28px;
        display: grid;
        grid-template-columns: 160px minmax(0, 1fr);
        gap: 28px;
        margin-bottom: 22px;
        position: relative;
        overflow: hidden;
    }
    .hero::before {
        content: "";
        position: absolute; inset: 0 auto 0 0;
        width: 5px;
        background: linear-gradient(180deg, var(--gold), var(--gold-l));
    }
    @media (max-width: 640px) { .hero { grid-template-columns: 1fr; } }

    .cover {
        aspect-ratio: 3 / 4;
        border-radius: 8px;
        background: linear-gradient(160deg, var(--navy), var(--navy-2) 60%, #2a5a8c);
        color: #fff;
        display: flex; flex-direction: column; justify-content: space-between;
        padding: 18px;
        box-shadow: 0 10px 24px rgba(15,37,64,.24);
        border: 1px solid rgba(231,198,107,.35);
    }
    .cover .uni {
        font-size: .62rem; letter-spacing: 1.6px; text-transform: uppercase;
        color: var(--navy); opacity: .95;
    }
    .cover .ttl { font-family: Georgia, serif; font-size: 1.15rem; line-height: 1.25; }
    .cover .vol {
        font-size: .74rem; letter-spacing: 1px; text-transform: uppercase;
        opacity: .88;
    }

    .kicker {
        display: inline-block; font-size: .80rem; font-weight: 800;
        letter-spacing: 1.5px; text-transform: uppercase; color: var(--gold);
        margin-bottom: 8px;
    }
    .hero h2 { font-size: 1.55rem; }
    .hero p.lead { color: var(--ink-soft); margin: 0 0 18px; }
    .btn-row { display: flex; flex-wrap: wrap; gap: 10px; }
    .btn {
        display: inline-block; padding: 10px 20px; border-radius: var(--radius);
        font-size: .87rem; font-weight: 600; border: 1px solid transparent;
        cursor: pointer;
    }
    .btn-primary { background: var(--navy); color: #fff; }
    .btn-primary:hover { background: var(--navy-2); text-decoration: none; }
    .btn-gold { background: var(--navy-2); color: #fff; }
    .btn-gold:hover { background: #9a7009; text-decoration: none; }
    .btn-ghost { border-color: var(--line); color: var(--ink); background: #fff; }
    .btn-ghost:hover { border-color: var(--navy); color: var(--navy); text-decoration: none; }

    /* ---------- Featured article ---------- */
    .featured {
        background: #fffdf5;
        border: 1px solid #efe2bd;
        border-left: 5px solid var(--gold);
        border-radius: var(--radius);
        padding: 24px 26px;
        margin-bottom: 40px;
    }
    .featured h3 { font-size: 1.25rem; margin-bottom: 6px; }
    .featured h3 a { color: var(--ink); }
    .featured h3 a:hover { color: var(--accent); text-decoration: none; }
    .featured .authors { font-size: .86rem; color: var(--ink-soft); margin: 0 0 12px; }
    .featured .abstract { font-size: .93rem; color: var(--ink-soft); margin: 0 0 14px; }

    /* ---------- Article cards ---------- */
    .article {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 20px 22px;
        margin-bottom: 14px;
        transition: box-shadow .18s ease, transform .18s ease;
    }
    .article:hover {
        box-shadow: 0 8px 20px rgba(15,37,64,.08);
        transform: translateY(-1px);
    }
    .badge {
        display: inline-block; font-size: .68rem; font-weight: 700;
        letter-spacing: .9px; text-transform: uppercase;
        color: var(--navy); background: #e5edf7;
        padding: 3px 10px; border-radius: 999px; margin-bottom: 10px;
    }
    .article h3 { font-size: 1.08rem; margin-bottom: 5px; }
    .article h3 a { color: var(--ink); }
    .article h3 a:hover { color: var(--accent); text-decoration: none; }
    .authors { font-size: .85rem; color: var(--ink-soft); margin: 0 0 9px; }
    .abstract {
        font-size: .89rem; color: var(--ink-soft); margin: 0 0 14px;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .meta {
        display: flex; flex-wrap: wrap; gap: 16px;
        font-size: .78rem; color: var(--ink-soft);
        border-top: 1px dashed var(--line); padding-top: 12px;
    }
    .meta strong { color: var(--ink); font-weight: 600; }

    /* ---------- Board ---------- */
    .board-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 14px;
    }
    .board-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        padding: 16px 18px;
    }
    .board-card .name { font-weight: 700; font-size: .95rem; }
    .board-card .role {
        font-size: .74rem; font-weight: 700; letter-spacing: 1px;
        text-transform: uppercase; color: var(--gold); margin: 4px 0;
    }
    .board-card .affil { font-size: .82rem; color: var(--ink-soft); }

    /* ---------- Announcements ---------- */
    .ann {
        background: var(--card); border: 1px solid var(--line);
        border-left: 4px solid var(--navy-2);
        border-radius: var(--radius);
        padding: 16px 20px; margin-bottom: 12px;
    }
    .ann .tag {
        display: inline-block; font-size: .68rem; font-weight: 700;
        letter-spacing: 1px; text-transform: uppercase;
        color: var(--navy); background: #e5edf7;
        padding: 2px 9px; border-radius: 999px; margin-bottom: 8px;
    }
    .ann h3 { font-size: 1rem; margin-bottom: 4px; }
    .ann time {
        font-size: .74rem; color: var(--ink-soft);
        text-transform: uppercase; letter-spacing: .6px;
    }
    .ann p { margin: 6px 0 0; font-size: .88rem; color: var(--ink-soft); }

    /* ---------- Sidebar ---------- */
    aside .panel {
        background: var(--card); border: 1px solid var(--line);
        border-radius: var(--radius); padding: 22px; margin-bottom: 20px;
    }
    aside .panel h3 {
        font-size: .92rem; text-transform: uppercase; letter-spacing: 1.1px;
        color: var(--ink-soft); margin-bottom: 14px;
    }
    aside .panel p { font-size: .88rem; color: var(--ink-soft); margin: 0 0 14px; }
    aside .panel .btn { width: 100%; text-align: center; }
    .tags { display: flex; flex-wrap: wrap; gap: 7px; }
    .tags span {
        font-size: .76rem; background: #eef2f8; color: var(--navy);
        padding: 4px 11px; border-radius: 999px;
    }
    .contact-list { list-style: none; margin: 0; padding: 0; font-size: .88rem; }
    .contact-list li { padding: 5px 0; color: var(--ink-soft); }
    .contact-list strong { color: var(--ink); }

    /* ---------- Footer ---------- */
    footer.site {
        background: var(--navy); color: #9fb2c9; font-size: .85rem;
        padding: 40px 0 26px; margin-top: 10px;
        border-top: 4px solid var(--gold);
    }
    footer.site a { color: #dbe4ee; }
    .foot-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 28px; margin-bottom: 28px;
    }
    footer.site h4 { color: #fff; font-size: .95rem; margin-bottom: 12px; }
    footer.site ul { list-style: none; margin: 0; padding: 0; }
    footer.site li { padding: 3px 0; }
    .copyright {
        border-top: 1px solid #1e3a5c; padding-top: 18px;
        display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between;
        font-size: .79rem;
    }
	
	
	
	
</style>
</head>
<body>

<!-- ================= TOP UTILITY BAR ================= -->
<div class="topbar">
    <div class="wrap">
        <span><?= e($university['name']) ?> &middot; <?= e($university['press']) ?></span>
        <nav class="links" aria-label="University links">
            <a href="<?= e($university['library']) ?>">Library</a>
			<a href="https://www.uicto.edu.ng">University Website</a>
			<a href="https://www.campusxpressclientsise.onrender.com">Campus News</a>
			<a href="https://uictoenreadlms.com">School LMS</a>
            <a href="<?= e($university['research']) ?>">Journal News</a>
            <a href="mailto:<?= e($journal['email']) ?>"><?= e($journal['email']) ?></a>
        </nav>
    </div>
</div>

<!-- ================= MASTHEAD ================= -->
<header class="site">
    <div class="wrap masthead">
        <div class="brand">
            <div class="crest" aria-hidden=""><img src = 'img/logo.png' width = '100'></div>
            <div class="brand-text">
                <span class="uni"><?= e($university['name']) ?></span>
                <h1><a href="/" style="color:inherit"><?= e($journal['title']) ?></a></h1>
                <p class="tagline"><?= e($journal['tagline']) ?></p>
            </div>
        </div>

        <div class="masthead-actions">
            <form class="search" action="search.php" method="get" role="search">
                <label for="q" style="position:absolute;left:-9999px">Search</label>
                <input type="search" id="q" name="q" placeholder="Search articles, authors, DOIs…"
                       value="<?= e($_GET['q'] ?? '') ?>">
                <button type="submit">Search</button>
            </form>
            <a class="btn btn-gold" href="submit.php">Submit paper</a>
        </div>
    </div>
</header>

<!-- ================= NAV ================= -->
<nav class="main" aria-label="Main navigation">
    <div class="wrap">
        <ul>
            <?php foreach ($nav as $label => $href): ?>
                <li>
                    <a href="<?= e($href) ?>"<?= $href === '#' ? ' aria-current="page"' : '' ?>>
                        <?= e($label) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

<!-- ================= MAIN ================= -->
<div class="wrap layout">

    <main>

        <!-- ---------- Stats strip ---------- -->
        <div class="stats" aria-label="Journal metrics">
            <?php foreach ($stats as $s): ?>
                <div class="stat">
                    <span class="num"><?= e($s['value']) ?></span>
                    <span class="lbl"><?= e($s['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ---------- Current issue hero ---------- -->
        <section class="hero" id="issue" aria-labelledby="issue-heading">
            <div class="cover" aria-hidden="true">
                <div>
                    <div class="uni"><?= e($university['short']) ?> University Press</div>
                    <div class="ttl" style="margin-top:10px"><?= e($journal['abbr']) ?></div>
                </div>
                <div class="vol">
                    Vol. <?= e((string) $currentIssue['volume']) ?>,
                    No. <?= e((string) $currentIssue['number']) ?>
                    &middot; <?= e((string) $currentIssue['year']) ?>
                </div>
            </div>

            <div>
			
                <span class="kicker">Current Issue</span>	
                <h2 id="issue-heading"><?= e($currentIssue['theme']) ?></h2>
                <p class="lead">
                    Volume <?= e((string) $currentIssue['volume']) ?>,
                    Issue <?= e((string) $currentIssue['number']) ?>
                    (<?= e($currentIssue['season']) ?> <?= e((string) $currentIssue['year']) ?>)
                    &middot; Published <?= e(formatDate($currentIssue['published'])) ?>
                </p>
                <div class="btn-row">
                    <a class="btn btn-primary" href="current">Read the issue</a>
                    <a class="btn btn-ghost" href="guidlines.php">Author guidelines</a>
                </div>
            </div>
        </section>
		
		
			
<section id="board" aria-labelledby="board-heading">
<h2 id="board-heading"></h2>
<h2><span class="kicker">JOURNALS</span></h2>	
		<div class="board-grid">						
<p ALIGN = "right">
<a href ="faofcomputing.php"><img src ="imgs/computing.png" width ="200" height = "200" alt "computing journal"></a>
<p ALIGN = "right">
<a href ="faofengineering.php"><<img src ="imgs/engineering.png" width ="200" height = "200"alt "Engineering journal ></a>
<p ALIGN = "right">
<a href ="faofcommandsosc.php"><img src ="imgs/commandsos.png" width ="200" height = "200" alt "Social Science journal></a>
<p ALIGN = "right">
<a href ="faofhealthscience.php"><img src ="imgs/healthsciences.png" width ="200" height = "200" alt "Health Science journal></a>
<p ALIGN = "right">
<a href ="faofsurveying.php"><img src ="imgs/surveying.png" width ="200" height = "200"alt "Surveying journal ></a>
</p>
			
 </div>
        </section>
	
		




        

        <!-- ---------- Editorial board ---------- -->
        <section id="board" aria-labelledby="board-heading">
            <div class="section-head">
                <h2 id="board-heading">Editorial Board</h2>
                <a class="more" href="editorial-board.php">Full board listing →</a>
            </div>

            <div class="board-grid">
                <?php foreach ($board as $member): ?>
                    <div class="board-card">
                        <div class="name"><?= e($member['name']) ?></div>
                        <div class="role"><?= e($member['role']) ?></div>
                        <div class="affil"><?= e($member['affil']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>






        <!-- ---------- Announcements ---------- -->
        <section id="announcements" aria-labelledby="ann-heading">
            <div class="section-head">
                <h2 id="ann-heading">Announcements</h2>
                <a class="more" href="announcement.php">All announcements →</a>
            </div>

            <?php foreach ($announcements as $n): ?>
                <div class="ann">
                    <span class="tag"><?= e($n['tag']) ?></span>
                    <h3><?= e($n['title']) ?></h3>
                    <time datetime="<?= e($n['date']) ?>"><?= e(formatDate($n['date'])) ?></time>
                    <p><?= e($n['body']) ?></p>
                </div>
            <?php endforeach; ?>
        </section>

    </main>

    <!-- ================= SIDEBAR ================= -->
    <aside>

        <div class="panel" id="submissions">
            <h3>Submit Your paper</h3>
            <p>
                <strong>UICTO </strong>welcomes original research, reviews, and short communications.
                All submissions undergo double-blind peer review.
            </p>
            <a class="btn btn-gold" href="submit.php">Submit a manuscript</a>
        </div>

        <div class="panel" id="about">
            <h3>About the Journal</h3>
            <p>
                Founded in <?= e((string) $journal['founded']) ?>, <?= e($journal['abbr']) ?>
                is published twice yearly by <?= e($university['press']) ?> and is fully
                open access under a CC BY 4.0 licence.
            </p>
            <ul class="contact-list">
                <li><strong>Editor-in-Chief:</strong> <?= e($journal['editor']) ?></li>
                <li><strong>ISSN:</strong> <?= e($journal['issn']) ?></li>
                <li><strong>e-ISSN:</strong> <?= e($journal['eissn']) ?></li>
            </ul>
        </div>

        <div class="panel">
            <h3>Indexed In</h3>
            <div class="tags">
                <?php foreach ($indexedIn as $db): ?>
                    <span><?= e($db) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="panel" id="contact">
            <h3>Editorial Office</h3>
            <ul class="contact-list">
                <li><?= e($university['press']) ?></li>
                <li><?= e($university['address']) ?></li>
                <li><?= e($university['phone']) ?></li>
                <li><a href="mailto:<?= e($journal['email']) ?>"><?= e($journal['email']) ?></a></li>
            </ul>
        </div>

    </aside>
</div>

<!-- ================= FOOTER ================= -->
<footer class="site">
    <div class="wrap">
        <div class="foot-grid">
            <div>
                <h4><?= e($journal['title']) ?></h4>
                <p style="margin:0"><?= e($journal['tagline']) ?></p>
            </div>
            <div>
                <h4>Explore</h4>
                <ul>
                    <li><a href="current.php">Current Issue</a></li>
                    <li><a href="archives.php">Archives</a></li>
                    <li><a href="editorial-board">Editorial Board</a></li>
                    <li><a href="announcement.php">Announcements</a></li>
                </ul>
            </div>
            <div>
                <h4>Authors</h4>
                <ul>
                    <li><a href="guidlines.php">Author Guidelines</a></li>
                    <li><a href="#submissions">Peer-Review Policy</a></li>
                    <li><a href="#submissions">Publication Ethics</a></li>
                    <li><a href="authors.php">Submit a Manuscript</a></li>
                </ul>
            </div>
            <div>
                <h4>University</h4>
                <ul>
                   
                      <li><a href="<?= e($university['library']) ?>">Library</a>
			<li><a href="https://www.uicto.edu.ng">University Website</a></li></li>
			<li><a href="https://www.campusxpressclientsise.onrender.com">Campus News</a></li>
			</li><a href="https://uictoenreadlms.com">School LMS</a><li>
			   </ul>
            </div>
        </div>

        <div class="copyright">
            <span>
                &copy; <?= e((string) $issueYear) ?> <?= e($university['name']) ?>.
                Published by <?= e($university['press']) ?>.
            </span>
            <span>ISSN <?= e($journal['issn']) ?> &middot; e-ISSN <?= e($journal['eissn']) ?></span>
        </div>
    </div>
	
</footer>

</body>
</html>