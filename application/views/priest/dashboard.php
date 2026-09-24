<?php
$service_labels = array_map(fn($r) => $r['name'], $service_mix);
$service_values = array_map(fn($r) => (int)$r['total'], $service_mix);
?>

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
  <div>
    <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Priest Dashboard</div>
    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Welcome, Fr. <?= html_escape($current_user['first_name']) ?></h1>
    <p class="text-gray-500 text-sm mt-1">Your sacramental workload, upcoming assignments, and reader preparation at a glance.</p>
  </div>
  <a href="<?= site_url('priest/schedule') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-parish-200 bg-white text-parish-700 text-sm font-semibold hover:bg-parish-50">
    <i class="ph ph-calendar-blank"></i> Full Schedule
  </a>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="w-10 h-10 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center"><i class="ph ph-calendar-check text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$priest_report['next_7_days'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Assignments in next 7 days</div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center"><i class="ph ph-calendar-dots text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$priest_report['next_30_days'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Assignments in next 30 days</div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center"><i class="ph ph-check-circle text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$priest_report['completed_this_month'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Completed this month</div>
  </div>
  <a href="<?= site_url('priest/mass-intentions') ?>" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-parish-300 transition">
    <div class="w-10 h-10 rounded-xl bg-gold-50 text-gold-700 flex items-center justify-center"><i class="ph ph-hands-praying text-xl"></i></div>
    <div class="text-2xl font-semibold text-gray-900 mt-4"><?= (int)$priest_report['reader_intentions'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Upcoming intentions ready for reading</div>
  </a>
</div>

<div class="flex items-center gap-3 mb-4">
  <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center"><i class="ph ph-chart-bar text-lg"></i></div>
  <div>
    <h2 class="font-semibold text-gray-900">Pastoral Workload Report</h2>
    <p class="text-xs text-gray-400">See upcoming service load and the kinds of ministries assigned to you.</p>
  </div>
</div>

<div class="grid xl:grid-cols-[1.2fr_.8fr] gap-6 mb-8">
  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="mb-5">
      <h3 class="font-semibold text-gray-800">Upcoming Assignments by Week</h3>
      <p class="text-xs text-gray-400 mt-1">Your scheduled sacramental and pastoral assignments over the next 8 weeks.</p>
    </div>
    <div class="h-72"><canvas id="priest-weekly-assignments"></canvas></div>
  </section>

  <section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="mb-5">
      <h3 class="font-semibold text-gray-800">Assignment Mix</h3>
      <p class="text-xs text-gray-400 mt-1">Services assigned to you during the last 180 days.</p>
    </div>
    <div class="h-72"><canvas id="priest-service-mix"></canvas></div>
  </section>
</div>

<section class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
  <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between gap-4">
    <div>
      <h2 class="font-semibold text-gray-800">This Week</h2>
      <p class="text-xs text-gray-400 mt-1">Your confirmed assignments in the next 7 days.</p>
    </div>
    <a href="<?= site_url('priest/schedule') ?>" class="text-xs font-semibold text-parish-700 hover:underline">View all</a>
  </div>

  <?php if (empty($this_week)): ?>
    <div class="text-center py-14">
      <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center text-xl mx-auto"><i class="ph ph-calendar-blank"></i></div>
      <p class="text-sm text-gray-400 mt-3">No assignments scheduled in the next 7 days.</p>
    </div>
  <?php else: ?>
    <div class="divide-y divide-gray-100">
      <?php foreach ($this_week as $a): ?>
      <a href="<?= site_url('priest/schedule/view/' . $a['id']) ?>" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-5 hover:bg-gray-50 transition">
        <div class="min-w-0">
          <div class="text-sm font-semibold text-gray-800"><?= html_escape($a['service_name']) ?></div>
          <div class="text-xs text-gray-500 mt-1"><?= html_escape($a['first_name'] . ' ' . $a['last_name']) ?></div>
          <div class="text-xs text-gray-400 mt-1"><i class="ph ph-clock mr-1"></i><?= format_datetime($a['confirmed_date']) ?></div>
        </div>
        <span class="self-start sm:self-auto px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge_class($a['status']) ?>"><?= status_label($a['status']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<script>
(function(){
  if(typeof Chart === 'undefined') return;
  Chart.defaults.font.family = "'Google Sans','Product Sans',sans-serif";
  Chart.defaults.color = '#6b7280';

  var weekly = <?= json_encode($weekly_assignments) ?>;
  new Chart(document.getElementById('priest-weekly-assignments'), {
    type:'bar',
    data:{
      labels:weekly.map(function(x){return x.label;}),
      datasets:[{label:'Assignments',data:weekly.map(function(x){return x.total;}),backgroundColor:'#235a38',borderRadius:8}]
    },
    options:{
      maintainAspectRatio:false,
      plugins:{legend:{display:false}},
      scales:{y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#f3f4f6'}},x:{grid:{display:false}}}
    }
  });

  new Chart(document.getElementById('priest-service-mix'), {
    type:'doughnut',
    data:{
      labels:<?= json_encode($service_labels) ?>,
      datasets:[{data:<?= json_encode($service_values) ?>,backgroundColor:['#235a38','#3a8f5b','#8bc99f','#c8901d','#e7bf4d','#64748b','#94a3b8'],borderWidth:0}]
    },
    options:{maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{boxWidth:9,usePointStyle:true,padding:12}}}}
  });
})();
</script>
