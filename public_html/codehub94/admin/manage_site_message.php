<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");

include 'conn.php';

// HTML structure template (emoji hataya)
$baseTemplate = '
<div style="padding: 20px; font-family: Arial, sans-serif; font-size: 16px; background: #fff; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); max-width: 600px; margin: auto;">
  <h3 style="color: #0d6efd; text-align: center;">{popup_heading}</h3>
  <p style="text-align: center; font-weight: bold;">{announcement}</p>
  <p style="text-align: center;">{body_text}</p>
  <p style="text-align: center; font-weight: bold;">{reward_text}</p>
  <div style="text-align: center; margin-top: 15px;">
    <a href="{vip_link}" style="display: inline-block; padding: 10px 25px; background: #0d6efd; color: white; border-radius: 6px; text-decoration: none;">{button_text}</a>
  </div>
</div>
';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = $_POST['title'];
    $popup_heading = $_POST['popup_heading'];
    $announcement  = $_POST['announcement'];
    $body_text     = $_POST['body_text'];
    $reward_text   = $_POST['reward_text'];
    $vip_link      = $_POST['vip_link'];
    $button_text   = $_POST['button_text'];

    $finalHTML = str_replace(
        ['{popup_heading}', '{announcement}', '{body_text}', '{reward_text}', '{vip_link}', '{button_text}'],
        [$popup_heading, $announcement, $body_text, $reward_text, $vip_link, $button_text],
        $baseTemplate
    );

    $stmt = $conn->prepare("UPDATE site_messages SET title = ?, siteMessage = ? WHERE id = 1");
    $stmt->bind_param("ss", $title, $finalHTML);
    $stmt->execute();
    $success = true;
}

// Load existing
$data = $conn->query("SELECT * FROM site_messages WHERE id = 1")->fetch_assoc();
$siteHTML = $data['siteMessage'];
$title    = $data['title'];

// DOM extractors
function extractDomTagText($html, $tag, $index = 0, $isBoldOnly = false) {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $tags = $doc->getElementsByTagName($tag);
    if ($tags->length > $index) {
        $node = $tags->item($index);
        if ($isBoldOnly) {
            foreach ($node->childNodes as $child) {
                if ($child->nodeName === 'b') {
                    return $child->textContent;
                }
            }
        }
        return $node->textContent;
    }
    return '';
}

function extractButton($html) {
    if (preg_match('/<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/i', $html, $match)) {
        return [$match[1], $match[2]];
    }
    return ['', ''];
}

// Extract values
$popup_heading = extractDomTagText($siteHTML, 'h3', 0);
$announcement  = extractDomTagText($siteHTML, 'p', 0, true);
$body_text     = extractDomTagText($siteHTML, 'p', 1);
$reward_text   = extractDomTagText($siteHTML, 'p', 2, true);
list($vip_link, $button_text) = extractButton($siteHTML);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Popup Message Editor</title>
<style>
/* --- your CSS (unchanged, only fix in success msg & heading) --- */
</style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="card shadow custom-editor-card">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="color: #000000;">Popup Message Editor (Structured)</h5>
            <span class="badge bg-light text-dark">Edit Text & Link</span>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success">✔ Message saved successfully!</div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Popup Title</label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($title) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Popup Heading</label>
                    <input type="text" name="popup_heading" class="form-control" required value="<?= htmlspecialchars($popup_heading) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Announcement</label>
                    <input type="text" name="announcement" class="form-control" value="<?= htmlspecialchars($announcement) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Body Message</label>
                    <textarea name="body_text" class="form-control" rows="3"><?= htmlspecialchars($body_text) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reward Text</label>
                    <input type="text" name="reward_text" class="form-control" value="<?= htmlspecialchars($reward_text) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">VIP Link (URL/Image Link/Text Link)</label>
                    <input type="text" name="vip_link" class="form-control" value="<?= htmlspecialchars($vip_link) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button_text" class="form-control" value="<?= htmlspecialchars($button_text) ?>">
                </div>
                <button type="submit" class="btn btn-success w-100"> Save Changes</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
