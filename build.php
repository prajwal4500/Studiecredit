<?php
/*
 * ============================================================
 *  build.php — Credit Score Engine Builder
 *  
 *  STUDENT REFERENCE CODE
 *  This is a simplified standalone version — no database needed.
 *  The engine configuration is stored in PHP $_SESSION.
 *  
 *  In a production system (like myfintechs.com) you would:
 *  1. Store engine config in a MySQL database table
 *  2. Require user login before accessing this page
 *  3. Generate a unique public URL (slug) per student
 *  
 *  Key concepts demonstrated here:
 *  - Collecting weightages from a form (POST)
 *  - Validating that weightages sum to exactly 100
 *  - Setting approval/hold thresholds
 *  - Storing engine config in session
 *  - Generating the public applicant URL
 * ============================================================
 */

session_start();

$msg = '';
$msg_type = '';

// ── Load engine from session (replaces database in this demo) ─────
$engine = $_SESSION['engine'] ?? null;

// ── SAVE ENGINE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $company  = trim($_POST['company_name'] ?? '');
    $tagline  = trim($_POST['tagline']      ?? '');

    // Weightages — each factor gets a percentage of the total score
    $w_income     = max(0, min(100, (int)($_POST['w_income']     ?? 20)));
    $w_dscr       = max(0, min(100, (int)($_POST['w_dscr']       ?? 30)));
    $w_debt       = max(0, min(100, (int)($_POST['w_debt']       ?? 20)));
    $w_employment = max(0, min(100, (int)($_POST['w_employment'] ?? 15)));
    $w_repayment  = max(0, min(100, (int)($_POST['w_repayment']  ?? 15)));

    // Thresholds — student decides where Approve/Hold/Reject lines are
    $threshold_approve = max(1, min(99, (int)($_POST['threshold_approve'] ?? 70)));
    $threshold_hold    = max(1, min(99, (int)($_POST['threshold_hold']    ?? 50)));

    $total_weight = $w_income + $w_dscr + $w_debt + $w_employment + $w_repayment;

    // ── VALIDATION ────────────────────────────────────────────────
    if (strlen($company) < 2) {
        $msg = "Please enter your company name.";
        $msg_type = "error";
    } elseif ($total_weight !== 100) {
        $msg = "Weightages must total exactly 100. Your total: {$total_weight}.";
        $msg_type = "error";
    } elseif ($threshold_hold >= $threshold_approve) {
        $msg = "Hold threshold must be lower than Approve threshold.";
        $msg_type = "error";
    } else {
        // ── FIX: Generate slug correctly ──────────────────────────
        // BUG WAS HERE: strtolower() was called AFTER preg_replace,
        // so uppercase letters (P, r, a...) were stripped before lowercasing.
        // FIX: lowercase first, THEN strip non-alphanumeric characters.
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($company));
        $slug = trim($slug, '-'); // remove leading/trailing hyphens

        // ── SAVE TO SESSION ───────────────────────────────────────
        // In production: INSERT or UPDATE in MySQL database
        $_SESSION['engine'] = [
            'company_name'      => $company,
            'tagline'           => $tagline,
            'w_income'          => $w_income,
            'w_dscr'            => $w_dscr,
            'w_debt'            => $w_debt,
            'w_employment'      => $w_employment,
            'w_repayment'       => $w_repayment,
            'threshold_approve' => $threshold_approve,
            'threshold_hold'    => $threshold_hold,
            'slug'              => $slug,
        ];
        $engine = $_SESSION['engine'];
        $msg = "✅ Engine saved! Share the link below with the public.";
        $msg_type = "success";
    }
}

// Public URL (in production this would be a unique slug from the database)
$public_url = $engine ? 'apply.php?slug=' . $engine['slug'] : '';

function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Engine Builder · Credit Score Engine</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#ffffff;
  --bg2:#f7f7f5;
  --bg3:#f0eeeb;
  --ink:#0a0a0a;
  --ink2:#1a1a1a;
  --ink3:#444;
  --muted:#888;
  --border:#e0ddd8;
  --green:#16a34a;
  --red:#dc2626;
  --amber:#d97706;
  --green-bg:rgba(22,163,74,.06);
  --green-border:rgba(22,163,74,.2);
  --red-bg:rgba(220,38,38,.06);
  --red-border:rgba(220,38,38,.2);
}
body{font-family:'DM Sans',sans-serif;background:var(--bg2);color:var(--ink);min-height:100vh}
body::before{
  content:'';position:fixed;inset:0;
  background-image:linear-gradient(var(--border) 1px,transparent 1px),
    linear-gradient(90deg,var(--border) 1px,transparent 1px);
  background-size:48px 48px;opacity:.25;pointer-events:none;z-index:0
}

.topbar{display:flex;justify-content:space-between;align-items:center;padding:16px 40px;
  background:rgba(255,255,255,.97);border-bottom:1px solid var(--border);
  position:sticky;top:0;z-index:10;backdrop-filter:blur(8px)}
.logo{font-family:'Playfair Display',serif;font-weight:900;font-size:1.1rem;color:var(--ink);letter-spacing:-.02em}
.logo span{color:var(--muted)}
.nav-btn{color:var(--muted);font-size:.78rem;text-decoration:none;font-family:'DM Sans',sans-serif;
  padding:6px 16px;border:1px solid var(--border);border-radius:2px;transition:.15s}
.nav-btn:hover{color:var(--ink);border-color:var(--ink)}

.wrap{max-width:800px;margin:0 auto;padding:36px 24px 80px;position:relative;z-index:1}
.page-title{font-family:'Playfair Display',serif;font-weight:900;font-size:1.6rem;color:var(--ink);
  margin-bottom:6px;letter-spacing:-.02em}
.page-sub{font-size:.84rem;color:var(--muted);line-height:1.8;margin-bottom:28px;font-weight:300}

/* ALERT */
.alert{padding:12px 16px;border-radius:2px;margin-bottom:20px;font-size:.84rem;line-height:1.5}
.alert-success{background:var(--green-bg);border:1px solid var(--green-border);color:var(--green)}
.alert-error{background:var(--red-bg);border:1px solid var(--red-border);color:var(--red)}

/* CARDS */
.card{background:#fff;border:1px solid var(--border);border-radius:2px;padding:24px;margin-bottom:14px}
.card-title{font-family:'DM Mono',monospace;font-size:.62rem;font-weight:500;color:var(--muted);
  letter-spacing:.15em;text-transform:uppercase;
  margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border)}

/* FORM */
.fg{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.fg label{font-family:'DM Mono',monospace;font-size:.6rem;color:var(--muted);letter-spacing:.12em;text-transform:uppercase}
.fg input{background:#fff;border:1.5px solid var(--border);border-radius:2px;
  color:var(--ink);padding:11px 14px;font-size:.9rem;outline:none;transition:.2s;
  font-family:'DM Sans',sans-serif}
.fg input:focus{border-color:var(--ink);box-shadow:0 0 0 3px rgba(10,10,10,.06)}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:600px){.two-col{grid-template-columns:1fr}}

/* WEIGHTAGE ROWS */
.w-row{display:grid;grid-template-columns:180px 1fr 64px;align-items:center;
  gap:16px;margin-bottom:18px;padding-bottom:18px;border-bottom:1px solid var(--border)}
.w-row:last-of-type{border:none;margin-bottom:0;padding-bottom:0}
@media(max-width:600px){.w-row{grid-template-columns:1fr;gap:6px}}
.w-label{font-size:.85rem;font-weight:600;color:var(--ink)}
.w-desc{font-size:.7rem;color:var(--muted);margin-top:3px;line-height:1.6;font-weight:300}
.w-slider{-webkit-appearance:none;width:100%;height:4px;
  background:var(--border);border-radius:99px;outline:none;cursor:pointer}
.w-slider::-webkit-slider-thumb{-webkit-appearance:none;width:16px;height:16px;
  border-radius:50%;background:var(--ink);cursor:pointer;border:2px solid #fff;
  box-shadow:0 1px 4px rgba(0,0,0,.2)}
.w-num{background:#fff;border:1.5px solid var(--border);border-radius:2px;
  color:var(--ink);font-family:'DM Mono',monospace;font-size:.95rem;font-weight:500;
  padding:6px;text-align:center;outline:none;width:62px;transition:.15s}
.w-num:focus{border-color:var(--ink)}

.total-box{background:var(--bg2);border:1px solid var(--border);
  border-radius:2px;padding:14px 18px;margin-top:18px;
  display:flex;align-items:center;justify-content:space-between}
.total-num{font-family:'Playfair Display',serif;font-weight:900;font-size:2.6rem;line-height:1;color:var(--ink)}
.ok{color:var(--green)!important}
.warn{color:var(--red)!important}

/* THRESHOLDS */
.thresh-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px}
.thresh-card{background:var(--bg2);border:1.5px solid var(--border);border-radius:2px;padding:16px;text-align:center}
.thresh-label{font-family:'DM Mono',monospace;font-size:.6rem;color:var(--muted);
  letter-spacing:.12em;text-transform:uppercase;margin-bottom:8px}
.thresh-input{width:100%;background:transparent;border:none;outline:none;
  font-family:'Playfair Display',serif;font-weight:900;font-size:2.2rem;color:var(--ink);text-align:center}
.thresh-note{font-size:.7rem;color:var(--muted);margin-top:4px;font-weight:300}

/* PUBLISH BOX */
.pub-box{background:#fff;border:1.5px solid var(--green-border);
  border-radius:2px;padding:22px;margin-top:20px}
.pub-title{font-family:'DM Mono',monospace;font-size:.6rem;font-weight:500;
  color:var(--green);letter-spacing:.15em;text-transform:uppercase;margin-bottom:12px}
.pub-url{font-family:'DM Mono',monospace;font-size:.78rem;color:var(--ink);
  background:var(--bg2);border:1px solid var(--border);
  padding:10px 14px;border-radius:2px;word-break:break-all;margin-bottom:12px}

/* BUTTON */
.btn{display:inline-flex;align-items:center;gap:6px;padding:13px 28px;border-radius:2px;
  border:none;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.88rem;transition:.15s}
.btn-primary{background:var(--ink);color:#fff;
  width:100%;justify-content:center;margin-top:18px;padding:14px}
.btn-primary:disabled{opacity:.3;cursor:not-allowed}
.btn-primary:not(:disabled):hover{background:var(--ink2)}

/* CODE NOTE */
.code-note{background:var(--bg2);border-left:3px solid var(--border);
  padding:14px 18px;margin-top:28px;border-radius:0 2px 2px 0;
  font-size:.78rem;color:var(--muted);line-height:1.9;font-family:'DM Mono',monospace}
.code-note strong{color:var(--ink3)}
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">Credit<span>Score</span> Engine <span style="font-size:.5rem;color:var(--border);font-family:'DM Mono',monospace;margin-left:10px">/ Build</span></div>
  <a href="index.php" class="nav-btn">← Home</a>
</div>

<div class="wrap">
  <div class="page-title"><?= $engine ? 'Edit Your Engine' : 'Build Your Credit Score Engine' ?></div>
  <div class="page-sub">
    Set your company name, assign weightages to each scoring factor, and define your thresholds.<br>
    Once saved, share the public URL — anyone who visits it submits their finances and your engine scores them.
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?= $msg_type ?>"><?= h($msg) ?></div>
  <?php endif; ?>

  <form method="POST">

    <!-- ── COMPANY IDENTITY ─────────────────────────────────── -->
    <div class="card">
      <div class="card-title">Company Identity</div>
      <div class="two-col">
        <div class="fg">
          <label>Credit Company Name *</label>
          <input type="text" name="company_name" required maxlength="80"
            placeholder="e.g. SmartRisk AI, TrustMetric"
            value="<?= h($engine['company_name'] ?? '') ?>">
        </div>
        <div class="fg">
          <label>Tagline</label>
          <input type="text" name="tagline" maxlength="160"
            placeholder="e.g. Credit Intelligence You Can Trust"
            value="<?= h($engine['tagline'] ?? '') ?>">
        </div>
      </div>
    </div>

    <!-- ── WEIGHTAGES ────────────────────────────────────────── -->
    <div class="card">
      <div class="card-title">Scoring Factor Weightages &nbsp;<span style="font-weight:400;opacity:.5">must total 100</span></div>

      <?php
      $factors = [
        ['w_income',     '💰 Income Stability',
         'Net income ÷ gross income. How much of your income is left after expenses.',
         $engine['w_income'] ?? 20],
        ['w_dscr',       '⚖️ DSCR',
         'Debt Service Coverage Ratio: Net Income ÷ (Existing EMIs + New EMI). The core metric.',
         $engine['w_dscr'] ?? 30],
        ['w_debt',       '💳 Existing Debt Load',
         'Existing EMIs as % of income. Lower burden = higher score.',
         $engine['w_debt'] ?? 20],
        ['w_employment', '🏢 Employment Stability',
         'Years of continuous employment. More years = more stable income.',
         $engine['w_employment'] ?? 15],
        ['w_repayment',  '📈 Repayment Track Record',
         'Repayments completed ÷ loans taken. Higher ratio = better credit discipline.',
         $engine['w_repayment'] ?? 15],
      ];
      foreach($factors as [$key, $label, $desc, $val]): ?>
      <div class="w-row">
        <div>
          <div class="w-label"><?= $label ?></div>
          <div class="w-desc"><?= $desc ?></div>
        </div>
        <input type="range" class="w-slider" min="0" max="100" value="<?= $val ?>"
          oninput="syncW('<?= $key ?>',this.value)" id="sl-<?= $key ?>">
        <input type="number" class="w-num" name="<?= $key ?>" min="0" max="100"
          value="<?= $val ?>" id="n-<?= $key ?>"
          oninput="syncW('<?= $key ?>',this.value)">
      </div>
      <?php endforeach; ?>

      <div class="total-box">
        <div>
          <div style="font-family:'DM Mono',monospace;font-size:.58rem;color:var(--muted);letter-spacing:.12em;text-transform:uppercase;margin-bottom:4px">Total Weightage</div>
          <div id="w-msg" style="font-size:.78rem;color:var(--muted)">Adjust until total = 100</div>
        </div>
        <div style="text-align:right">
          <div class="total-num" id="w-total">100</div>
          <div style="font-family:'DM Mono',monospace;font-size:.58rem;color:var(--muted)">/ 100</div>
        </div>
      </div>
    </div>

    <!-- ── THRESHOLDS ────────────────────────────────────────── -->
    <div class="card">
      <div class="card-title">Decision Thresholds</div>
      <div style="font-size:.82rem;color:var(--muted);margin-bottom:14px;line-height:1.7;font-weight:300">
        Score ≥ Approve → <strong style="color:var(--green)">Approve</strong> &nbsp;|&nbsp;
        Score ≥ Hold → <strong style="color:var(--amber)">Hold</strong> &nbsp;|&nbsp;
        Below Hold → <strong style="color:var(--red)">Reject</strong>
      </div>
      <div class="thresh-row">
        <div class="thresh-card">
          <div class="thresh-label">✅ Approve if score ≥</div>
          <input type="number" class="thresh-input" name="threshold_approve" id="t-approve" min="1" max="99"
            value="<?= $engine['threshold_approve'] ?? 70 ?>" oninput="checkThresh()">
          <div class="thresh-note" style="color:var(--green)">Strong creditworthiness</div>
        </div>
        <div class="thresh-card">
          <div class="thresh-label">⏸ Hold if score ≥</div>
          <input type="number" class="thresh-input" name="threshold_hold" id="t-hold" min="1" max="99"
            value="<?= $engine['threshold_hold'] ?? 50 ?>" oninput="checkThresh()">
          <div class="thresh-note" style="color:var(--amber)">Needs further review</div>
        </div>
      </div>
      <div id="thresh-warn" style="display:none;margin-top:10px;font-size:.8rem;color:var(--red)">
        ⚠️ Hold threshold must be lower than Approve threshold.
      </div>
    </div>

    <button type="submit" class="btn btn-primary" id="save-btn">
      <?= $engine ? '💾 Save Changes' : '🚀 Publish Engine' ?>
    </button>
  </form>

  <!-- ── PUBLISHED URL ─────────────────────────────────────── -->
  <?php if($engine): ?>
  <div class="pub-box">
    <div class="pub-title">🟢 Your Engine is Live</div>
    <div class="pub-url"><?= htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $public_url) ?></div>
    <div style="font-size:.82rem;color:var(--muted);line-height:1.7;font-weight:300">
      Share this URL on LinkedIn or Instagram.<br>
      Anyone who visits it can submit their financial profile and your engine will score them instantly.
    </div>
    <a href="<?= $public_url ?>" target="_blank"
      style="display:inline-flex;align-items:center;gap:6px;margin-top:14px;
      background:var(--ink);color:#fff;
      font-family:'DM Sans',sans-serif;font-weight:600;font-size:.84rem;
      padding:10px 22px;border-radius:2px;text-decoration:none">
      🔗 Open Public Page →
    </a>
  </div>
  <?php endif; ?>

  <!-- ── CODE EXPLANATION ──────────────────────────────────── -->
  <div class="code-note">
    <strong>📌 How this works (for developers):</strong><br>
    1. Student fills this form → POST to build.php → PHP validates weightages sum = 100<br>
    2. Engine config saved to <strong>$_SESSION['engine']</strong> (in production: MySQL database)<br>
    3. A public URL is generated: company name is lowercased FIRST, then non-alphanumeric chars stripped (slug fix)<br>
    4. Public visits <strong>apply.php?slug=xxx</strong> → fills financial form → PHP calculates score<br>
    5. Score = Σ (factor_raw_score × weight / 100) — all in PHP backend, no JavaScript scoring<br>
    6. Decision made by comparing score to thresholds → result shown to applicant
  </div>
</div>

<script>
const W_KEYS = ['w_income','w_dscr','w_debt','w_employment','w_repayment'];

function syncW(key, val) {
  val = Math.max(0, Math.min(100, parseInt(val) || 0));
  document.getElementById('sl-'+key).value = val;
  document.getElementById('n-'+key).value  = val;
  updateTotal();
}

function updateTotal() {
  const total = W_KEYS.reduce((s,k) => s + (parseInt(document.getElementById('n-'+k)?.value) || 0), 0);
  const el  = document.getElementById('w-total');
  const msg = document.getElementById('w-msg');
  const btn = document.getElementById('save-btn');
  if (el) { el.textContent = total; el.className = 'total-num ' + (total === 100 ? 'ok' : 'warn'); }
  if (msg) msg.textContent = total === 100 ? '✅ Perfect — ready to publish.' : `Adjust sliders. Current: ${total}`;
  if (btn) btn.disabled = (total !== 100);
}

function checkThresh() {
  const ta = parseInt(document.getElementById('t-approve')?.value) || 70;
  const th = parseInt(document.getElementById('t-hold')?.value)    || 50;
  const w  = document.getElementById('thresh-warn');
  const b  = document.getElementById('save-btn');
  if (w) w.style.display = th >= ta ? 'block' : 'none';
  if (b) b.disabled = th >= ta;
}

updateTotal();
</script>
</body>
</html>
