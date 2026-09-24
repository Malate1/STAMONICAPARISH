<?php
$status_labels = [];
$status_values = [];
foreach ($status_mix as $row) {
    $status_labels[] = status_label($row['status']);
    $status_values[] = (int)$row['total'];
}
$service_labels = array_map(fn($r) => $r['name'], $service_mix);
$service_values = array_map(fn($r) => (int)$r['total'], $service_mix);
?>

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
  <div>
    <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Parish Secretary</div>
    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Daily Operations Dashboard</h1>
    <p class="text-gray-500 text-sm mt-1">Welcome, <?= html_escape($current_user['first_name']) ?>. Track today’s workload, request flow, and service demand.</p>
  </div>
  <div class="text-xs text-gray-400">As of <?= date('M j, Y g:i A') ?></div>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
  <a href="<?= site_url('staff/booking') ?>?status=submitted" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-parish-300 transition">
    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center"><i class="ph ph-hourglass-medium text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$needs_attention['awaiting_verification'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Bookings awaiting verification</div>
  </a>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center"><i class="ph ph-warning-circle text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$needs_attention['incomplete_requirements'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Incomplete requirements</div>
  </div>
  <a href="<?= site_url('staff/payment') ?>" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-parish-300 transition">
    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center"><i class="ph ph-wallet text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$needs_attention['payments_awaiting'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Payments awaiting review</div>
  </a>
  <a href="<?= site_url('staff/certificate') ?>" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-parish-300 transition">
    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center"><i class="ph ph-certificate text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$needs_attention['certificates_ready'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Certificates ready for release</div>
  </a>
</div>

<div class="flex items-center gap-3 mb-4">
  <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center"><i class="ph ph-chart-line-up text-lg"></i></div>
  <div>
    <h2 class="font-semibold text-gray-900">Workload Reports</h2>
    <p class="text-xs text-gray-400">Operational reports designed for daily parish office decisions.</p>
  </div>
</div>

<div class="grid xl:grid-cols-[1.2fr_.8fr] gap-6 mb-6">
  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="mb-5">
      <h3 class="font-semibold text-gray-800">14-Day Request Intake</h3>
      <p class="text-xs text-gray-400 mt-1">New bookings, certificate requests, and payment records received each day.</p>
    </div>
    <div class="h-72"><canvas id="staff-intake-trend"></canvas></div>
  </section>

  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="mb-5">
      <h3 class="font-semibold text-gray-800">Booking Status Mix</h3>
      <p class="text-xs text-gray-400 mt-1">Where current booking applications are in the workflow.</p>
    </div>
    <div class="h-72"><canvas id="staff-status-mix"></canvas></div>
  </section>
</div>

<div class="grid xl:grid-cols-[.9fr_1.1fr] gap-6 mb-8">
  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="mb-5">
      <h3 class="font-semibold text-gray-800">Most Requested Services</h3>
      <p class="text-xs text-gray-400 mt-1">Service demand during the last 90 days.</p>
    </div>
    <div class="h-72"><canvas id="staff-service-mix"></canvas></div>
  </section>

  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="flex items-center justify-between gap-4 mb-5">
      <div>
        <h3 class="font-semibold text-gray-800">Monthly Office Report</h3>
        <p class="text-xs text-gray-400 mt-1">Current-month totals for common secretary workflows.</p>
      </div>
      <span class="text-xs text-gray-400"><?= date('F Y') ?></span>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
      <?php
      $report_cards = [
        ['Bookings received', $office_report['bookings_received'], 'ph-calendar-plus'],
        ['Bookings completed', $office_report['bookings_completed'], 'ph-check-circle'],
        ['Certificates received', $office_report['certificates_received'], 'ph-files'],
        ['Certificates released', $office_report['certificates_released'], 'ph-certificate'],
        ['Payments verified', $office_report['payments_verified'], 'ph-receipt'],
        ['Intentions ready', $office_report['mass_intentions_ready'], 'ph-hands-praying'],
      ];
      foreach ($report_cards as $card):
      ?>
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
          <i class="ph <?= $card[2] ?> text-lg text-parish-700"></i>
          <div class="text-xl font-semibold text-gray-900 mt-2"><?= (int)$card[1] ?></div>
          <div class="text-[11px] text-gray-500 mt-1"><?= html_escape($card[0]) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<div class="grid lg:grid-cols-2 gap-6">
  <section class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Today's Confirmed Services</h2>
    <?php if (empty($today_summary)): ?>
      <div class="text-center py-10">
        <i class="ph ph-calendar-blank text-3xl text-gray-300"></i>
        <p class="text-sm text-gray-400 mt-2">No confirmed services today.</p>
      </div>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach ($today_summary as $t): ?>
        <div class="flex items-center justify-between text-sm py-2.5 border-b border-gray-50 last:border-0">
          <span class="text-gray-700"><?= html_escape($t['service_name']) ?></span>
          <span class="px-2.5 py-1 rounded-full bg-parish-50 text-parish-700 font-semibold"><?= (int)$t['total'] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-2 gap-3">
      <a href="<?= site_url('staff/walkin') ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-100 hover:border-parish-300 text-center">
        <i class="ph ph-user-plus text-2xl text-parish-700"></i>
        <span class="text-xs font-medium text-gray-700">Record Walk-in</span>
      </a>
      <a href="<?= site_url('staff/booking') ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-100 hover:border-parish-300 text-center">
        <i class="ph ph-calendar-check text-2xl text-parish-700"></i>
        <span class="text-xs font-medium text-gray-700">Review Bookings</span>
      </a>
      <a href="<?= site_url('staff/mass_intention') ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-100 hover:border-parish-300 text-center">
        <i class="ph ph-hands-praying text-2xl text-parish-700"></i>
        <span class="text-xs font-medium text-gray-700">Mass Intentions</span>
      </a>
      <a href="<?= site_url('staff/record') ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-100 hover:border-parish-300 text-center">
        <i class="ph ph-archive text-2xl text-parish-700"></i>
        <span class="text-xs font-medium text-gray-700">Sacramental Records</span>
      </a>
    </div>
  </section>
</div>

<script>
(function(){
  if(typeof Chart === 'undefined') return;
  Chart.defaults.font.family = "'Google Sans','Product Sans',sans-serif";
  Chart.defaults.color = '#6b7280';

  var intake = <?= json_encode($intake_trend) ?>;
  new Chart(document.getElementById('staff-intake-trend'), {
    type:'line',
    data:{
      labels:intake.map(function(x){return x.label;}),
      datasets:[
        {label:'Bookings',data:intake.map(function(x){return x.bookings;}),borderColor:'#235a38',backgroundColor:'rgba(35,90,56,.08)',tension:.35,pointRadius:2},
        {label:'Certificates',data:intake.map(function(x){return x.certificates;}),borderColor:'#c8901d',backgroundColor:'rgba(200,144,29,.08)',tension:.35,pointRadius:2},
        {label:'Payments',data:intake.map(function(x){return x.payments;}),borderColor:'#64748b',backgroundColor:'rgba(100,116,139,.08)',tension:.35,pointRadius:2}
      ]
    },
    options:{
      maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{boxWidth:10,usePointStyle:true}}},
      scales:{y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#f3f4f6'}},x:{grid:{display:false}}}
    }
  });

  new Chart(document.getElementById('staff-status-mix'), {
    type:'doughnut',
    data:{
      labels:<?= json_encode($status_labels) ?>,
      datasets:[{data:<?= json_encode($status_values) ?>,backgroundColor:['#235a38','#3a8f5b','#8bc99f','#c8901d','#e7bf4d','#64748b','#94a3b8','#dc2626'],borderWidth:0}]
    },
    options:{maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{boxWidth:9,usePointStyle:true,padding:10}}}}
  });

  new Chart(document.getElementById('staff-service-mix'), {
    type:'bar',
    data:{
      labels:<?= json_encode($service_labels) ?>,
      datasets:[{label:'Requests',data:<?= json_encode($service_values) ?>,backgroundColor:'#235a38',borderRadius:7}]
    },
    options:{
      indexAxis:'y',
      maintainAspectRatio:false,
      plugins:{legend:{display:false}},
      scales:{x:{beginAtZero:true,ticks:{precision:0},grid:{color:'#f3f4f6'}},y:{grid:{display:false}}}
    }
  });
})();
</script>
