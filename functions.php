<?php
/**
 * includes/functions.php
 * Small helper functions shared by all pages.
 */

/** Escape output to prevent XSS. */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Redirect and stop the script. */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/** Store a one-time message (shown after redirect). */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Print and clear the one-time message as a Bootstrap alert. */
function show_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show" role="alert">'
           . e($f['message'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

/** CSRF token: protects forms from cross-site submissions. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Hidden input to place inside every POST form. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Stop the request if the CSRF token is missing or wrong. */
function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(400);
        die('Invalid request (CSRF token mismatch). Go back and try again.');
    }
}

/** Average of the four rating criteria (1 to 5). */
function average_rating($t, $c, $p, $pr): float
{
    return ($t + $c + $p + $pr) / 4;
}

/** Bootstrap color for an average rating. */
function rating_color(float $avg): string
{
    if ($avg >= 4.5) return 'success';
    if ($avg >= 3.5) return 'primary';
    if ($avg >= 2.5) return 'warning';
    return 'danger';
}

/** Text label for an average rating. */
function rating_label(float $avg): string
{
    if ($avg >= 4.5) return 'Outstanding';
    if ($avg >= 3.5) return 'Very Satisfactory';
    if ($avg >= 2.5) return 'Satisfactory';
    if ($avg >= 1.5) return 'Fair';
    return 'Poor';
}

/** Render a 1-5 <select> for one rating criterion. */
function rating_select(string $name, $selected = ''): string
{
    $labels = [5 => 'Excellent', 4 => 'Very Good', 3 => 'Good', 2 => 'Fair', 1 => 'Poor'];
    $html = '<select class="form-select" name="' . e($name) . '" required>';
    $html .= '<option value="">Select...</option>';
    foreach ($labels as $value => $label) {
        $sel = ((string)$selected === (string)$value) ? ' selected' : '';
        $html .= "<option value=\"$value\"$sel>$value - $label</option>";
    }
    return $html . '</select>';
}
