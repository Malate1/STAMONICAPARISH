<?php
$groups = [];
$type_labels = [
    'thanksgiving' => 'Thanksgiving',
    'birthday' => 'Birthday Intentions',
    'anniversary' => 'Anniversaries',
    'healing' => 'Healing & Recovery',
    'safe_travel' => 'Safe Travel',
    'special' => 'Special Intentions',
    'souls_departed' => 'Souls of the Faithful Departed',
    'other' => 'Other Intentions',
];

foreach ($intentions as $row) {
    $groups[$row['intention_type']][] = $row;
}

$selected_mass = !empty($intentions) ? $intentions[0] : null;
if (!$selected_mass && $mass_date && $schedule_id) {
    foreach ($occurrences as $occ) {
        if ($occ['mass_date'] === $mass_date && (int)$occ['mass_schedule_id'] === (int)$schedule_id) {
            $selected_mass = $occ;
            break;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mass Intention Reader Sheet<?= $mass_date ? ' - ' . html_escape($mass_date) : '' ?></title>
  <style>
    :root{--green:#173b29;--green2:#235a38;--gold:#c8901d;--ink:#1f2937;--muted:#6b7280;--line:#e5e7eb;--paper:#fff;--bg:#f3f5f4}
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);font-family:Arial,Helvetica,sans-serif;color:var(--ink)}
    .toolbar{position:sticky;top:0;z-index:10;background:#fff;border-bottom:1px solid var(--line);padding:12px 16px}
    .toolbar-inner{max-width:980px;margin:0 auto;display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap}
    .toolbar-left,.toolbar-right{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    select,button,a.btn{font:inherit;font-size:13px;border-radius:10px;border:1px solid #d1d5db;background:#fff;padding:9px 12px;color:var(--ink);text-decoration:none}
    button.primary{background:var(--green2);color:white;border-color:var(--green2);cursor:pointer;font-weight:700}
    .sheet{max-width:900px;margin:24px auto 48px;background:var(--paper);padding:44px 52px;border-radius:18px;box-shadow:0 10px 35px rgba(0,0,0,.08)}
    .eyebrow{font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);font-weight:700}
    h1{font-size:30px;margin:8px 0 6px;color:var(--green)}
    .mass-meta{font-size:15px;color:var(--muted);line-height:1.6}
    .rule{height:3px;background:linear-gradient(90deg,var(--green),var(--gold),transparent);margin:22px 0 28px}
    .intro{font-size:14px;line-height:1.65;color:#4b5563;margin-bottom:26px}
    .group{margin:26px 0}
    .group h2{font-size:15px;text-transform:uppercase;letter-spacing:.08em;color:var(--green);margin:0 0 10px;padding-bottom:8px;border-bottom:1px solid var(--line)}
    .group ul{list-style:none;padding:0;margin:0}
    .group li{font-size:17px;line-height:1.55;padding:5px 0 5px 22px;position:relative}
    .group li:before{content:"•";position:absolute;left:4px;color:var(--gold);font-weight:700}
    .departed li:before{content:"†";font-size:15px;top:7px}
    .empty{text-align:center;padding:54px 16px;color:var(--muted)}
    .empty strong{display:block;color:var(--green);font-size:18px;margin-bottom:8px}
    .footer-note{margin-top:38px;padding-top:16px;border-top:1px solid var(--line);font-size:11px;color:#9ca3af;display:flex;justify-content:space-between;gap:20px}
    @media(max-width:700px){
      .sheet{margin:0;border-radius:0;padding:28px 22px;box-shadow:none}
      .toolbar{position:relative}
      .toolbar-left,.toolbar-right{width:100%}
      select{width:100%}
      h1{font-size:25px}
    }
    @media print{
      body{background:#fff}
      .toolbar{display:none!important}
      .sheet{max-width:none;margin:0;padding:12mm 14mm;border-radius:0;box-shadow:none}
      .group{break-inside:avoid}
      .footer-note{position:fixed;bottom:8mm;left:14mm;right:14mm}
      @page{size:A4;margin:8mm}
    }
  </style>
</head>
<body>
  <div class="toolbar">
    <div class="toolbar-inner">
      <div class="toolbar-left">
        <a class="btn" href="<?= html_escape($back_url) ?>">← Back</a>
        <form method="get" action="" id="reader-filter">
          <select name="occurrence" onchange="changeOccurrence(this.value)">
            <option value="">Select a ready Mass…</option>
            <?php foreach ($occurrences as $occ): ?>
              <?php $value = $occ['mass_date'] . '|' . $occ['mass_schedule_id']; ?>
              <option value="<?= html_escape($value) ?>" <?= ($mass_date === $occ['mass_date'] && (int)$schedule_id === (int)$occ['mass_schedule_id']) ? 'selected' : '' ?>>
                <?= format_date($occ['mass_date'], 'D, M j, Y') ?> · <?= date('g:i A', strtotime($occ['mass_time'])) ?> · <?= html_escape($occ['mass_title'] ?: 'Mass') ?> (<?= (int)$occ['intention_count'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
      <div class="toolbar-right">
        <?php if (!empty($can_manage) && $mass_date && $schedule_id && !empty($intentions)): ?>
          <button type="button" onclick="completeMass()">Mark Mass Completed</button>
        <?php endif; ?>
        <button type="button" class="primary" onclick="window.print()">Print Reader Sheet</button>
      </div>
    </div>
  </div>

  <main class="sheet">
    <div class="eyebrow">Sta. Monica Parish Church · Alburquerque, Bohol</div>
    <h1>Mass Intention Reader Sheet</h1>

    <?php if ($mass_date && $selected_mass): ?>
      <div class="mass-meta">
        <strong><?= format_date($mass_date, 'l, F j, Y') ?></strong><br>
        <?= date('g:i A', strtotime($selected_mass['mass_time'])) ?> · <?= html_escape($selected_mass['mass_title'] ?: 'Holy Mass') ?>
        <?php if (!empty($selected_mass['location'])): ?> · <?= html_escape($selected_mass['location']) ?><?php endif; ?>
        <?php if (!empty($selected_mass['language'])): ?><br><?= html_escape($selected_mass['language']) ?><?php endif; ?>
      </div>
    <?php else: ?>
      <div class="mass-meta">No Mass selected.</div>
    <?php endif; ?>

    <div class="rule"></div>

    <?php if (!empty($intentions)): ?>
      <p class="intro">
        The following intentions have been reviewed by the parish office and marked <strong>Ready for Reading</strong>.
        Please read names carefully and follow the parish’s customary announcement format before the celebration of the Mass.
      </p>

      <?php foreach ($type_labels as $type => $label): ?>
        <?php if (empty($groups[$type])) continue; ?>
        <section class="group <?= $type === 'souls_departed' ? 'departed' : '' ?>">
          <h2><?= html_escape($label) ?></h2>
          <ul>
            <?php foreach ($groups[$type] as $item): ?>
              <li><?= html_escape($item['offered_for']) ?></li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty">
        <strong>No intentions are Ready for Reading for this Mass.</strong>
        Only intentions finalized by the parish secretary appear on this sheet.
      </div>
    <?php endif; ?>

    <div class="footer-note">
      <span>Commentator / Reader Copy</span>
      <span>Generated <?= date('M j, Y g:i A') ?></span>
    </div>
  </main>

<script>
function changeOccurrence(value){
  if(!value) return;
  var parts = value.split('|');
  if(parts.length !== 2) return;
  var url = new URL(window.location.href);
  url.searchParams.set('date', parts[0]);
  url.searchParams.set('schedule_id', parts[1]);
  window.location.href = url.toString();
}

<?php if (!empty($can_manage)): ?>
function completeMass(){
  if(!confirm('Mark all Ready for Reading intentions for this Mass as completed? Use this only after the Mass.')) return;

  var body = new URLSearchParams();
  body.set('mass_date', <?= json_encode($mass_date) ?>);
  body.set('schedule_id', <?= json_encode((string)$schedule_id) ?>);

  fetch(<?= json_encode(site_url('staff/mass-intention-reader/complete')) ?>, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
    body:body.toString()
  })
  .then(function(r){ return r.json(); })
  .then(function(res){
    alert(res.message || (res.success ? 'Completed.' : 'Could not update.'));
    if(res.success) window.location.reload();
  })
  .catch(function(){ alert('Could not update the Mass intentions.'); });
}
<?php endif; ?>
</script>
</body>
</html>
