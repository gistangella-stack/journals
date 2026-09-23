<?php
// submit_paper.php — Insert research paper into MySQLi database

require_once __DIR__ . '/config.php';
//require_once 'UICTO-JOURNALS' . '/config.php';

session_start();

// ---------- CSRF token ----------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors  = [];
$success = false;
$paperId = null;

// ---------- Handle POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) CSRF check
    if (!isset($_POST['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Security token mismatch. Please refresh and try again.';
    }

    // 2) Collect & sanitize inputs
    $title       = trim($_POST['title']       ?? '');
    $authors     = trim($_POST['authors']     ?? '');
    $email       = trim($_POST['email']       ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $department  = trim($_POST['department']  ?? '');
    $country     = trim($_POST['country']     ?? '');
    $track       = trim($_POST['track']       ?? '');
    $keywords    = trim($_POST['keywords']    ?? '');
    $abstract    = trim($_POST['abstract']    ?? '');

    // 3) Validate fields
    if ($title === '')                         $errors[] = 'Paper title is required.';
    elseif (mb_strlen($title) > 300)           $errors[] = 'Title must not exceed 300 characters.';

    if ($authors === '')                       $errors[] = 'Author name(s) are required.';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
                                               $errors[] = 'A valid email address is required.';

    if ($institution === '')                   $errors[] = 'Institution is required.';
    if ($track === '')                         $errors[] = 'Please select a subject track.';
    if ($keywords === '')                      $errors[] = 'Keywords are required.';
    elseif (mb_strlen($keywords) > 500)        $errors[] = 'Keywords must not exceed 500 characters.';

    if ($abstract === '')                      $errors[] = 'Abstract is required.';
    elseif (mb_strlen($abstract) < 100)        $errors[] = 'Abstract must be at least 100 characters.';
    elseif (mb_strlen($abstract) > 5000)       $errors[] = 'Abstract must not exceed 5000 characters.';

    // 4) Validate uploaded file
    $uploadedFile = null;
    if (!isset($_FILES['paper']) || $_FILES['paper']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please attach your paper file.';
    } else {
        $file = $_FILES['paper'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload failed (error code: ' . $file['error'] . ').';
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File exceeds the 10 MB limit.';
        } elseif ($file['size'] === 0) {
            $errors[] = 'Uploaded file is empty.';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $GLOBALS['ALLOWED_EXTENSIONS'], true)) {
                $errors[] = 'Invalid extension. Allowed: ' 
                          . implode(', ', $GLOBALS['ALLOWED_EXTENSIONS']);
            } else {
                // Use finfo for accurate MIME detection
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($file['tmp_name']);

                if (!in_array($mime, $GLOBALS['ALLOWED_MIME_TYPES'], true)) {
                    $errors[] = 'Invalid file type. Only PDF, DOC, and DOCX are accepted.';
                } else {
                    $uploadedFile = [
                        'tmp'  => $file['tmp_name'],
                        'ext'  => $ext,
                        'size' => (int) $file['size'],
                        'mime' => $mime,
                    ];
                }
            }
        }
    }

    // 5) Duplicate submission check (same email + same title)
    if (empty($errors)) {
        try {
            $conn = getDB();
            $stmt = $conn->prepare(
                "SELECT id FROM research_papers 
                 WHERE email = ? AND title = ? LIMIT 1"
            );
            $stmt->bind_param('ss', $email, $title);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors[] = 'A paper with this title has already been submitted under this email.';
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            error_log('Duplicate check failed: ' . $e->getMessage());
            $errors[] = 'Server error. Please try again later.';
        }
    }

    // 6) Save file + insert into database
    if (empty($errors) && $uploadedFile !== null) {

        // Ensure upload directory exists
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        // Build unique safe filename
        $safeTitle   = preg_replace('/[^A-Za-z0-9]+/', '_', mb_substr($title, 0, 50));
        $uniqueName  = date('Ymd_His') . '_' . bin2hex(random_bytes(4))
                       . '_' . trim($safeTitle, '_') . '.' . $uploadedFile['ext'];
        $destination = UPLOAD_DIR . $uniqueName;

        if (!move_uploaded_file($uploadedFile['tmp'], $destination)) {
            $errors[] = 'Failed to save uploaded file. Check folder permissions.';
        } else {
            try {
                $conn = getDB();

                // ---- Prepare INSERT with MySQLi ----
                $sql = "INSERT INTO research_papers
                          (title, authors, email, institution, department, country,
                           track, keywords, abstract, file_name, file_size,
                           file_type, status, ip_address)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";

                $stmt = $conn->prepare($sql);

                // Nullable fields need explicit null handling
                $departmentVal = $department !== '' ? $department : null;
                $countryVal    = $country    !== '' ? $country    : null;
                $ipAddress     = $_SERVER['REMOTE_ADDR'] ?? null;

                /*
                 * bind_param type string:
                 *   s = string, i = integer, d = double, b = blob
                 *
                 * 12 parameters:
                 *   title (s), authors (s), email (s), institution (s),
                 *   department (s), country (s), track (s), keywords (s),
                 *   abstract (s), file_name (s), file_size (i), file_type (s),
                 *   ip_address (s)
                 */
                $stmt->bind_param(
                    'ssssssssssiss',
                    $title,
                    $authors,
                    $email,
                    $institution,
                    $departmentVal,
                    $countryVal,
                    $track,
                    $keywords,
                    $abstract,
                    $uniqueName,
                    $uploadedFile['size'],
                    $uploadedFile['mime'],
                    $ipAddress
                );

                $stmt->execute();
                $paperId = (int) $conn->insert_id;
                $stmt->close();

                $success = true;

                // Regenerate CSRF token after successful submission
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                // Optional confirmation email
                sendConfirmationEmail($email, $title, $paperId);

            } catch (mysqli_sql_exception $e) {
                error_log('Insert failed: ' . $e->getMessage());
                @unlink($destination); // Remove orphaned file
                $errors[] = 'Database error. Your submission was not saved.';
            }
        }
    }
}

/**
 * Send confirmation email to corresponding author.
 */
function sendConfirmationEmail(string $to, string $title, int $id): void
{
    $subject = 'Paper Submission Received — ' . SITE_NAME;
    $message = "Dear Author,\n\n"
             . "Thank you for submitting your paper to " . SITE_NAME . ".\n\n"
             . "Reference ID: #{$id}\n"
             . "Title: {$title}\n\n"
             . "Our editorial team will contact you after review.\n\n"
             . "Regards,\nEditorial Office\n" . SITE_NAME;

    $headers = "From: editor@university.edu\r\n"
             . "Reply-To: editor@university.edu\r\n"
             . "X-Mailer: PHP/" . phpversion();

    @mail($to, $subject, $message, $headers);
}

/**
 * Repopulate form values after validation failure.
 */
function old(string $key, string $default = ''): string
{
    return htmlspecialchars($_POST[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit Paper | <?= SITE_NAME ?></title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #eef2f7; color: #2c3e50;
        line-height: 1.6; padding: 24px;

    }
    .container {
        max-width: 880px; margin: 0 auto; background: #fff;
        border-radius: 10px; box-shadow: 0 6px 24px rgba(0,0,0,0.08);
        padding: 40px;
    }
    header {
        text-align: center; margin-bottom: 28px;
        border-bottom: 2px solid #1a3c6e; padding-bottom: 20px;
    }
    header h1 { color: #1a3c6e; font-size: 1.85rem; }
    header p  { color: #7f8c8d; margin-top: 6px; }

    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .grid .full { grid-column: 1 / -1; }

    .form-group { margin-bottom: 18px; }
    label {
        display: block; font-weight: 600; margin-bottom: 6px;
        color: #1a3c6e; font-size: 0.95rem;
    }
    label .req { color: #c0392b; }

    input[type="text"], input[type="email"], select, textarea {
        width: 100%; padding: 10px 12px; border: 1px solid #ccd1d9;
        border-radius: 6px; font-size: 1rem; font-family: inherit;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    input:focus, select:focus, textarea:focus {
        outline: none; border-color: #1a3c6e;
        box-shadow: 0 0 0 3px rgba(26,60,110,0.12);
    }
    textarea { resize: vertical; min-height: 150px; }
    .hint { font-size: 0.82rem; color: #95a5a6; margin-top: 4px; }

    .file-box {
        border: 2px dashed #ccd1d9; padding: 22px; text-align: center;
        border-radius: 8px; background: #fafbfc; cursor: pointer;
        transition: 0.2s;
    }
    .file-box:hover { border-color: #1a3c6e; background: #f0f4fa; }
    .file-box input[type="file"] { display: none; }
    .file-box label { cursor: pointer; color: #1a3c6e; font-weight: 600; }
    .file-name { margin-top: 8px; color: #27ae60; font-weight: 600; font-size: 0.9rem; }

    .btn {
        background: #1a3c6e; color: #fff; border: none;
        padding: 14px 28px; font-size: 1.05rem; border-radius: 6px;
        cursor: pointer; width: 100%; font-weight: 600;
        letter-spacing: 0.4px; transition: background 0.2s;
    }
    .btn:hover { background: #14305a; }

    .alert { padding: 14px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.95rem; }
    .alert-error   { background: #fdecea; border-left: 4px solid #c0392b; color: #922b21; }
    .alert-error ul { margin-left: 20px; }
    .alert-success { background: #eafaf1; border-left: 4px solid #27ae60; color: #1e8449; }

    .back-link {
        display: inline-block; margin-top: 18px;
        color: #1a3c6e; text-decoration: none; font-weight: 600;
    }
    .back-link:hover { text-decoration: underline; }

    @media (max-width: 640px) {
        .container { padding: 22px 16px; }
        .grid { grid-template-columns: 1fr; }
        header h1 { font-size: 1.5rem; }
    }
</style>
</head>
<body>
<div class="container">
    <header>
        <h1><?= SITE_NAME ?></h1>
        <p>Submit Your Research Paper</p>
    </header>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <strong>✅ Submission Successful!</strong><br>
            Your paper has been received. Reference ID:
            <strong>#<?= (int) $paperId ?></strong>.<br>
            A confirmation email has been sent to
            <strong><?= e($email) ?></strong>.
        </div>
        <a href="submit.php" class="back-link">&larr; Submit another paper</a>

    <?php else: ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Please fix the following:</strong>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="paperForm" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= e($_SESSION['csrf_token']) ?>">

            <!-- Title -->
            <div class="form-group">
                <label for="title">Paper Title <span class="req">*</span></label>
                <input type="text" id="title" name="title" maxlength="300" required
                       value="<?= old('title') ?>"
                       placeholder="Full title of your research paper">
            </div>

            <div class="grid">
                <!-- Authors -->
                <div class="form-group full">
                    <label for="authors">Author(s) <span class="req">*</span></label>
                    <input type="text" id="authors" name="authors" required
                           value="<?= old('authors') ?>"
                           placeholder="e.g., John Doe, Jane Smith, Ahmed Khan">
                    <div class="hint">Separate multiple authors with commas.</div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address<span class="req">*</span></label>
                    <input type="email" id="email" name="email" required
                           value="<?= old('email') ?>"
                           placeholder="author@university.edu">
                </div>

                <!-- Country -->
                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country"
                           value="<?= old('country') ?>"
                           placeholder="e.g., Nigeria">
                </div>

                <!-- Institution -->
                <div class="form-group">
                    <label for="institution">Institution <span class="req">*</span></label>
                    <input type="text" id="institution" name="institution" required
                           value="<?= old('institution') ?>"
                           placeholder="University / Organization">
                </div>

                <!-- Department -->
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department"
                           value="<?= old('department') ?>"
                           placeholder="e.g., Computer Science">
                </div>

                <!-- Track -->
                <div class="form-group full">
                    <label for="track">Subject Track <span class="req">*</span></label>
                    <select id="track" name="track" required>
                        <option value="">-- Select a track --</option>
                        <?php
                        $tracks = [
                            'Computer Science & IT',
                            'Engineering & Technology',
                            'Life Sciences & Biology',
                            'Physical Sciences & Mathematics',
                            'Social Sciences & Humanities',
                            'Business & Economics',
                            'Medicine & Health Sciences',
                            'Environmental Studies',
                            'Education & Pedagogy',
							 'Entreprenueship',
                            'Law & Political Science'
                        ];
                        $selected = $_POST['track'] ?? '';
                        foreach ($tracks as $t) {
                            $sel = ($selected === $t) ? ' selected' : '';
                            echo '<option value="' . e($t) . '"' . $sel . '>'
                               . e($t) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Keywords -->
                <div class="form-group full">
                    <label for="keywords">Keywords <span class="req">*</span></label>
                    <input type="text" id="keywords" name="keywords" required
                           value="<?= old('keywords') ?>"
                           placeholder="e.g., machine learning, neural networks">
                    <div class="hint">Separate keywords with commas.</div>
                </div>
            </div>

            <!-- Abstract -->
            <div class="form-group">
                <label for="abstract">Abstract <span class="req">*</span></label>
                <textarea id="abstract" name="abstract" required
                          minlength="100" maxlength="5000"
                          placeholder="Paste or type your paper abstract here (100 – 5000 characters)..."><?= old('abstract') ?></textarea>
                <div class="hint"><span id="charCount">0</span> / 5000 characters</div>
            </div>

            <!-- File upload -->
            <div class="form-group">
                <label>Paper File <span class="req">*</span></label>
                <div class="file-box" id="fileBox">
                    <input type="file" id="paper" name="paper" required
                           accept=".pdf,.doc,.docx">
                    <label for="paper">📄 Click to browse or drag &amp; drop your file</label>
                    <div class="hint">Accepted: PDF, DOC, DOCX — Max 10 MB</div>
                    <div class="file-name" id="fileName"></div>
                </div>
            </div>

            <button type="submit" class="btn">Submit Paper</button>
        </form>
    <?php endif; ?>
</div>

<script>
    // Show selected file name
    const fileInput = document.getElementById('paper');
    const fileName  = document.getElementById('fileName');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            fileName.textContent = this.files.length
                ? '📎 ' + this.files[0].name : '';
        });
    }

    // Drag & drop
    const fileBox = document.getElementById('fileBox');
    if (fileBox) {
        ['dragenter', 'dragover'].forEach(evt =>
            fileBox.addEventListener(evt, e => {
                e.preventDefault();
                fileBox.style.borderColor = '#1a3c6e';
                fileBox.style.background  = '#eef3fb';
            })
        );
        ['dragleave', 'drop'].forEach(evt =>
            fileBox.addEventListener(evt, e => {
                e.preventDefault();
                fileBox.style.borderColor = '#ccd1d9';
                fileBox.style.background  = '#fafbfc';
            })
        );
        fileBox.addEventListener('drop', e => {
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                fileName.textContent = '📎 ' + files[0].name;
            }
        });
    }

    // Live character counter
    const abstract  = document.getElementById('abstract');
    const charCount = document.getElementById('charCount');
    if (abstract && charCount) {
        const update = () => charCount.textContent = abstract.value.length;
        abstract.addEventListener('input', update);
        update();
    }
</script>
</body>
</html>