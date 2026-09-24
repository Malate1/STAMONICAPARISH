<?php
$service_labels = array_map(fn($r) => $r['name'], $service_mix);
$service_values = array_map(fn($r) => (int)$r['total'], $service_mix);
?>

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
  <div>
    <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Administrator</div>
    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Parish Operations Dashboard</h1>
    <p class="text-gray-500 text-sm mt-1">Live operational overview, workload trends, collections, and parish service reports.</p>
  </div>
  <div class="text-xs text-gray-400">
    Reporting period: <strong class="text-gray-600"><?= date('F Y') ?></strong>
  </div>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex items-center justify-between gap-3">
      <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center"><i class="ph ph-hourglass-medium text-xl"></i></div>
      <span class="text-[11px] uppercase tracking-wide text-gray-400">Needs action</span>
    </div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$needs_attention['awaiting_verification'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Bookings awaiting verification</div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex items-center justify-between gap-3">
      <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center"><i class="ph ph-warning-circle text-xl"></i></div>
      <span class="text-[11px] uppercase tracking-wide text-gray-400">Documents</span>
    </div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$needs_attention['incomplete_requirements'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Incomplete requirements</div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex items-center justify-between gap-3">
      <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center"><i class="ph ph-wallet text-xl"></i></div>
      <span class="text-[11px] uppercase tracking-wide text-gray-400">Payments</span>
    </div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$needs_attention['payments_awaiting'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Payments awaiting review</div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex items-center justify-between gap-3">
      <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center"><i class="ph ph-certificate text-xl"></i></div>
      <span class="text-[11px] uppercase tracking-wide text-gray-400">Release</span>
    </div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$needs_attention['certificates_ready'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Certificates ready for release</div>
  </div>
</div>

<div class="grid lg:grid-cols-4 gap-4 mb-8">
  <div class="bg-parish-900 text-white rounded-2xl p-5">
    <div class="text-xs text-white/60">Verified Collections · This Month</div>
    <div class="text-2xl font-semibold mt-2"><?= peso($revenue_this_month) ?></div>
    <div class="text-[11px] text-white/50 mt-2">Based on verified payment records</div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Registered Parishioners</div>
    <div class="text-2xl font-semibold text-gray-900 mt-2"><?= (int)$user_counts['parishioners'] ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Active Priests</div>
    <div class="text-2xl font-semibold text-gray-900 mt-2"><?= (int)$user_counts['priests'] ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Parish Secretaries</div>
    <div class="text-2xl font-semibold text-gray-900 mt-2"><?= (int)$user_counts['secretaries'] ?></div>
  </div>
</div>

<div class="flex items-center gap-3 mb-4">
  <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center"><i class="ph ph-chart-line-up text-lg"></i></div>
  <div>
    <h2 class="font-semibold text-gray-900">Reports & Trends</h2>
    <p class="text-xs text-gray-400">Useful historical indicators for planning parish office workload and services.</p>
  </div>
</div>

<div class="grid xl:grid-cols-2 gap-6 mb-6">
  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="flex items-start justify-between gap-4 mb-5">
      <div>
        <h3 class="font-semibold text-gray-800">Booking Volume</h3>
        <p class="text-xs text-gray-400 mt-1">New service applications received during the last 6 months.</p>
      </div>
      <span class="px-2.5 py-1 rounded-full bg-parish-50 text-parish-700 text-[11px] font-semibold">6 months</span>
    </div>
    <div class="h-72"><canvas id="admin-booking-trend"></canvas></div>
  </section>

  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="flex items-start justify-between gap-4 mb-5">
      <div>
        <h3 class="font-semibold text-gray-800">Verified Collections</h3>
        <p class="text-xs text-gray-400 mt-1">Monthly amount from payments marked verified.</p>
      </div>
      <span class="px-2.5 py-1 rounded-full bg-gold-50 text-gold-700 text-[11px] font-semibold">Financial</span>
    </div>
    <div class="h-72"><canvas id="admin-revenue-trend"></canvas></div>
  </section>
</div>

<div class="grid xl:grid-cols-[.9fr_1.1fr] gap-6 mb-8">
  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="mb-5">
      <h3 class="font-semibold text-gray-800">Service Demand</h3>
      <p class="text-xs text-gray-400 mt-1">Most requested parish services during the last 180 days.</p>
    </div>
    <div class="h-72"><canvas id="admin-service-mix"></canvas></div>
  </section>

  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="mb-5">
      <h3 class="font-semibold text-gray-800">New Accounts</h3>
      <p class="text-xs text-gray-400 mt-1">Registration growth by account type over the last 6 months.</p>
    </div>
    <div class="h-72"><canvas id="admin-user-growth"></canvas></div>
  </section>
</div>

<section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6 mb-8">
  <div class="flex items-center justify-between gap-4 mb-5">
    <div>
      <h2 class="font-semibold text-gray-900">Monthly Office Report</h2>
      <p class="text-xs text-gray-400 mt-1">Operational totals for <?= date('F Y') ?>.</p>
    </div>
    <span class="text-xs text-gray-400">As of <?= date('M j, Y') ?></span>
  </div>

  <div class="grid sm:grid-cols-2 xl:grid-cols-6 gap-3">
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
    <h2 class="font-semibold text-gray-800 mb-4">Recent Activity</h2>
    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
      <?php if (empty($recent_activity)): ?><p class="text-sm text-gray-400">No recent activity.</p><?php endif; ?>
      <?php foreach ($recent_activity as $a): ?>
      <div class="text-sm border-b border-gray-50 pb-3 last:border-0">
        <span class="font-medium text-gray-700"><?= html_escape(trim(($a['first_name'] ?? 'System') . ' ' . ($a['last_name'] ?? ''))) ?></span>
        <span class="text-gray-400">— <?= html_escape($a['action']) ?><?= $a['description'] ? ': ' . html_escape($a['description']) : '' ?></span>
        <div class="text-xs text-gray-300 mt-1"><?= format_datetime($a['created_at']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<script>
(function(){
  if(typeof Chart === 'undefined') return;

  Chart.defaults.font.family = "'Google Sans','Product Sans',sans-serif";
  Chart.defaults.color = '#6b7280';

  var bookingTrend = <?= json_encode($booking_trend) ?>;
  new Chart(document.getElementById('admin-booking-trend'), {
    type:'line',
    data:{
      labels:bookingTrend.map(function(x){return x.label;}),
      datasets:[{
        label:'Applications',
        data:bookingTrend.map(function(x){return x.total;}),
        borderColor:'#235a38',
        backgroundColor:'rgba(35,90,56,.10)',
        fill:true,
        tension:.35,
        pointRadius:3,
        pointHoverRadius:5
      }]
    },
    options:{
      maintainAspectRatio:false,
      plugins:{legend:{display:false}},
      scales:{y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#f3f4f6'}},x:{grid:{display:false}}}
    }
  });

  var revenueTrend = <?= json_encode($revenue_trend) ?>;
  new Chart(document.getElementById('admin-revenue-trend'), {
    type:'bar',
    data:{
      labels:revenueTrend.map(function(x){return x.label;}),
      datasets:[{
        label:'Verified collections',
        data:revenueTrend.map(function(x){return x.total;}),
        backgroundColor:'rgba(200,144,29,.78)',
        borderRadius:8
      }]
    },
    options:{
      maintainAspectRatio:false,
      plugins:{legend:{display:false},tooltip:{callbacks:{label:function(ctx){return '₱' + Number(ctx.raw||0).toLocaleString();}}}},
      scales:{y:{beginAtZero:true,ticks:{callback:function(v){return '₱'+Number(v).toLocaleString();}},grid:{color:'#f3f4f6'}},x:{grid:{display:false}}}
    }
  });

  new Chart(document.getElementById('admin-service-mix'), {
    type:'doughnut',
    data:{
      labels:<?= json_encode($service_labels) ?>,
      datasets:[{data:<?= json_encode($service_values) ?>,backgroundColor:['#235a38','#3a8f5b','#8bc99f','#c8901d','#e7bf4d','#64748b','#94a3b8'],borderWidth:0}]
    },
    options:{
      maintainAspectRatio:false,
      cutout:'65%',
      plugins:{legend:{position:'bottom',labels:{boxWidth:10,usePointStyle:true,padding:14}}}
    }
  });

  var growth = <?= json_encode($user_growth) ?>;
  new Chart(document.getElementById('admin-user-growth'), {
    type:'bar',
    data:{
      labels:growth.map(function(x){return x.label;}),
      datasets:[
        {label:'Parishioners',data:growth.map(function(x){return x.parishioners;}),backgroundColor:'#235a38',borderRadius:6},
        {label:'Staff',data:growth.map(function(x){return x.staff;}),backgroundColor:'#c8901d',borderRadius:6},
        {label:'Priests',data:growth.map(function(x){return x.priests;}),backgroundColor:'#64748b',borderRadius:6}
      ]
    },
    options:{
      maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{boxWidth:10,usePointStyle:true}}},
      scales:{y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#f3f4f6'}},x:{grid:{display:false}}}
    }
  });
})();
</script>
