<?php
$status_labels = [];
$status_values = [];
foreach ($status_mix as $row) {
    $status_labels[] = status_label($row['status']);
    $status_values[] = (int)$row['total'];
}
?>

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
  <div>
    <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">My Parish Connect</div>
    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Welcome, <?= html_escape($current_user['first_name']) ?> 👋</h1>
    <p class="text-gray-500 text-sm mt-1">Track your parish requests, upcoming services, Mass intentions, and account activity.</p>
  </div>
  <a href="<?= site_url('my/bookings') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold">
    <i class="ph ph-calendar-plus"></i> Book a Service
  </a>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="w-10 h-10 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center"><i class="ph ph-files text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$stats['total_bookings'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Total service applications</div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center"><i class="ph ph-hourglass-medium text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$stats['pending'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Requests still in progress</div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center"><i class="ph ph-check-circle text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$stats['completed'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Completed services</div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center"><i class="ph ph-certificate text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$stats['certificates'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Certificate requests</div>
  </div>
</div>

<section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6 mb-6">
  <div class="flex items-center justify-between gap-4 mb-5">
    <div>
      <h2 class="font-semibold text-gray-900">My Current Report</h2>
      <p class="text-xs text-gray-400 mt-1">Items that may need your attention or are coming up soon.</p>
    </div>
    <span class="text-xs text-gray-400">Personal</span>
  </div>

  <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
    <a href="<?= site_url('my/bookings') ?>" class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 hover:border-parish-200 transition">
      <i class="ph ph-calendar-check text-lg text-parish-700"></i>
      <div class="text-xl font-semibold text-gray-900 mt-2"><?= (int)$personal_report['upcoming_services'] ?></div>
      <div class="text-[11px] text-gray-500 mt-1">Upcoming services</div>
    </a>
    <a href="<?= site_url('my/certificates') ?>" class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 hover:border-parish-200 transition">
      <i class="ph ph-certificate text-lg text-parish-700"></i>
      <div class="text-xl font-semibold text-gray-900 mt-2"><?= (int)$personal_report['open_certificates'] ?></div>
      <div class="text-[11px] text-gray-500 mt-1">Open certificate requests</div>
    </a>
    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
      <i class="ph ph-bell text-lg text-parish-700"></i>
      <div class="text-xl font-semibold text-gray-900 mt-2"><?= (int)$personal_report['unread_notifications'] ?></div>
      <div class="text-[11px] text-gray-500 mt-1">Unread notifications</div>
    </div>
    <a href="<?= site_url('my/mass-intentions') ?>" class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 hover:border-parish-200 transition">
      <i class="ph ph-hands-praying text-lg text-parish-700"></i>
      <div class="text-xl font-semibold text-gray-900 mt-2"><?= (int)$personal_report['mass_intentions_ready'] ?></div>
      <div class="text-[11px] text-gray-500 mt-1">Intentions ready for reading</div>
    </a>
    <a href="<?= site_url('my/payments') ?>" class="rounded-xl border border-gray-100 bg-parish-50/50 p-4 hover:border-parish-200 transition col-span-2 lg:col-span-1">
      <i class="ph ph-receipt text-lg text-parish-700"></i>
      <div class="text-lg font-semibold text-gray-900 mt-2"><?= peso($personal_report['verified_payments']) ?></div>
      <div class="text-[11px] text-gray-500 mt-1">Total verified payments</div>
    </a>
  </div>
</section>

<div class="flex items-center gap-3 mb-4">
  <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center"><i class="ph ph-chart-line-up text-lg"></i></div>
  <div>
    <h2 class="font-semibold text-gray-900">My Activity Reports</h2>
    <p class="text-xs text-gray-400">A simple history of your requests and their current status.</p>
  </div>
</div>

<div class="grid xl:grid-cols-[1.2fr_.8fr] gap-6 mb-8">
  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="mb-5">
      <h3 class="font-semibold text-gray-800">6-Month Activity</h3>
      <p class="text-xs text-gray-400 mt-1">Service bookings, certificate requests, and Mass intentions you submitted.</p>
    </div>
    <div class="h-72"><canvas id="parishioner-activity-trend"></canvas></div>
  </section>

  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="mb-5">
      <h3 class="font-semibold text-gray-800">Booking Status</h3>
      <p class="text-xs text-gray-400 mt-1">Current distribution of your service applications.</p>
    </div>
    <div class="h-72"><canvas id="parishioner-status-mix"></canvas></div>
  </section>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <section class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-semibold text-gray-800">Recent Applications</h2>
        <p class="text-xs text-gray-400 mt-1">Latest service booking activity.</p>
      </div>
      <a href="<?= site_url('my/bookings') ?>" class="text-xs font-semibold text-parish-700 hover:underline">View all</a>
    </div>

    <?php if (empty($bookings)): ?>
      <div class="text-center py-10">
        <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center text-xl mx-auto"><i class="ph ph-calendar-plus"></i></div>
        <p class="text-sm text-gray-400 mt-3">No applications yet.</p>
        <div class="mt-3"><a href="<?= site_url('my/bookings') ?>" class="text-sm text-parish-700 font-medium hover:underline">Book a service →</a></div>
      </div>
    <?php else: ?>
      <div class="divide-y divide-gray-50">
        <?php foreach ($bookings as $b): ?>
        <a href="<?= site_url('my/bookings/' . $b['id']) ?>" class="flex items-center justify-between gap-4 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
          <div class="min-w-0">
            <div class="text-sm font-medium text-gray-800"><?= html_escape($b['service_name']) ?></div>
            <div class="text-xs text-gray-400 mt-1"><?= html_escape($b['booking_code']) ?> · <?= format_date($b['created_at']) ?></div>
          </div>
          <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge_class($b['status']) ?> whitespace-nowrap"><?= status_label($b['status']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Announcements</h2>
    <div class="space-y-4">
      <?php if (empty($announcements)): ?>
        <p class="text-sm text-gray-400">No announcements yet.</p>
      <?php endif; ?>
      <?php foreach ($announcements as $a): ?>
      <a href="<?= site_url('announcements/' . $a['slug']) ?>" class="block group">
        <div class="font-medium text-sm text-gray-700 group-hover:text-parish-700"><?= html_escape($a['title']) ?></div>
        <div class="text-xs text-gray-400 mt-1"><?= format_date($a['publish_date']) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<script>
(function(){
  if(typeof Chart === 'undefined') return;
  Chart.defaults.font.family = "'Google Sans','Product Sans',sans-serif";
  Chart.defaults.color = '#6b7280';

  var activity = <?= json_encode($activity_trend) ?>;
  new Chart(document.getElementById('parishioner-activity-trend'), {
    type:'bar',
    data:{
      labels:activity.map(function(x){return x.label;}),
      datasets:[
        {label:'Bookings',data:activity.map(function(x){return x.bookings;}),backgroundColor:'#235a38',borderRadius:6},
        {label:'Certificates',data:activity.map(function(x){return x.certificates;}),backgroundColor:'#c8901d',borderRadius:6},
        {label:'Mass intentions',data:activity.map(function(x){return x.intentions;}),backgroundColor:'#64748b',borderRadius:6}
      ]
    },
    options:{
      maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{boxWidth:10,usePointStyle:true}}},
      scales:{y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#f3f4f6'}},x:{grid:{display:false}}}
    }
  });

  new Chart(document.getElementById('parishioner-status-mix'), {
    type:'doughnut',
    data:{
      labels:<?= json_encode($status_labels) ?>,
      datasets:[{data:<?= json_encode($status_values) ?>,backgroundColor:['#235a38','#3a8f5b','#8bc99f','#c8901d','#e7bf4d','#64748b','#94a3b8','#dc2626'],borderWidth:0}]
    },
    options:{maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{boxWidth:9,usePointStyle:true,padding:11}}}}
  });
})();
</script>
