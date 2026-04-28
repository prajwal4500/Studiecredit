<?php
session_start();

$slug = trim($_GET['slug'] ?? '');

// Load engine from JSON file first (works for anyone)
$engine = null;
$jsonFile = 'engines/' . preg_replace('/[^a-z0-9\-]/', '', $slug) . '.json';

if ($slug && file_exists($jsonFile)) {
    $engine = json_decode(file_get_contents($jsonFile), true);
} elseif (!empty($_SESSION['engine'])) {
    $engine = $_SESSION['engine'];
} elseif (file_exists('engines/last.json')) {
    $engine = json_decode(file_get_contents('engines/last.json'), true);
}

if (!$engine) {
    header('Location: build.php');
    exit;
}

function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fmt($n) { return '€' . number_format((float)$n, 0, '.', ','); }

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
    $dscr = $total_debt_service > 0 ? $net_income / $total_debt_service : ($net_income > 0 ? 5.0 : 0.0);

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

    $s_repayment = $loans_taken === 0 ? 55 : (int)round(min(1.0, $repaid / $loans_taken) * 100);

    $score = max(0, min(100, (int)round(
        $s_income     * $engine['w_income']     / 100 +
        $s_dscr       * $engine['w_dscr']       / 100 +
        $s_debt       * $engine['w_debt']       / 100 +
        $s_employment * $engine['w_employment'] / 100 +
        $s_repayment  * $engine['w_repayment']  / 100
    )));

    $decision = $score >= $engine['threshold_approve'] ? 'Approve' : ($score >= $engine['threshold_hold'] ? 'Hold' : 'Reject');

    return ['score'=>$score,'decision'=>$decision,'dscr'=>round($dscr,3),'new_emi'=>round($new_emi,2),
            'net_income'=>$net_income,'score_income'=>$s_income,'score_dscr'=>$s_dscr,
            'score_debt'=>$s_debt,'score_employment'=>$s_employment,'score_repayment'=>$s_repayment];
}

$result = null; $applicant = null; $errors = [];

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
    if (strlen($applicant['name']) < 2)          $errors[] = "Please enter your name.";
    if ($applicant['monthly_income'] <= 0)        $errors[] = "Monthly income must be greater than zero.";
    if ($applicant['monthly_expenses'] < 0)       $errors[] = "Monthly expenses cannot be negative.";
    if ($applicant['loan_requested'] <= 0)        $errors[] = "Please enter the loan amount you are requesting.";
    if ($applicant['repayments_made'] > $applicant['loans_taken']) $errors[] = "Repayments made cannot exceed total loans taken.";
    if (empty($errors)) $result = score_applicant($engine, $applicant);
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
:root{--bg:#ffffff;--bg2:#f7f7f5;--ink:#0a0a0a;--ink2:#1a1a1a;--ink3:#444;--muted:#888;--border:#e0ddd8;--green:#16a34a;--red:#dc2626;--amber:#d97706;--green-bg:rgba(22,163,74,.07);--green-border:rgba(22,163,74,.25);--red-bg:rgba(220,38,38,.07);--red-border:rgba(220,38,38,.25);--amber-bg:rgba(217,119,6,.07);--amber-border:rgba(217,119,6,.25);}
body{font-family:'DM Sans',sans-serif;background:var(--bg2);color:var(--ink);min-height:100vh}
body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(var(--border) 1px,transparent 1px),linear-gradient(90deg,var(--border) 1px,transparent 1px);background-size:48px 48px;opacity:.25;pointer-events:none;z-index:0}
.header{padding:44px 24px 0;max-width:700px;margin:0 auto;text-align:center;position:relative;z-index:1}
.co-badge{font-family:'DM Mono',monospace;font-size:.56rem;letter-spacing:.22em;text-transform:uppercase;color:var(--ink);border:1px solid var(--ink);display:inline-block;padding:4px 16px;border-radius:2px;margin-bottom:18px;font-weight:500}
.co-name{font-family:'Playfair Display',serif;font-weight:900;font-size:clamp(2rem,6vw,3rem);color:var(--ink);line-height:1.05;margin-bottom:8px;letter-spacing:-.03em}
.co-name em{font-style:italic;color:var(--ink3)}
.co-tagline{font-size:.9rem;color:var(--ink);margin-bottom:6px;font-weight:500}
.co-credit{font-family:'DM Mono',monospace;font-size:.58rem;color:var(--ink);letter-spacing:.15em;text-transform:uppercase;margin-bottom:36px;font-weight:500}
.wrap{max-width:660px;margin:0 auto;padding:0 24px 80px;position:relative;z-index:1}
.form-card{background:#fff;border:1px solid var(--border);border-radius:2px;padding:26px;margin-bottom:14px}
.section-title{font-family:'DM Mono',monospace;font-size:.6rem;font-weight:500;color:var(--muted);letter-spacing:.15em;text-transform:uppercase;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)}
.fg{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.fg label{font-family:'DM Mono',monospace;font-size:.58rem;color:var(--muted);letter-spacing:.1em;text-transform:uppercase}
.fg input{background:#fff;border:1.5px solid var(--border);border-radius:2px;color:var(--ink);padding:12px 14px;font-size:.9rem;outline:none;transition:.2s;font-family:'DM Sans',sans-serif}
.fg input:focus{border-color:var(--ink);box-shadow:0 0 0 3px rgba(10,10,10,.06)}
.fg .note{font-size:.7rem;color:var(--muted);line-height:1.5;margin-top:2px;font-weight:300}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:520px){.two-col{grid-template-columns:1fr}}
.submit-btn{width:100%;padding:15px;background:var(--ink);color:#fff;border:none;border-radius:2px;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.9rem;cursor:pointer;transition:.15s}
.submit-btn:hover{background:var(--ink2)}
.errors{background:var(--red-bg);border:1px solid var(--red-border);border-radius:2px;padding:12px 16px;margin-bottom:16px;font-size:.82rem;color:var(--red);line-height:1.8}
.result-wrap{max-width:660px;margin:0 auto;padding:0 24px 80px;position:relative;z-index:1}
.score-circle{width:148px;height:148px;border-radius:50%;margin:0 auto 22px;display:flex;flex-direction:column;align-items:center;justify-content:center;border:3px solid}
.sc-a{background:var(--green-bg);border-color:var(--green)}
.sc-h{background:var(--amber-bg);border-color:var(--amber)}
.sc-r{background:var(--red-bg);border-color:var(--red)}
.score-num{font-family:'Playfair Display',serif;font-weight:900;font-size:3rem;line-height:1}
.score-sub{font-family:'DM Mono',monospace;font-size:.52rem;color:var(--muted);margin-top:4px;letter-spacing:.1em}
.decision{font-family:'Playfair Display',serif;font-weight:900;font-size:1.5rem;text-align:center;margin-bottom:8px}
.d-a{color:var(--green)}.d-h{color:var(--amber)}.d-r{color:var(--red)}
.decision-desc{font-size:.84rem;color:var(--muted);text-align:center;max-width:420px;margin:0 auto 28px;line-height:1.8;font-weight:300}
.breakdown{background:#fff;border:1px solid var(--border);border-radius:2px;padding:22px;margin-bottom:14px}
.bd-title{font-family:'DM Mono',monospace;font-size:.6rem;font-weight:500;color:var(--muted);letter-spacing:.15em;text-transform:uppercase;margin-bottom:16px}
.factor-row{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.f-label{font-size:.76rem;color:var(--ink3);width:165px;flex-shrink:0;line-height:1.4;font-weight:500}
.f-label-note{font-size:.62rem;color:var(--muted);margin-top:1px;font-weight:300}
.f-bar{flex:1;height:4px;background:var(--border);border-radius:99px;overflow:hidden}
.f-fill{height:100%;border-radius:99px;background:var(--ink)}
.f-score{font-family:'DM Mono',monospace;font-size:.7rem;color:var(--ink3);width:34px;text-align:right}
.f-pts{font-family:'DM Mono',monospace;font-size:.65rem;color:var(--muted);width:44px;text-align:right}
.summary{background:var(--bg2);border-radius:2px;padding:16px;margin:12px 0;border:1px solid var(--border)}
.s-row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:.8rem}
.s-row:last-child{border:none}
.s-key{color:var(--muted);font-weight:300}
.s-val{font-family:'DM Mono',monospace;color:var(--ink);font-size:.76rem}
.disclaimer{background:var(--bg2);border:1px solid var(--border);border-radius:2px;padding:14px 18px;font-size:.74rem;color:var(--muted);line-height:1.85;text-align:center;margin-bottom:14px;font-weight:300}
.try-again{width:100%;padding:13px;background:transparent;color:var(--ink);border:1.5px solid var(--border);border-radius:2px;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.86rem;cursor:pointer;transition:.15s}
.try-again:hover{border-color:var(--ink)}
.footer{text-align:center;padding:20px;font-family:'DM Mono',monospace;font-size:.5rem;color:var(--border);letter-spacing:.18em;text-transform:uppercase;position:relative;z-index:1}
</style>
</head>
<body>

<div class="header">
  <div class="co-badge">Credit Score Simulator</div>
  <div class="co-name"><?= h($engine['company_name']) ?> <em>AI</em></div>
  <?php if($engine['tagline']): ?>
  <div class="co-tagline"><?= h($engine['tagline']) ?></div>
  <?php endif; ?>
  <div class="co-credit">A student-built credit scoring engine · By Prajwal Manikantan</div>
</div>

<?php if($result !== null): ?>
<?php
$dec = $result['decision'];
$sc_class = ['Approve'=>'sc-a','Hold'=>'sc-h','Reject'=>'sc-r'][$dec];
$dc_class = ['Approve'=>'d-a','Hold'=>'d-h','Reject'=>'d-r'][$dec];
$dec_color = ['Approve'=>'var(--green)','Hold'=>'var(--amber)','Reject'=>'var(--red)'][$dec];
$dec_texts = [
    'Approve' => ['✅ Approved',      'Your financial profile meets this engine\'s creditworthiness criteria.'],
    'Hold'    => ['⏸ Under Review',  'Your profile shows mixed signals. Further documentation may be needed.'],
    'Reject'  => ['❌ Not Approved',  'Your profile does not meet this engine\'s thresholds at this time.'],
];
?>
<div class="result-wrap">
  <div style="text-align:center;padding:36px 0 24px">
    <div style="font-family:'DM Mono',monospace;font-size:.6rem;color:var(--ink);letter-spacing:.15em;text-transform:uppercase;margin-bottom:16px;font-weight:500">Result for <?= h($applicant['name']) ?></div>
    <div class="score-circle <?= $sc_class ?>">
      <div class="score-num" style="color:<?= $dec_color ?>"><?= $result['score'] ?></div>
      <div class="score-sub">out of 100</div>
    </div>
    <div class="decision <?= $dc_class ?>"><?= $dec_texts[$dec][0] ?></div>
    <div class="decision-desc"><?= $dec_texts[$dec][1] ?></div>
  </div>

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
      <div class="f-label"><?= $label ?><div class="f-label-note"><?= $note ?></div></div>
      <div class="f-bar"><div class="f-fill" style="width:<?= $raw ?>%"></div></div>
      <div class="f-score"><?= $raw ?></div>
      <div class="f-pts">+<?= $contribution ?>pt</div>
    </div>
    <?php endforeach; ?>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
      <span style="font-family:'DM Mono',monospace;font-size:.58rem;color:var(--muted);letter-spacing:.1em;text-transform:uppercase">Final Score</span>
      <span style="font-family:'Playfair Display',serif;font-weight:900;font-size:1.8rem;color:<?= $dec_color ?>"><?= $result['score'] ?> / 100</span>
    </div>
    <div style="font-family:'DM Mono',monospace;font-size:.62rem;color:var(--muted);margin-top:6px">
      Approve ≥ <?= $engine['threshold_approve'] ?> &nbsp;|&nbsp; Hold ≥ <?= $engine['threshold_hold'] ?> &nbsp;|&nbsp; Reject &lt; <?= $engine['threshold_hold'] ?>
    </div>
  </div>

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

  <div class="disclaimer">⚠️ <strong style="color:var(--ink3)">Simulation Disclaimer</strong><br>This is a student-built simulator. Not real financial advice. No data is retained.</div>
  <button class="try-again" onclick="location.href=location.href">← Try Another Scenario</button>
</div>

<?php else: ?>
<div class="wrap">
  <?php if(!empty($errors)): ?>
  <div class="errors"><?php foreach($errors as $e): ?>⚠️ <?= h($e) ?><br><?php endforeach; ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-card">
      <div class="section-title">About You</div>
      <div class="fg">
        <label>Your Name *</label>
        <input type="text" name="applicant_name" required maxlength="100" placeholder="Enter your full name" value="<?= h($applicant['name'] ?? '') ?>">
      </div>
    </div>

    <div class="form-card">
      <div class="section-title">Monthly Finances</div>
      <div class="two-col">
        <div class="fg">
          <label>Monthly Income (€) *</label>
          <input type="number" name="monthly_income" required min="0" step="100" placeholder="e.g. 3500" value="<?= h($applicant['monthly_income'] ?? '') ?>">
          <div class="note">Total gross monthly income</div>
        </div>
        <div class="fg">
          <label>Monthly Expenses (€) *</label>
          <input type="number" name="monthly_expenses" required min="0" step="100" placeholder="e.g. 1800" value="<?= h($applicant['monthly_expenses'] ?? '') ?>">
          <div class="note">Rent, food, utilities, transport</div>
        </div>
      </div>
      <div class="fg">
        <label>Existing Monthly Loan EMIs (€)</label>
        <input type="number" name="existing_emis" min="0" step="50" placeholder="0 if none" value="<?= h($applicant['existing_emis'] ?? '0') ?>">
        <div class="note">Total of all current loan repayments per month</div>
      </div>
    </div>

    <div class="form-card">
      <div class="section-title">Loan Request</div>
      <div class="fg">
        <label>Loan Amount Requested (€) *</label>
        <input type="number" name="loan_requested" required min="0" step="1000" placeholder="e.g. 25000" value="<?= h($applicant['loan_requested'] ?? '') ?>">
        <div class="note">Engine will estimate a 60-month repayment EMI at 10% interest</div>
      </div>
    </div>

    <div class="form-card">
      <div class="section-title">Employment & Credit History</div>
      <div class="fg">
        <label>Years in Employment *</label>
        <input type="number" name="employment_years" required min="0" step="0.5" placeholder="e.g. 4.5" value="<?= h($applicant['employment_years'] ?? '') ?>">
        <div class="note">Total continuous years in current or recent job</div>
      </div>
      <div class="two-col">
        <div class="fg">
          <label>Total Loans Ever Taken</label>
          <input type="number" name="loans_taken" min="0" step="1" placeholder="e.g. 2" value="<?= h($applicant['loans_taken'] ?? '0') ?>">
          <div class="note">All loans including credit cards</div>
        </div>
        <div class="fg">
          <label>Loans Fully Repaid</label>
          <input type="number" name="repayments_made" min="0" step="1" placeholder="e.g. 2" value="<?= h($applicant['repayments_made'] ?? '0') ?>">
          <div class="note">How many loans are fully closed</div>
        </div>
      </div>
    </div>

    <div class="disclaimer">⚠️ This is a student simulation. No data is stored. Not a real credit check.</div>
    <button type="submit" class="submit-btn">Check My Score with <?= h($engine['company_name']) ?> →</button>
  </form>
</div>
<?php endif; ?>

<div class="footer">Powered by <?= strtoupper(h($engine['company_name'])) ?> &nbsp;·&nbsp; Student FinTech Simulator · By Prajwal Manikantan</div>
</body>
</html>
