<?php
$parish_name = trim((string)($settings['parish_name'] ?? '')) ?: 'Sta. Monica Parish Church';
$parish_address = trim((string)($settings['parish_address'] ?? ''));
$parish_contact = trim((string)($settings['parish_contact'] ?? ''));
$is_released = ($cert['status'] ?? '') === 'released';
$is_no_record = ($cert['certificate_type'] ?? '') === 'no_record';
$document_title = $is_no_record
    ? 'Certificate of No Record'
    : ucfirst((string)$cert['certificate_type']) . ' Certificate';
$subject_name = $record['full_name'] ?? trim((string)$cert['first_name'] . ' ' . (string)$cert['last_name']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= html_escape($document_title . ' - ' . $cert['request_code']) ?></title>
  <style>
    :root{--green:#173e2a;--gold:#a97924;--ink:#1f2937;--muted:#6b7280;--line:#d1d5db}
    *{box-sizing:border-box}
    body{margin:0;background:#eef2ef;color:var(--ink);font-family:Georgia,'Times New Roman',serif}
    .toolbar{max-width:900px;margin:18px auto 0;padding:0 18px;display:flex;justify-content:flex-end;gap:10px;font-family:Arial,sans-serif}
    .btn{border:1px solid #d1d5db;background:white;border-radius:10px;padding:10px 14px;font-size:13px;cursor:pointer;text-decoration:none;color:#374151}
    .btn-primary{background:var(--green);border-color:var(--green);color:white}
    .sheet{position:relative;width:min(900px,calc(100% - 36px));min-height:1120px;margin:16px auto 28px;background:#fff;padding:68px 78px 70px;box-shadow:0 18px 50px rgba(15,23,42,.12);overflow:hidden}
    .sheet:before{content:'';position:absolute;inset:24px;border:2px solid var(--green);pointer-events:none}
    .sheet:after{content:'';position:absolute;inset:31px;border:1px solid rgba(169,121,36,.65);pointer-events:none}
    .watermark{position:absolute;left:50%;top:51%;transform:translate(-50%,-50%) rotate(-28deg);font-family:Arial,sans-serif;font-size:72px;font-weight:800;letter-spacing:.12em;color:rgba(185,28,28,.07);white-space:nowrap;pointer-events:none}
    .seal{width:76px;height:76px;margin:0 auto 14px;border-radius:50%;border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;color:var(--green);font-weight:bold;font-size:11px;text-align:center;line-height:1.15;padding:8px}
    .header{text-align:center}
    .parish{font-size:25px;font-weight:bold;color:var(--green)}
    .address{font-family:Arial,sans-serif;font-size:11px;color:var(--muted);margin-top:5px;line-height:1.5}
    .ornament{width:120px;height:1px;background:var(--gold);margin:22px auto;position:relative}.ornament:after{content:'✦';position:absolute;left:50%;top:50%;transform:translate(-50%,-52%);background:#fff;color:var(--gold);padding:0 9px;font-size:13px}
    h1{text-align:center;text-transform:uppercase;letter-spacing:.12em;font-size:24px;color:var(--green);margin:0}
    .code{text-align:center;font-family:Arial,sans-serif;font-size:10px;color:var(--muted);margin-top:8px;letter-spacing:.08em}
    .body{margin-top:40px;font-size:17px;line-height:1.85;text-align:justify}
    .name{display:block;text-align:center;font-size:26px;font-weight:bold;color:#111827;margin:18px 0 12px;text-transform:uppercase;letter-spacing:.03em}
    .details{margin:30px auto 0;max-width:650px;border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:18px 0;font-family:Arial,sans-serif;font-size:12px}
    .row{display:flex;gap:24px;padding:6px 0}.label{width:155px;flex:0 0 155px;color:var(--muted)}.value{font-weight:600;color:#374151;flex:1}
    .statement{margin-top:32px;font-size:16px;line-height:1.8;text-align:justify}
    .signature{margin-top:72px;display:grid;grid-template-columns:1fr 1fr;gap:70px;text-align:center;font-family:Arial,sans-serif}
    .sig-line{border-top:1px solid #4b5563;padding-top:8px;font-size:11px}.sig-name{font-weight:700;font-size:12px;color:#111827}.sig-role{color:var(--muted);margin-top:2px}
    .verify{margin-top:46px;border-top:1px dashed #d1d5db;padding-top:16px;font-family:Arial,sans-serif;font-size:10px;color:var(--muted);line-height:1.5;text-align:center;word-break:break-all}
    .status{display:inline-block;margin-top:9px;padding:4px 9px;border-radius:999px;font-family:Arial,sans-serif;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;background:#ecfdf3;color:#047857}
    .status.preview{background:#fff7ed;color:#b45309}
    @media(max-width:700px){.sheet{padding:52px 42px;min-height:0}.sheet:before{inset:14px}.sheet:after{inset:20px}.row{display:block}.label{width:auto;margin-bottom:2px}.signature{grid-template-columns:1fr;gap:55px}.body,.statement{font-size:15px}.name{font-size:22px}}
    @media print{@page{size:A4;margin:0}.toolbar{display:none}.sheet{width:210mm;min-height:297mm;margin:0;box-shadow:none;padding:20mm 21mm}.sheet:before{inset:7mm}.sheet:after{inset:9mm}body{background:#fff}.watermark{font-size:55px}}
  </style>
</head>
<body>
  <div class="toolbar">
    <button class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button>
    <button class="btn" onclick="window.close()">Close</button>
  </div>

  <main class="sheet">
    <?php if (!$is_released): ?><div class="watermark">NOT YET RELEASED</div><?php endif; ?>

    <header class="header">
      <div class="seal">STA.<br>MONICA<br>PARISH</div>
      <div class="parish"><?= html_escape($parish_name) ?></div>
      <?php if ($parish_address !== '' || $parish_contact !== ''): ?>
        <div class="address"><?= html_escape($parish_address) ?><?= $parish_contact !== '' ? '<br>' . html_escape($parish_contact) : '' ?></div>
      <?php endif; ?>
      <div class="ornament"></div>
      <h1><?= html_escape($document_title) ?></h1>
      <div class="code">DOCUMENT REFERENCE: <?= html_escape($cert['request_code']) ?></div>
      <div class="status <?= $is_released ? '' : 'preview' ?>"><?= $is_released ? 'Released / Issued' : 'Prepared — Awaiting Release' ?></div>
    </header>

    <?php if ($is_no_record): ?>
      <section class="body">
        This is to certify that, after a search of the available parish sacramental registers in relation to the request of
        <span class="name"><?= html_escape($subject_name) ?></span>
        no matching record was located based on the information supplied and the records reviewed by the parish office at the time this certificate was prepared.
      </section>
      <p class="statement">This certification is issued upon request for the purpose stated in the parish transaction record. It should be interpreted only in relation to the parish registers and search information actually reviewed.</p>
    <?php else: ?>
      <section class="body">
        This is to certify that the parish sacramental register contains a record for
        <span class="name"><?= html_escape($subject_name) ?></span>
        corresponding to the sacramental entry summarized below.
      </section>

      <div class="details">
        <?php if (!empty($record['birth_date'])): ?><div class="row"><div class="label">Date of Birth</div><div class="value"><?= html_escape(format_date($record['birth_date'])) ?></div></div><?php endif; ?>
        <?php if (!empty($record['sacrament_date'])): ?><div class="row"><div class="label">Sacrament Date</div><div class="value"><?= html_escape(format_date($record['sacrament_date'])) ?></div></div><?php endif; ?>
        <?php if (!empty($record['father_name'])): ?><div class="row"><div class="label">Father</div><div class="value"><?= html_escape($record['father_name']) ?></div></div><?php endif; ?>
        <?php if (!empty($record['mother_name'])): ?><div class="row"><div class="label">Mother</div><div class="value"><?= html_escape($record['mother_name']) ?></div></div><?php endif; ?>
        <?php if (!empty($record['spouse_name'])): ?><div class="row"><div class="label">Spouse</div><div class="value"><?= html_escape($record['spouse_name']) ?></div></div><?php endif; ?>
        <?php if (!empty($record['minister_name'])): ?><div class="row"><div class="label">Minister</div><div class="value"><?= html_escape($record['minister_name']) ?></div></div><?php endif; ?>
        <div class="row"><div class="label">Registry Reference</div><div class="value">Book <?= html_escape($record['registry_book'] ?: '—') ?> · Page <?= html_escape($record['registry_page'] ?: '—') ?> · Entry <?= html_escape($record['registry_entry_no'] ?: '—') ?></div></div>
      </div>

      <p class="statement">This certification is issued from a verified parish registry entry upon the request of the concerned party for the stated purpose.</p>
    <?php endif; ?>

    <div class="signature">
      <div><div class="sig-line"><div class="sig-name">Authorized Parish Signatory</div><div class="sig-role">Parish Priest / Authorized Representative</div></div></div>
      <div><div class="sig-line"><div class="sig-name"><?= html_escape($processor ?: 'Parish Office') ?></div><div class="sig-role">Prepared by Parish Office</div></div></div>
    </div>

    <div class="verify">
      Verification address:<br><?= html_escape($verification_url) ?><br>
      <?= $is_released ? 'The public verification page should show this document as Released.' : 'Preview only. This certificate must not be represented as issued until parish staff marks it Released.' ?>
    </div>
  </main>
</body>
</html>
