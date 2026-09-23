<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Santa_Monica_Church_Alburquerque_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
$today_idx = (int) date('w');
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[360px] flex items-end">
  <img src="<?= $hero_img ?>" alt="Santa Monica Parish Church" class="absolute inset-0 w-full h-full object-cover object-center">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900/95 via-parish-900/70 to-black/25"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Worship with us</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Mass Schedule</h1>
    <p class="text-white/70 mt-3 max-w-2xl text-lg">Plan your visit and join the parish community in prayer at Sta. Monica Parish Church, Alburquerque, Bohol.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5 mb-8">
    <div>
      <div class="heritage-kicker text-gold-600">Regular weekly schedule</div>
      <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-2">Find a Mass that fits your visit.</h2>
      <p class="text-gray-500 mt-2">Times shown below are based on the current parish schedule.</p>
    </div>
    <div class="inline-flex items-center gap-2 text-sm text-gray-500 bg-white border border-stonewarm-200 rounded-full px-4 py-2.5 self-start">
      <i class="ph ph-info text-parish-700"></i>
      Schedule changes are posted in Announcements
    </div>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <?php
      $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
      foreach ($days as $i => $label):
        $masses = $grid[$i] ?? [];
        $is_today = $i === $today_idx;
    ?>
    <div class="relative rounded-3xl border p-5 sm:p-6 min-h-[190px] transition <?= $is_today ? 'bg-parish-900 border-parish-900 text-white shadow-heritage' : 'bg-white border-stonewarm-200 hover:border-parish-200 hover:shadow-soft' ?>">
      <?php if ($is_today): ?>
        <span class="absolute top-4 right-4 text-[10px] font-bold uppercase tracking-wider bg-gold-400 text-parish-900 rounded-full px-2.5 py-1">Today</span>
      <?php endif; ?>
      <div class="text-sm font-semibold <?= $is_today ? 'text-gold-200' : 'text-gold-600' ?>"><?= $label ?></div>
      <div class="mt-5 space-y-3">
        <?php if (empty($masses)): ?>
          <div class="text-sm <?= $is_today ? 'text-white/55' : 'text-gray-400' ?>">No Mass scheduled</div>
        <?php else: ?>
          <?php foreach ($masses as $m): ?>
          <div class="<?= $is_today ? 'border-white/10' : 'border-stonewarm-200' ?> border-b last:border-0 pb-3 last:pb-0">
            <div class="text-xl font-bold <?= $is_today ? 'text-white' : 'text-parish-800' ?>"><?= date('g:i A', strtotime($m['mass_time'])) ?></div>
            <div class="text-xs mt-1 <?= $is_today ? 'text-white/55' : 'text-gray-500' ?>"><?= html_escape($m['title']) ?></div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php if (!empty($special)): ?>
<section class="bg-white border-y border-stonewarm-200">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-8">
      <div class="heritage-kicker text-gold-600">Mark your calendar</div>
      <h2 class="text-3xl font-bold text-parish-900 mt-2">Special &amp; Upcoming Schedules</h2>
    </div>
    <div class="grid gap-3">
      <?php foreach ($special as $s): ?>
      <div class="grid sm:grid-cols-[90px_1fr_auto] gap-4 items-center rounded-2xl border border-stonewarm-200 bg-stonewarm-50/30 p-5">
        <div class="w-16 h-16 rounded-2xl bg-parish-800 text-white flex flex-col items-center justify-center">
          <div class="text-[10px] uppercase tracking-wider text-gold-200"><?= date('M', strtotime($s['specific_date'])) ?></div>
          <div class="text-xl font-bold"><?= date('d', strtotime($s['specific_date'])) ?></div>
        </div>
        <div>
          <div class="text-[11px] font-semibold uppercase tracking-wider text-gold-600"><?= ucfirst(str_replace('_',' ',$s['schedule_type'])) ?></div>
          <div class="font-bold text-gray-900 mt-1"><?= html_escape($s['title']) ?></div>
        </div>
        <div class="sm:text-right">
          <div class="font-bold text-parish-800"><?= date('g:i A', strtotime($s['mass_time'])) ?></div>
          <div class="text-xs text-gray-500 mt-1"><?= format_date($s['specific_date']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="rounded-[2rem] bg-parish-50 border border-parish-100 p-7 sm:p-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
      <div class="heritage-kicker text-gold-600">Visiting for the first time?</div>
      <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-2">We look forward to welcoming you.</h2>
      <p class="text-gray-600 mt-2 max-w-2xl">See parish location and contact information, or check announcements for schedule changes during fiestas, Holy Week and special celebrations.</p>
    </div>
    <div class="flex flex-wrap gap-2 flex-shrink-0">
      <a href="<?= site_url('contact') ?>" class="px-5 py-3 rounded-full bg-parish-800 text-white font-semibold hover:bg-parish-900 transition">Plan Your Visit</a>
      <a href="<?= site_url('announcements') ?>" class="px-5 py-3 rounded-full bg-white border border-parish-200 text-parish-800 font-semibold hover:bg-parish-50 transition">Announcements</a>
    </div>
  </div>
</section>
