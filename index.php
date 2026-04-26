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
