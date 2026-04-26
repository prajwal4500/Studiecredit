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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Credit Score Engine Platform</title>
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
}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh}
body::before{
  content:'';position:fixed;inset:0;
  background-image:linear-gradient(var(--border) 1px,transparent 1px),
    linear-gradient(90deg,var(--border) 1px,transparent 1px);
  background-size:48px 48px;opacity:.3;pointer-events:none;z-index:0
}

nav{display:flex;justify-content:space-between;align-items:center;padding:20px 56px;
  background:rgba(255,255,255,.96);border-bottom:1px solid var(--border);
  position:sticky;top:0;z-index:10;backdrop-filter:blur(8px)}
.logo{font-family:'Playfair Display',serif;font-weight:900;font-size:1.25rem;color:var(--ink);letter-spacing:-.02em}
.logo span{color:var(--muted)}
.logo-sub{font-family:'DM Mono',monospace;font-size:.48rem;color:var(--muted);letter-spacing:.22em;text-transform:uppercase;display:block;margin-top:3px}
.nav-tag{font-family:'DM Mono',monospace;font-size:.6rem;letter-spacing:.15em;text-transform:uppercase;
  color:var(--muted);border:1px solid var(--border);padding:5px 12px;border-radius:2px}

.hero{text-align:center;padding:110px 24px 80px;max-width:800px;margin:0 auto;position:relative;z-index:1}
.eyebrow{font-family:'DM Mono',monospace;font-size:.6rem;letter-spacing:.28em;text-transform:uppercase;
  color:var(--muted);display:inline-flex;align-items:center;gap:10px;margin-bottom:32px}
.eyebrow::before,.eyebrow::after{content:'';display:block;width:32px;height:1px;background:var(--border)}
.hero h1{font-family:'Playfair Display',serif;font-weight:900;font-size:clamp(2.6rem,7vw,4.8rem);
  line-height:1.02;margin-bottom:24px;color:var(--ink);letter-spacing:-.03em}
.hero h1 em{font-style:italic;color:var(--muted)}
.hero-sub{font-size:.95rem;color:var(--ink3);line-height:1.9;max-width:520px;margin:0 auto 48px;font-weight:300}

.btn-primary{display:inline-flex;align-items:center;gap:10px;
  background:var(--ink);color:#fff;
  font-family:'DM Sans',sans-serif;font-weight:600;font-size:.88rem;
  padding:15px 36px;border-radius:2px;text-decoration:none;letter-spacing:.03em;
  transition:opacity .2s,transform .15s}
.btn-primary:hover{opacity:.85;transform:translateY(-1px)}

.divider{display:flex;align-items:center;gap:16px;max-width:900px;margin:40px auto 32px;padding:0 24px;position:relative;z-index:1}
.divider-line{flex:1;height:1px;background:var(--border)}
.divider-text{font-family:'DM Mono',monospace;font-size:.55rem;letter-spacing:.22em;text-transform:uppercase;color:var(--muted)}

.features{display:grid;grid-template-columns:repeat(3,1fr);
  max-width:900px;margin:0 auto 60px;border:1px solid var(--border);
  border-radius:2px;position:relative;z-index:1}
@media(max-width:700px){.features{grid-template-columns:1fr}}
.feat{padding:32px 28px;border-right:1px solid var(--border);background:var(--bg)}
.feat:last-child{border-right:none}
@media(max-width:700px){.feat{border-right:none;border-bottom:1px solid var(--border)}}
.feat-num{font-family:'DM Mono',monospace;font-size:.56rem;color:var(--muted);letter-spacing:.15em;margin-bottom:18px;display:block}
.feat-icon{font-size:1.4rem;margin-bottom:12px;display:block}
.feat-title{font-family:'Playfair Display',serif;font-weight:700;font-size:1rem;color:var(--ink);margin-bottom:10px}
.feat-desc{font-size:.79rem;color:var(--muted);line-height:1.85;font-weight:300}

.cta-strip{background:var(--ink);margin:0 24px 60px;border-radius:2px;padding:48px 52px;
  display:flex;justify-content:space-between;align-items:center;gap:24px;
  max-width:852px;margin-left:auto;margin-right:auto;position:relative;z-index:1}
@media(max-width:640px){.cta-strip{flex-direction:column;text-align:center;padding:36px 28px}}
.cta-text h2{font-family:'Playfair Display',serif;font-size:1.6rem;color:#fff;margin-bottom:8px}
.cta-text p{font-size:.82rem;color:rgba(255,255,255,.4);line-height:1.75;font-weight:300}
.btn-inv{display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--ink);
  font-family:'DM Sans',sans-serif;font-weight:600;font-size:.86rem;
  padding:14px 32px;border-radius:2px;text-decoration:none;white-space:nowrap;transition:opacity .2s}
.btn-inv:hover{opacity:.88}

footer{text-align:center;padding:28px;font-family:'DM Mono',monospace;
  font-size:.52rem;color:var(--border);letter-spacing:.2em;text-transform:uppercase;
  border-top:1px solid var(--border);position:relative;z-index:1}
</style>
</head>
<body>

<nav>
  <div>
    <div class="logo">Credit<span>Score</span> Engine</div>
    <div class="logo-sub">Student FinTech Project</div>
  </div>
  <div class="nav-tag">FinTech Lab</div>
</nav>

<div class="hero">
  <div class="eyebrow">Credit Scoring · FinTech Lab</div>
  <h1>Build. Test.<br><em>Own Your Model.</em></h1>
  <p class="hero-sub">
    Design your own credit scoring engine. Set the rules, assign weightages,
    let the public apply — your PHP backend scores them in real time.
  </p>
  <a href="build.php" class="btn-primary">Build My Credit Engine &nbsp;→</a>
</div>

<div class="divider">
  <div class="divider-line"></div>
  <div class="divider-text">How It Works</div>
  <div class="divider-line"></div>
</div>

<div class="features">
  <div class="feat">
    <span class="feat-num">01</span>
    <span class="feat-icon">⚖️</span>
    <div class="feat-title">You Set the Weightages</div>
    <div class="feat-desc">
      Assign importance to Income, DSCR, Debt Load, Employment and Repayment history.
      Your weights define your model. Total must equal 100.
    </div>
  </div>
  <div class="feat">
    <span class="feat-num">02</span>
    <span class="feat-icon">🧮</span>
    <div class="feat-title">PHP Scores in Backend</div>
    <div class="feat-desc">
      No JavaScript scoring. The engine runs in PHP — applicants submit a form,
      the server calculates DSCR and applies your weightages, then returns the verdict.
    </div>
  </div>
  <div class="feat">
    <span class="feat-num">03</span>
    <span class="feat-icon">📊</span>
    <div class="feat-title">Real Financial Inputs</div>
    <div class="feat-desc">
      Income, expenses, existing EMIs, loan requested, employment years,
      loans taken and repayments made. Real data → real score → real decision.
    </div>
  </div>
</div>

<div class="cta-strip">
  <div class="cta-text">
    <h2>Ready to build your engine?</h2>
    <p>Set your scoring rules in under 2 minutes and share the link with anyone.</p>
  </div>
  <a href="build.php" class="btn-inv">Get Started →</a>
</div>

<footer>
  Credit Score Engine &nbsp;·&nbsp; Student FinTech Project &nbsp;·&nbsp; Built with PHP + MySQL
</footer>

</body>
</html>

<?php
/*
 * ============================================================
 *  apply.php — Public Credit Application Form
 *  
 *  STUDENT REFERENCE CODE
 *  This is a simplified standalone version — no database needed.
 *  Engine config is read from $_SESSION (set in build.php).
 *  
 *  In a production system (like myfintechs.com) you would:
 *  1. Look up the engine config from MySQL using the slug in the URL
 *  2. Save each application result to the database
 *  3. Show the student a live dashboard of all applicants
 *  
 *  KEY CONCEPT — The Scoring Engine:
 *  This file contains the complete PHP credit scoring algorithm.
 *  Each financial factor is converted to a raw score (0-100),
 *  then multiplied by the student's weightage, then summed.
 *  The final score is compared to thresholds → decision made.
 *  ALL scoring happens in PHP on the server. No JavaScript math.
 * ============================================================
 */

session_start();

// ── LOAD ENGINE CONFIG ────────────────────────────────────────────
$engine = $_SESSION['engine'] ?? null;

if (!$engine) {
    header('Location: build.php');
    exit;
}

function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fmt($n) { return '€' . number_format((float)$n, 0, '.', ','); }


// ════════════════════════════════════════════════════════════════
//  THE SCORING ENGINE — Core PHP Algorithm
// ════════════════════════════════════════════════════════════════
function score_applicant(array $engine, array $input): array {

    $income      = max(0, (float)$input['monthly_income']);
    $expenses    = max(0, (float)$input['monthly_expenses']);
    $emis        = max(0, (float)$input['existing_emis']);
    $loan        = max(0, (float)$input['loan_requested']);
    $emp_years   = max(0, (float)$input['employment_years']);
    $loans_taken = max(0, (int)$input['loans_taken']);
    $repaid      = max(0, (int)$input['repayments_made']);

    $net_income = $income - $expenses;
    $new_emi = $loan > 0 ? ($loan * 1.10) / 60 : 0;
    $total_debt_service = $emis + $new_emi;

    if ($total_debt_service > 0) {
        $dscr = $net_income / $total_debt_service;
    } else {
        $dscr = $net_income > 0 ? 5.0 : 0.0;
    }

    $income_ratio = $income > 0 ? $net_income / $income : 0;
    $s_income = max(0, min(100, (int)round($income_ratio * 100)));

    if      ($dscr >= 2.5) $s_dscr = 100;
    elseif  ($dscr >= 2.0) $s_dscr = 90;
    elseif  ($dscr >= 1.5) $s_dscr = 78;
    elseif  ($dscr >= 1.2) $s_dscr = 62;
    elseif  ($dscr >= 1.0) $s_dscr = 42;
    elseif  ($dscr >= 0.7) $s_dscr = 22;
    else                   $s_dscr = max(0, (int)round($dscr * 25));

    $debt_ratio = $income > 0 ? $emis / $income : 1;
    $s_debt = max(0, min(100, (int)round((1 - $debt_ratio) * 100)));

    if      ($emp_years >= 10) $s_employment = 100;
    elseif  ($emp_years >= 5)  $s_employment = (int)round(70 + ($emp_years - 5) * 6);
    elseif  ($emp_years >= 2)  $s_employment = (int)round(35 + ($emp_years - 2) * 11.7);
    elseif  ($emp_years >= 1)  $s_employment = 20;
    else                       $s_employment = 8;

    if ($loans_taken === 0) {
        $s_repayment = 55;
    } else {
        $ratio = min(1.0, $repaid / $loans_taken);
        $s_repayment = (int)round($ratio * 100);
    }

    $score = (int)round(
        $s_income     * $engine['w_income']     / 100 +
        $s_dscr       * $engine['w_dscr']       / 100 +
        $s_debt       * $engine['w_debt']       / 100 +
        $s_employment * $engine['w_employment'] / 100 +
        $s_repayment  * $engine['w_repayment']  / 100
    );
    $score = max(0, min(100, $score));

    if      ($score >= $engine['threshold_approve']) $decision = 'Approve';
    elseif  ($score >= $engine['threshold_hold'])    $decision = 'Hold';
    else                                              $decision = 'Reject';

    return [
        'score'            => $score,
        'decision'         => $decision,
        'dscr'             => round($dscr, 3),
        'new_emi'          => round($new_emi, 2),
        'net_income'       => $net_income,
        'score_income'     => $s_income,
        'score_dscr'       => $s_dscr,
        'score_debt'       => $s_debt,
        'score_employment' => $s_employment,
        'score_repayment'  => $s_repayment,
    ];
}


// ── PROCESS FORM SUBMISSION ───────────────────────────────────────
$result    = null;
$applicant = null;
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $applicant = [
        'name'             => trim($_POST['applicant_name']  ?? ''),
        'monthly_income'   => (float)($_POST['monthly_income']   ?? 0),
        'monthly_expenses' => (float)($_POST['monthly_expenses'] ?? 0),
        'existing_emis'    => (float)($_POST['existing_emis']    ?? 0),
        'loan_requested'   => (float)($_POST['loan_requested']   ?? 0),
        'employment_years' => (float)($_POST['employment_years'] ?? 0),
        'loans_taken'      => (int)  ($_POST['loans_taken']      ?? 0),
        'repayments_made'  => (int)  ($_POST['repayments_made']  ?? 0),
    ];

    if (strlen($applicant['name']) < 2)
        $errors[] = "Please enter your name.";
    if ($applicant['monthly_income'] <= 0)
        $errors[] = "Monthly income must be greater than zero.";
    if ($applicant['monthly_expenses'] < 0)
        $errors[] = "Monthly expenses cannot be negative.";
    if ($applicant['loan_requested'] <= 0)
        $errors[] = "Please enter the loan amount you are requesting.";
    if ($applicant['repayments_made'] > $applicant['loans_taken'])
        $errors[] = "Repayments made cannot exceed total loans taken.";

    if (empty($errors)) {
        $result = score_applicant($engine, $applicant);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($engine['company_name']) ?> · Credit Score Check</title>
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
  --green-bg:rgba(22,163,74,.07);
  --green-border:rgba(22,163,74,.25);
  --red-bg:rgba(220,38,38,.07);
  --red-border:rgba(220,38,38,.25);
  --amber-bg:rgba(217,119,6,.07);
  --amber-border:rgba(217,119,6,.25);
}
body{font-family:'DM Sans',sans-serif;background:var(--bg2);color:var(--ink);min-height:100vh}
body::before{
  content:'';position:fixed;inset:0;
  background-image:linear-gradient(var(--border) 1px,transparent 1px),
    linear-gradient(90deg,var(--border) 1px,transparent 1px);
  background-size:48px 48px;opacity:.25;pointer-events:none;z-index:0
}

/* COMPANY HEADER */
.header{padding:44px 24px 0;max-width:700px;margin:0 auto;text-align:center;position:relative;z-index:1}
.co-badge{font-family:'DM Mono',monospace;font-size:.56rem;letter-spacing:.22em;text-transform:uppercase;
  color:var(--muted);border:1px solid var(--border);
  display:inline-block;padding:4px 16px;border-radius:2px;margin-bottom:18px}
.co-name{font-family:'Playfair Display',serif;font-weight:900;font-size:clamp(2rem,6vw,3rem);
  color:var(--ink);line-height:1.05;margin-bottom:8px;letter-spacing:-.03em}
.co-name em{font-style:italic;color:var(--muted)}
.co-tagline{font-size:.9rem;color:var(--muted);margin-bottom:6px;font-weight:300}
.co-credit{font-family:'DM Mono',monospace;font-size:.52rem;color:var(--border);
  letter-spacing:.15em;text-transform:uppercase;margin-bottom:36px}

/* FORM */
.wrap{max-width:660px;margin:0 auto;padding:0 24px 80px;position:relative;z-index:1}
.form-card{background:#fff;border:1px solid var(--border);border-radius:2px;padding:26px;margin-bottom:14px}
.section-title{font-family:'DM Mono',monospace;font-size:.6rem;font-weight:500;color:var(--muted);
  letter-spacing:.15em;text-transform:uppercase;
  margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)}
.fg{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.fg label{font-family:'DM Mono',monospace;font-size:.58rem;color:var(--muted);letter-spacing:.1em;text-transform:uppercase}
.fg input{background:#fff;border:1.5px solid var(--border);
  border-radius:2px;color:var(--ink);padding:12px 14px;font-size:.9rem;outline:none;transition:.2s;
  font-family:'DM Sans',sans-serif}
.fg input:focus{border-color:var(--ink);box-shadow:0 0 0 3px rgba(10,10,10,.06)}
.fg .note{font-size:.7rem;color:var(--muted);line-height:1.5;margin-top:2px;font-weight:300}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:520px){.two-col{grid-template-columns:1fr}}

.submit-btn{width:100%;padding:15px;
  background:var(--ink);color:#fff;
  border:none;border-radius:2px;font-family:'DM Sans',sans-serif;
  font-weight:600;font-size:.9rem;cursor:pointer;letter-spacing:.02em;transition:.15s}
.submit-btn:hover{background:var(--ink2)}

.errors{background:var(--red-bg);border:1px solid var(--red-border);
  border-radius:2px;padding:12px 16px;margin-bottom:16px;
  font-size:.82rem;color:var(--red);line-height:1.8}

/* RESULT */
.result-wrap{max-width:660px;margin:0 auto;padding:0 24px 80px;position:relative;z-index:1}

.score-circle{width:148px;height:148px;border-radius:50%;
  margin:0 auto 22px;display:flex;flex-direction:column;
  align-items:center;justify-content:center;border:3px solid}
.sc-a{background:var(--green-bg);border-color:var(--green)}
.sc-h{background:var(--amber-bg);border-color:var(--amber)}
.sc-r{background:var(--red-bg);border-color:var(--red)}
.score-num{font-family:'Playfair Display',serif;font-weight:900;font-size:3rem;line-height:1}
.score-sub{font-family:'DM Mono',monospace;font-size:.52rem;color:var(--muted);margin-top:4px;letter-spacing:.1em}

.decision{font-family:'Playfair Display',serif;font-weight:900;font-size:1.5rem;text-align:center;margin-bottom:8px}
.d-a{color:var(--green)}.d-h{color:var(--amber)}.d-r{color:var(--red)}
.decision-desc{font-size:.84rem;color:var(--muted);text-align:center;
  max-width:420px;margin:0 auto 28px;line-height:1.8;font-weight:300}

.breakdown{background:#fff;border:1px solid var(--border);border-radius:2px;padding:22px;margin-bottom:14px}
.bd-title{font-family:'DM Mono',monospace;font-size:.6rem;font-weight:500;color:var(--muted);
  letter-spacing:.15em;text-transform:uppercase;margin-bottom:16px}

.factor-row{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.f-label{font-size:.76rem;color:var(--ink3);width:165px;flex-shrink:0;line-height:1.4;font-weight:500}
.f-label-note{font-size:.62rem;color:var(--muted);margin-top:1px;font-weight:300}
.f-bar{flex:1;height:4px;background:var(--border);border-radius:99px;overflow:hidden}
.f-fill{height:100%;border-radius:99px;background:var(--ink)}
.f-score{font-family:'DM Mono',monospace;font-size:.7rem;color:var(--ink3);width:34px;text-align:right}
.f-pts{font-family:'DM Mono',monospace;font-size:.65rem;color:var(--muted);width:44px;text-align:right}

.summary{background:var(--bg2);border-radius:2px;padding:16px;margin:12px 0;border:1px solid var(--border)}
.s-row{display:flex;justify-content:space-between;padding:6px 0;
  border-bottom:1px solid var(--border);font-size:.8rem}
.s-row:last-child{border:none}
.s-key{color:var(--muted);font-weight:300}
.s-val{font-family:'DM Mono',monospace;color:var(--ink);font-size:.76rem}

.disclaimer{background:var(--bg2);border:1px solid var(--border);
  border-radius:2px;padding:14px 18px;font-size:.74rem;color:var(--muted);
  line-height:1.85;text-align:center;margin-bottom:14px;font-weight:300}

.try-again{width:100%;padding:13px;background:transparent;color:var(--ink);
  border:1.5px solid var(--border);border-radius:2px;
  font-family:'DM Sans',sans-serif;font-weight:600;font-size:.86rem;cursor:pointer;transition:.15s}
.try-again:hover{border-color:var(--ink)}

.footer{text-align:center;padding:20px;font-family:'DM Mono',monospace;
  font-size:.5rem;color:var(--border);letter-spacing:.18em;text-transform:uppercase;position:relative;z-index:1}
</style>
</head>
<body>

<!-- COMPANY HEADER -->
<div class="header">
  <div class="co-badge">Credit Score Simulator</div>
  <div class="co-name"><?= h($engine['company_name']) ?> <em>AI</em></div>
  <?php if($engine['tagline']): ?>
  <div class="co-tagline"><?= h($engine['tagline']) ?></div>
  <?php endif; ?>
  <div class="co-credit">A student-built credit scoring engine · myfintechs.com</div>
</div>

<?php if($result !== null): ?>
<!-- ════════════════════════════════════
     RESULT SCREEN
════════════════════════════════════ -->
<?php
$dec = $result['decision'];
$sc_class = ['Approve'=>'sc-a','Hold'=>'sc-h','Reject'=>'sc-r'][$dec];
$dc_class = ['Approve'=>'d-a','Hold'=>'d-h','Reject'=>'d-r'][$dec];
$dec_color = ['Approve'=>'var(--green)','Hold'=>'var(--amber)','Reject'=>'var(--red)'][$dec];
$dec_texts = [
    'Approve' => ['✅ Approved', 'Your financial profile meets this engine\'s creditworthiness criteria. Your repayment capacity is sufficient for the requested loan.'],
    'Hold'    => ['⏸ Under Review', 'Your profile shows mixed signals. A real lender would request additional documentation before proceeding.'],
    'Reject'  => ['❌ Not Approved', 'Your repayment capacity or credit profile does not meet this engine\'s thresholds at this time.'],
];
?>
<div class="result-wrap">
  <div style="text-align:center;padding:36px 0 24px">
    <div style="font-family:'DM Mono',monospace;font-size:.6rem;color:var(--muted);
      letter-spacing:.15em;text-transform:uppercase;margin-bottom:16px">
      Result for <?= h($applicant['name']) ?>
    </div>

    <div class="score-circle <?= $sc_class ?>">
      <div class="score-num" style="color:<?= $dec_color ?>"><?= $result['score'] ?></div>
      <div class="score-sub">out of 100</div>
    </div>

    <div class="decision <?= $dc_class ?>"><?= $dec_texts[$dec][0] ?></div>
    <div class="decision-desc"><?= $dec_texts[$dec][1] ?></div>
  </div>

  <!-- FACTOR BREAKDOWN -->
  <div class="breakdown">
    <div class="bd-title">How Your Score Was Calculated</div>

    <?php
    $factors = [
        ['💰 Income Stability',      $result['score_income'],     $engine['w_income'],     'Net income ÷ gross income'],
        ['⚖️ DSCR',                  $result['score_dscr'],       $engine['w_dscr'],       'DSCR = '.number_format($result['dscr'],2)],
        ['💳 Existing Debt Load',    $result['score_debt'],       $engine['w_debt'],       'EMI burden vs income'],
        ['🏢 Employment Stability',  $result['score_employment'], $engine['w_employment'], $applicant['employment_years'].' years employed'],
        ['📈 Repayment Track Record',$result['score_repayment'],  $engine['w_repayment'],  $applicant['repayments_made'].' of '.$applicant['loans_taken'].' loans repaid'],
    ];
    foreach($factors as [$label, $raw, $weight, $note]):
        $contribution = round($raw * $weight / 100);
    ?>
    <div class="factor-row">
      <div class="f-label">
        <?= $label ?>
        <div class="f-label-note"><?= $note ?></div>
      </div>
      <div class="f-bar"><div class="f-fill" style="width:<?= $raw ?>%"></div></div>
      <div class="f-score"><?= $raw ?></div>
      <div class="f-pts">+<?= $contribution ?>pt</div>
    </div>
    <?php endforeach; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;
      margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
      <span style="font-family:'DM Mono',monospace;font-size:.58rem;color:var(--muted);
        letter-spacing:.1em;text-transform:uppercase">Final Score</span>
      <span style="font-family:'Playfair Display',serif;font-weight:900;font-size:1.8rem;color:<?= $dec_color ?>">
        <?= $result['score'] ?> / 100
      </span>
    </div>
    <div style="font-family:'DM Mono',monospace;font-size:.62rem;color:var(--muted);margin-top:6px">
      Approve ≥ <?= $engine['threshold_approve'] ?> &nbsp;|&nbsp;
      Hold ≥ <?= $engine['threshold_hold'] ?> &nbsp;|&nbsp;
      Reject &lt; <?= $engine['threshold_hold'] ?>
    </div>
  </div>

  <!-- SUBMITTED DATA SUMMARY -->
  <div class="breakdown">
    <div class="bd-title">Your Submitted Figures</div>
    <div class="summary">
      <?php
      $rows = [
        ['Monthly Income',            fmt($applicant['monthly_income'])],
        ['Monthly Expenses',          fmt($applicant['monthly_expenses'])],
        ['Existing Loan EMIs',        fmt($applicant['existing_emis']).' / mo'],
        ['Loan Amount Requested',     fmt($applicant['loan_requested'])],
        ['Estimated New EMI',         fmt($result['new_emi']).' / mo'],
        ['Net Income After Expenses', fmt($result['net_income']).' / mo'],
        ['Calculated DSCR',           number_format($result['dscr'],2)],
        ['Employment',                $applicant['employment_years'].' years'],
        ['Loans Taken / Repaid',      $applicant['loans_taken'].' / '.$applicant['repayments_made']],
      ];
      foreach($rows as [$k,$v]): ?>
      <div class="s-row"><span class="s-key"><?= $k ?></span><span class="s-val"><?= $v ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="disclaimer">
    ⚠️ <strong style="color:var(--ink3)">Simulation Disclaimer</strong><br>
    This credit assessment is produced by a student-built engine created as part of a FinTech education programme.
    It is a <strong style="color:var(--ink3)">pure simulator</strong> and does not constitute real financial advice,
    a real credit check, or a lending decision. No personal data is retained after you leave this page.
  </div>

  <button class="try-again" onclick="location.href=location.href">← Try Another Scenario</button>
</div>

<?php else: ?>
<!-- ════════════════════════════════════
     APPLICATION FORM
════════════════════════════════════ -->
<div class="wrap">
  <?php if(!empty($errors)): ?>
  <div class="errors">
    <?php foreach($errors as $e): ?>⚠️ <?= h($e) ?><br><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="POST">

    <div class="form-card">
      <div class="section-title">About You</div>
      <div class="fg">
        <label>Your Name *</label>
        <input type="text" name="applicant_name" required maxlength="100"
          placeholder="Enter your full name"
          value="<?= h($applicant['name'] ?? '') ?>">
      </div>
    </div>

    <div class="form-card">
      <div class="section-title">Monthly Finances</div>
      <div class="two-col">
        <div class="fg">
          <label>Monthly Income (€) *</label>
          <input type="number" name="monthly_income" required min="0" step="100"
            placeholder="e.g. 3500" value="<?= h($applicant['monthly_income'] ?? '') ?>">
          <div class="note">Total gross monthly income</div>
        </div>
        <div class="fg">
          <label>Monthly Expenses (€) *</label>
          <input type="number" name="monthly_expenses" required min="0" step="100"
            placeholder="e.g. 1800" value="<?= h($applicant['monthly_expenses'] ?? '') ?>">
          <div class="note">Rent, food, utilities, transport</div>
        </div>
      </div>
      <div class="fg">
        <label>Existing Monthly Loan EMIs (€)</label>
        <input type="number" name="existing_emis" min="0" step="50"
          placeholder="0 if none" value="<?= h($applicant['existing_emis'] ?? '0') ?>">
        <div class="note">Total of all current loan repayments per month</div>
      </div>
    </div>

    <div class="form-card">
      <div class="section-title">Loan Request</div>
      <div class="fg">
        <label>Loan Amount Requested (€) *</label>
        <input type="number" name="loan_requested" required min="0" step="1000"
          placeholder="e.g. 25000" value="<?= h($applicant['loan_requested'] ?? '') ?>">
        <div class="note">Engine will estimate a 60-month repayment EMI at 10% interest</div>
      </div>
    </div>

    <div class="form-card">
      <div class="section-title">Employment & Credit History</div>
      <div class="fg">
        <label>Years in Employment *</label>
        <input type="number" name="employment_years" required min="0" step="0.5"
          placeholder="e.g. 4.5" value="<?= h($applicant['employment_years'] ?? '') ?>">
        <div class="note">Total continuous years in current or recent job</div>
      </div>
      <div class="two-col">
        <div class="fg">
          <label>Total Loans Ever Taken</label>
          <input type="number" name="loans_taken" min="0" step="1"
            placeholder="e.g. 2" value="<?= h($applicant['loans_taken'] ?? '0') ?>">
          <div class="note">All loans including credit cards, mortgages</div>
        </div>
        <div class="fg">
          <label>Loans Fully Repaid</label>
          <input type="number" name="repayments_made" min="0" step="1"
            placeholder="e.g. 2" value="<?= h($applicant['repayments_made'] ?? '0') ?>">
          <div class="note">How many of those loans are fully closed</div>
        </div>
      </div>
    </div>

    <div class="disclaimer">
      ⚠️ This is a student simulation. No data is stored. Not a real credit check.
    </div>

    <button type="submit" class="submit-btn">
      Check My Score with <?= h($engine['company_name']) ?> →
    </button>
  </form>
</div>
<?php endif; ?>

<div class="footer">
  Powered by <?= strtoupper(h($engine['company_name'])) ?> &nbsp;·&nbsp; Student FinTech Simulator
</div>

</body>
</html>
