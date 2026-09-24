<?php
$is_parishioner_account = !empty($current_user) && (int)$current_user['role_id'] === (int)ROLE_PARISHIONER;
$is_restricted_account = !empty($current_user) && !$is_parishioner_account;

$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Santa_Monica_Church_Alburquerque_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
$interior_img = 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Santa_Monica_Church_Alburquerque_inside_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
$convent_img = 'https://upload.wikimedia.org/wikipedia/commons/c/c8/Santa_Monica_Church_Alburquerque_with_convent_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';

$seasonal_theme = [
    'lent'         => ['#3c2447', '#c9add7', 'Journey with Christ'],
    'christmas'    => ['#123f32', '#e7bf59', 'Christmas at Sta. Monica'],
    'rosary_month' => ['#234d74', '#cedcec', 'Month of the Holy Rosary'],
    'undas'        => ['#292929', '#d5b57d', 'Remembering the Faithful Departed'],
    'new_year'     => ['#1d304c', '#dcc47c', 'Begin the Year in Prayer'],
    'fiesta'       => ['#772f25', '#e5ba52', 'Our Patronal Celebration'],
];
$season_style = !empty($seasonal_event) ? ($seasonal_theme[$seasonal_event['season_key'] ?? ''] ?? ['#1a3c28', '#e7bf59', 'Seasonal Celebration']) : null;
$season_cover = !empty($seasonal_event['cover_image']) ? base_url($seasonal_event['cover_image']) : $hero_img;
?>

<!-- Destination-style hero -->
<section class="relative min-h-[720px] lg:min-h-[760px] flex items-end overflow-hidden bg-parish-900">
  <img src="<?= $hero_img ?>" alt="Santa Monica Parish Church in Alburquerque, Bohol" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
  <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/45 to-black/15"></div>
  <div class="absolute inset-0 bg-gradient-to-t from-parish-900/90 via-transparent to-parish-900/10"></div>

  <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20 lg:pb-24 pt-36">
    <div class="max-w-3xl">
      <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-semibold tracking-wide mb-6">
        <i class="ph-fill ph-map-pin text-gold-300"></i>
        ALBURQUERQUE, BOHOL · PHILIPPINES
      </div>
      <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold text-white leading-[.98] tracking-[-.04em]">
        Faith, heritage and community
        <span class="block text-gold-200 mt-2">since 1842.</span>
      </h1>
      <p class="mt-6 text-lg sm:text-xl text-white/80 leading-relaxed max-w-2xl">
        Discover Sta. Monica Parish Church — a living place of worship and one of Alburquerque's enduring heritage landmarks.
      </p>
      <div class="mt-9 flex flex-wrap gap-3">
        <a href="<?= site_url('mass-schedule') ?>" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-white text-parish-900 font-semibold shadow-lg hover:bg-gold-50 transition">
          <i class="ph ph-clock"></i> View Mass Schedule
        </a>
        <a href="<?= site_url('about') ?>" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-white/10 backdrop-blur border border-white/30 text-white font-semibold hover:bg-white/20 transition">
          Explore Our Heritage <i class="ph ph-arrow-right"></i>
        </a>
        <a href="<?= site_url('sacraments') ?>" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-gold-500/95 text-white font-semibold hover:bg-gold-600 transition">
          Online Parish Services
        </a>
      </div>
    </div>

    <div class="mt-12 grid sm:grid-cols-3 gap-3 max-w-3xl">
      <div class="rounded-2xl bg-black/20 backdrop-blur-md border border-white/15 px-5 py-4 text-white">
        <div class="text-2xl font-bold">1842</div>
        <div class="text-xs text-white/65 mt-1">Parish roots began as a visita of Baclayon</div>
      </div>
      <div class="rounded-2xl bg-black/20 backdrop-blur-md border border-white/15 px-5 py-4 text-white">
        <div class="text-2xl font-bold">1869</div>
        <div class="text-xs text-white/65 mt-1">Formally inaugurated as a parish</div>
      </div>
      <div class="rounded-2xl bg-black/20 backdrop-blur-md border border-white/15 px-5 py-4 text-white">
        <div class="text-2xl font-bold">2013</div>
        <div class="text-xs text-white/65 mt-1">Recognized as an Important Cultural Property</div>
      </div>
    </div>
  </div>
</section>

<!-- Live parish information -->
<section class="relative z-10 -mt-8 sm:-mt-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  <div class="grid lg:grid-cols-[1.25fr_.75fr] gap-4">
    <div class="heritage-card rounded-3xl p-6 sm:p-7">
      <div class="flex items-center justify-between gap-4 mb-5">
        <div>
          <div class="heritage-kicker text-gold-600">Worship today</div>
          <h2 class="text-2xl font-bold text-parish-900 mt-1">Today's Masses</h2>
        </div>
        <a href="<?= site_url('mass-schedule') ?>" class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-parish-700 hover:text-parish-900">
          Full schedule <i class="ph ph-arrow-right"></i>
        </a>
      </div>
      <?php if (!empty($today_masses)): ?>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <?php foreach ($today_masses as $m): ?>
        <div class="rounded-2xl bg-parish-50 border border-parish-100 px-4 py-4">
          <div class="text-xl font-bold text-parish-800"><?= date('g:i A', strtotime($m['mass_time'])) ?></div>
          <div class="text-xs text-gray-500 mt-1"><?= html_escape($m['title']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="rounded-2xl bg-stonewarm-50 border border-stonewarm-200 px-5 py-4 text-sm text-gray-500">No Mass schedule has been published for today yet.</div>
      <?php endif; ?>
    </div>

    <div class="rounded-3xl bg-parish-900 text-white p-6 sm:p-7 shadow-heritage">
      <div class="heritage-kicker text-gold-300">Next celebration</div>
      <?php if ($next_mass): ?>
        <div class="mt-3 text-4xl font-bold"><?= date('g:i A', strtotime($next_mass['mass_time'])) ?></div>
        <div class="text-white/70 mt-1"><?= html_escape($next_mass['title']) ?></div>
        <a href="<?= site_url('mass-schedule') ?>" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-gold-200 hover:text-white">
          See complete schedule <i class="ph ph-arrow-right"></i>
        </a>
      <?php else: ?>
        <div class="mt-3 text-xl font-semibold">Schedule coming soon</div>
        <div class="text-sm text-white/60 mt-2">Check the full Mass schedule for published services.</div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if (!empty($seasonal_event)): ?>
<!-- Seasonal homepage feature -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 sm:pt-12">
  <a href="<?= site_url('events/' . $seasonal_event['slug']) ?>" class="group block relative overflow-hidden rounded-[2rem] min-h-[420px] sm:min-h-[460px] shadow-heritage">
    <img src="<?= html_escape($season_cover) ?>" alt="<?= html_escape($seasonal_event['title']) ?>" class="absolute inset-0 w-full h-full object-cover transition duration-700 group-hover:scale-[1.025]">
    <div class="absolute inset-0" style="background:linear-gradient(90deg, <?= $season_style[0] ?>f5 0%, <?= $season_style[0] ?>dd 44%, <?= $season_style[0] ?>55 76%, transparent 100%);"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-transparent to-black/10"></div>

    <div class="relative min-h-[420px] sm:min-h-[460px] flex items-end">
      <div class="w-full max-w-3xl p-7 sm:p-10 lg:p-12 text-white">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/15 text-[11px] uppercase tracking-[.16em] font-bold" style="color:<?= $season_style[1] ?>">
          <i class="ph ph-star"></i> <?= html_escape($season_style[2]) ?>
        </div>

        <h2 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-[-.035em] leading-[1.02] mt-5">
          <?= html_escape($seasonal_event['title']) ?>
        </h2>

        <?php if (!empty($seasonal_event['description'])): ?>
          <p class="mt-5 text-white/78 text-base sm:text-lg leading-relaxed max-w-2xl">
            <?= html_escape(mb_strimwidth(strip_tags($seasonal_event['description']), 0, 220, '…')) ?>
          </p>
        <?php endif; ?>

        <div class="mt-7 flex flex-wrap items-center gap-3 text-sm">
          <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-black/20 backdrop-blur border border-white/10">
            <i class="ph ph-calendar-blank" style="color:<?= $season_style[1] ?>"></i>
            <?= format_date($seasonal_event['event_date']) ?>
            <?php if (!empty($seasonal_event['end_date']) && $seasonal_event['end_date'] !== $seasonal_event['event_date']): ?>
              – <?= format_date($seasonal_event['end_date']) ?>
            <?php endif; ?>
          </span>

          <?php if (!empty($seasonal_event_duration) && $seasonal_event_duration > 1): ?>
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-black/20 backdrop-blur border border-white/10">
              <i class="ph ph-clock" style="color:<?= $season_style[1] ?>"></i>
              <?= (int)$seasonal_event_duration ?>-day parish season
            </span>
          <?php endif; ?>
        </div>

        <div class="mt-8 inline-flex items-center gap-2 px-5 py-3 rounded-full bg-white text-gray-900 font-semibold shadow-lg group-hover:bg-gold-50 transition">
          View schedules & details <i class="ph ph-arrow-right"></i>
        </div>
      </div>
    </div>
  </a>
</section>
<?php endif; ?>

<!-- Heritage story -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-24">
  <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
    <div class="relative">
      <div class="heritage-photo rounded-[2rem] shadow-heritage aspect-[4/3]">
        <img src="<?= $convent_img ?>" alt="Santa Monica Parish Church and convent in Alburquerque, Bohol" class="w-full h-full object-cover" loading="lazy">
      </div>
      <div class="absolute -bottom-5 -right-2 sm:right-7 rounded-2xl bg-white shadow-soft border border-stonewarm-200 px-5 py-4 max-w-[220px]">
        <div class="flex items-center gap-2 text-gold-600 font-semibold text-xs uppercase tracking-wider"><i class="ph-fill ph-medal"></i> Heritage landmark</div>
        <div class="text-sm text-gray-600 mt-1.5">Coral-stone architecture, historic convento and a distinctive arcade.</div>
      </div>
    </div>
    <div>
      <div class="heritage-kicker text-gold-600">A story written in stone</div>
      <div class="heritage-rule mt-3"></div>
      <h2 class="public-section-title text-parish-900 mt-5">More than a church. A living chapter of Bohol's history.</h2>
      <p class="mt-6 text-gray-600 leading-relaxed text-lg">
        Sta. Monica Parish traces its beginnings to 1842, when a chapel, convento and school were established while the community was still connected to Baclayon. The parish was formally inaugurated in 1869, while the present stone church took shape in the late nineteenth century.
      </p>
      <p class="mt-4 text-gray-600 leading-relaxed">
        Today, the church continues to welcome parishioners, families, pilgrims and visitors while preserving the faith and heritage that have shaped generations of Alburquerqueños.
      </p>
      <div class="mt-8 flex flex-wrap gap-3">
        <a href="<?= site_url('about') ?>" class="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-parish-800 text-white font-semibold hover:bg-parish-900 transition">Read the Church Story <i class="ph ph-arrow-right"></i></a>
        <a href="<?= site_url('contact') ?>" class="inline-flex items-center gap-2 px-5 py-3 rounded-full border border-parish-200 text-parish-800 font-semibold hover:bg-parish-50 transition"><i class="ph ph-map-pin"></i> Plan Your Visit</a>
      </div>
    </div>
  </div>
</section>

<!-- Parish priests -->
<section class="bg-parish-50/70 border-y border-parish-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5 mb-9">
      <div>
        <div class="heritage-kicker text-gold-600">Parish leadership</div>
        <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-2">Meet our parish priests.</h2>
        <p class="text-gray-600 mt-2 max-w-2xl">
          Get to know the clergy serving Sta. Monica Parish through worship, sacramental ministry, pastoral care and community leadership.
        </p>
      </div>
      <a href="<?= site_url('priests') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-parish-700 hover:text-parish-900">
        View all priests <i class="ph ph-arrow-right"></i>
      </a>
    </div>

    <?php if (!empty($priests)): ?>
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($priests as $priest): ?>
          <?php
            $photo = !empty($priest['avatar'])
              ? (preg_match('/^https?:\/\//i', $priest['avatar']) ? $priest['avatar'] : base_url(ltrim($priest['avatar'], '/')))
              : null;
            $full_name = trim(($priest['title'] ?: 'Rev. Fr.') . ' ' . $priest['first_name'] . ' ' . $priest['last_name']);
          ?>
          <a href="<?= site_url('priests') ?>" class="group flex items-center gap-4 rounded-3xl bg-white border border-stonewarm-200 p-5 hover:border-parish-200 hover:shadow-soft transition">
            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-parish-800 text-white flex items-center justify-center flex-shrink-0">
              <?php if ($photo): ?>
                <img src="<?= html_escape($photo) ?>" alt="<?= html_escape($full_name) ?>" class="w-full h-full object-cover">
              <?php else: ?>
                <i class="ph ph-church text-3xl"></i>
              <?php endif; ?>
            </div>
            <div class="min-w-0">
              <div class="text-[10px] font-bold uppercase tracking-wider text-gold-600"><?= html_escape($priest['position'] ?: 'Priest') ?></div>
              <h3 class="font-bold text-parish-900 mt-1 group-hover:text-parish-700 transition"><?= html_escape($full_name) ?></h3>
              <?php if (!empty($priest['bio'])): ?>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2"><?= html_escape($priest['bio']) ?></p>
              <?php else: ?>
                <p class="text-xs text-gray-500 mt-1">Serving the Sta. Monica Parish community.</p>
              <?php endif; ?>
            </div>
            <i class="ph ph-arrow-up-right text-gray-300 group-hover:text-parish-700 ml-auto"></i>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <a href="<?= site_url('priests') ?>" class="flex items-center justify-between gap-4 rounded-3xl bg-white border border-stonewarm-200 p-6 hover:border-parish-200 transition">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 rounded-2xl bg-parish-50 text-parish-700 flex items-center justify-center text-2xl"><i class="ph ph-church"></i></div>
          <div>
            <div class="font-bold text-parish-900">Parish priest profiles</div>
            <div class="text-sm text-gray-500 mt-1">Clergy information will appear here once published by the parish office.</div>
          </div>
        </div>
        <i class="ph ph-arrow-right text-parish-700"></i>
      </a>
    <?php endif; ?>
  </div>
</section>

<!-- Patronal devotion -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
  <div class="grid lg:grid-cols-2 gap-4">
    <a href="<?= site_url('st-monica') ?>" class="group block rounded-[2rem] overflow-hidden bg-gradient-to-r from-parish-900 via-parish-800 to-parish-700 text-white shadow-heritage">
      <div class="p-7 sm:p-9 h-full flex flex-col justify-between gap-8">
        <div class="flex items-start gap-5">
          <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/10 text-gold-300 flex items-center justify-center text-3xl flex-shrink-0">
            <i class="ph ph-book-open-text"></i>
          </div>
          <div>
            <div class="heritage-kicker text-gold-300">Our patroness</div>
            <h2 class="text-2xl sm:text-3xl font-bold mt-2">The Life of St. Monica</h2>
            <p class="text-white/65 mt-2 leading-relaxed">Discover her story of family, patient love, perseverance, and faithful prayer — in English or Bisaya / Cebuano.</p>
          </div>
        </div>
        <div class="inline-flex items-center gap-2 font-semibold text-gold-200 group-hover:text-white transition">
          Read Her Story <i class="ph ph-arrow-right group-hover:translate-x-0.5 transition-transform"></i>
        </div>
      </div>
    </a>

    <a href="<?= site_url('prayers') ?>" class="group block rounded-[2rem] overflow-hidden bg-gold-50 border border-gold-100 text-parish-900 shadow-soft">
      <div class="p-7 sm:p-9 h-full flex flex-col justify-between gap-8">
        <div class="flex items-start gap-5">
          <div class="w-14 h-14 rounded-2xl bg-white text-gold-600 flex items-center justify-center text-3xl flex-shrink-0 shadow-sm">
            <i class="ph ph-hands-praying"></i>
          </div>
          <div>
            <div class="heritage-kicker text-gold-700">Devotion to our patroness</div>
            <h2 class="text-2xl sm:text-3xl font-bold mt-2">Prayers &amp; Novena to St. Monica</h2>
            <p class="text-gray-600 mt-2 leading-relaxed">Pray the nine-day novena and family prayers in English or Bisaya / Cebuano.</p>
          </div>
        </div>
        <div class="inline-flex items-center gap-2 font-semibold text-parish-700 group-hover:text-parish-900 transition">
          Open Prayer Guide <i class="ph ph-arrow-right group-hover:translate-x-0.5 transition-transform"></i>
        </div>
      </div>
    </a>
  </div>
</section>

<!-- Quick parish services -->
<section class="bg-white border-y border-stonewarm-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5 mb-9">
      <div>
        <div class="heritage-kicker text-gold-600">Parish services</div>
        <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 tracking-tight mt-2">Faith services, made easier to access.</h2>
      </div>
      <a href="<?= site_url('sacraments') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-parish-700 hover:text-parish-900">View all services <i class="ph ph-arrow-right"></i></a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php
        $icons = ['baptism' => 'ph-drop', 'wedding' => 'ph-heart', 'funeral' => 'ph-cross', 'confirmation' => 'ph-sparkle', 'house_blessing' => 'ph-house-line', 'vehicle_blessing' => 'ph-car'];
        $shown = 0;
        foreach ($service_types as $s):
          if ($shown >= 7) break;
          $icon = $icons[$s['service_key']] ?? 'ph-hand-heart';
          $shown++;
      ?>
      <?php if ($is_restricted_account): ?>
        <div class="rounded-2xl border border-stonewarm-200 bg-stonewarm-50/40 p-5 opacity-75 cursor-not-allowed" title="Online booking requires a Parishioner account">
          <div class="w-11 h-11 rounded-xl bg-white text-parish-700 flex items-center justify-center text-2xl shadow-sm">
            <i class="ph <?= $icon ?>"></i>
          </div>
          <div class="font-semibold text-gray-800 mt-4"><?= html_escape($s['name']) ?></div>
          <div class="text-xs text-gray-500 mt-1 flex items-center gap-1.5"><i class="ph ph-lock-key"></i> Parishioner account required</div>
        </div>
      <?php else: ?>
        <a href="<?= site_url('my/bookings/new/' . $s['service_key']) ?>" class="group rounded-2xl border border-stonewarm-200 bg-stonewarm-50/40 p-5 hover:bg-parish-800 hover:border-parish-800 transition duration-300">
          <div class="w-11 h-11 rounded-xl bg-white text-parish-700 flex items-center justify-center text-2xl shadow-sm group-hover:text-gold-600 transition">
            <i class="ph <?= $icon ?>"></i>
          </div>
          <div class="font-semibold text-gray-800 mt-4 group-hover:text-white"><?= html_escape($s['name']) ?></div>
          <div class="text-xs text-gray-500 mt-1 group-hover:text-white/65"><?= $is_parishioner_account ? 'View details & apply online' : 'Log in & apply online' ?></div>
        </a>
      <?php endif; ?>
      <?php endforeach; ?>
      <?php if ($is_restricted_account): ?>
        <div class="rounded-2xl border border-gold-200 bg-gold-50 p-5 opacity-75 cursor-not-allowed" title="Certificate requests require a Parishioner account">
          <div class="w-11 h-11 rounded-xl bg-white text-gold-600 flex items-center justify-center text-2xl shadow-sm"><i class="ph ph-scroll"></i></div>
          <div class="font-semibold text-gray-800 mt-4">Request Certificate</div>
          <div class="text-xs text-gray-500 mt-1 flex items-center gap-1.5"><i class="ph ph-lock-key"></i> Parishioner account required</div>
        </div>
      <?php else: ?>
        <a href="<?= site_url('my/certificates/new') ?>" class="group rounded-2xl border border-gold-200 bg-gold-50 p-5 hover:bg-gold-500 hover:border-gold-500 transition duration-300">
          <div class="w-11 h-11 rounded-xl bg-white text-gold-600 flex items-center justify-center text-2xl shadow-sm"><i class="ph ph-scroll"></i></div>
          <div class="font-semibold text-gray-800 mt-4 group-hover:text-white">Request Certificate</div>
          <div class="text-xs text-gray-500 mt-1 group-hover:text-white/75"><?= $is_parishioner_account ? 'Request sacramental records online' : 'Log in to request a certificate' ?></div>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Announcements and events -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-24">
  <div class="grid lg:grid-cols-2 gap-12">
    <div>
      <div class="flex items-end justify-between mb-6">
        <div>
          <div class="heritage-kicker text-gold-600">Stay informed</div>
          <h2 class="text-3xl font-bold text-parish-900 mt-1">Parish Announcements</h2>
        </div>
        <a href="<?= site_url('announcements') ?>" class="text-sm font-semibold text-parish-700 hover:text-parish-900">See all →</a>
      </div>
      <div class="space-y-3">
        <?php if (empty($announcements)): ?>
          <div class="rounded-2xl bg-white border border-stonewarm-200 p-6 text-gray-500">No announcements posted yet.</div>
        <?php endif; ?>
        <?php foreach ($announcements as $a): ?>
        <a href="<?= site_url('announcements/' . $a['slug']) ?>" class="group flex gap-4 items-start rounded-2xl bg-white border border-stonewarm-200 p-5 hover:border-parish-200 hover:shadow-soft transition">
          <div class="w-11 h-11 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center flex-shrink-0"><i class="ph <?= $a['is_pinned'] ? 'ph-fill ph-push-pin' : 'ph-megaphone' ?> text-xl"></i></div>
          <div class="min-w-0 flex-1">
            <div class="text-[11px] text-gold-600 font-semibold uppercase tracking-wider"><?= ucfirst(str_replace('_',' ',$a['category'])) ?> · <?= format_date($a['publish_date']) ?></div>
            <div class="font-semibold text-gray-900 mt-1 group-hover:text-parish-800 transition"><?= html_escape($a['title']) ?></div>
          </div>
          <i class="ph ph-arrow-up-right text-gray-300 group-hover:text-parish-700 mt-1"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <div class="flex items-end justify-between mb-6">
        <div>
          <div class="heritage-kicker text-gold-600">Gather with us</div>
          <h2 class="text-3xl font-bold text-parish-900 mt-1">Upcoming Activities</h2>
        </div>
        <a href="<?= site_url('events') ?>" class="text-sm font-semibold text-parish-700 hover:text-parish-900">See all →</a>
      </div>
      <div class="space-y-3">
        <?php if (empty($events)): ?>
          <div class="rounded-2xl bg-white border border-stonewarm-200 p-6 text-gray-500">No upcoming events posted yet.</div>
        <?php endif; ?>
        <?php foreach ($events as $e): ?>
        <a href="<?= site_url('events/' . $e['slug']) ?>" class="group flex gap-4 items-center rounded-2xl bg-white border border-stonewarm-200 p-4 hover:border-parish-200 hover:shadow-soft transition">
          <div class="w-16 h-16 rounded-2xl bg-parish-800 text-white flex flex-col items-center justify-center flex-shrink-0">
            <div class="text-[10px] uppercase tracking-wider text-gold-200"><?= date('M', strtotime($e['event_date'])) ?></div>
            <div class="text-2xl font-bold leading-none mt-0.5"><?= date('d', strtotime($e['event_date'])) ?></div>
          </div>
          <div class="min-w-0 flex-1">
            <div class="font-semibold text-gray-900 group-hover:text-parish-800"><?= html_escape($e['title']) ?></div>
            <div class="text-xs text-gray-500 mt-1 flex items-center gap-1.5"><i class="ph ph-map-pin"></i><?= html_escape($e['location'] ?: 'Parish grounds') ?></div>
          </div>
          <i class="ph ph-arrow-up-right text-gray-300 group-hover:text-parish-700"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Interior visual / visitor invitation -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20 sm:pb-24">
  <div class="relative rounded-[2rem] overflow-hidden min-h-[520px] shadow-heritage">
    <img src="<?= $interior_img ?>" alt="Interior of Santa Monica Parish Church in Alburquerque, Bohol" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
    <div class="absolute inset-0 bg-gradient-to-r from-parish-900/90 via-parish-900/55 to-transparent"></div>
    <div class="relative max-w-2xl px-7 sm:px-12 py-14 sm:py-20 text-white">
      <div class="heritage-kicker text-gold-200">Come and experience the parish</div>
      <h2 class="text-4xl sm:text-5xl font-bold tracking-tight leading-tight mt-4">A sacred space shaped by generations of faith.</h2>
      <p class="mt-5 text-white/75 text-lg leading-relaxed">Whether you are joining us for Mass, preparing for a sacrament, tracing family records, or visiting Alburquerque's heritage sites, you are welcome here.</p>
      <div class="mt-8 flex flex-wrap gap-3">
        <a href="<?= site_url('contact') ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white text-parish-900 font-semibold hover:bg-gold-50 transition"><i class="ph ph-compass"></i> Plan Your Visit</a>
        <a href="<?= site_url('ministries') ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-white/30 bg-white/10 backdrop-blur text-white font-semibold hover:bg-white/20 transition">Meet Our Community</a>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($chapels)): ?>
<!-- Chapel communities -->
<section class="bg-stonewarm-50 border-y border-stonewarm-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-7">
      <div>
        <div class="heritage-kicker text-gold-600">GSK communities</div>
        <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-2">Faith lived in our chapel communities.</h2>
        <p class="text-gray-600 mt-2 max-w-2xl">Find chapel details, regular Mass schedules and the GSK communities that connect parish life with neighborhoods and families.</p>
      </div>
      <a href="<?= site_url('chapels') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-parish-700 hover:underline">Explore all chapels <i class="ph ph-arrow-right"></i></a>
    </div>

    <div class="grid md:grid-cols-3 gap-5">
      <?php foreach ($chapels as $chapel):
        $chapel_img = !empty($chapel['cover_image']) ? base_url($chapel['cover_image']) : $hero_img;
      ?>
      <a href="<?= site_url('chapels/'.$chapel['slug']) ?>" class="group rounded-3xl overflow-hidden bg-white border border-stonewarm-200 hover:border-parish-200 hover:shadow-heritage transition">
        <div class="relative h-44 overflow-hidden bg-parish-900">
          <img src="<?= html_escape($chapel_img) ?>" alt="<?= html_escape($chapel['name']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-700">
          <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-transparent"></div>
          <div class="absolute left-4 right-4 bottom-4 text-white">
            <?php if ($chapel['patron_saint']): ?><div class="text-[10px] uppercase tracking-wider font-bold text-gold-200"><?= html_escape($chapel['patron_saint']) ?></div><?php endif; ?>
            <div class="font-bold text-lg mt-1"><?= html_escape($chapel['name']) ?></div>
          </div>
        </div>
        <div class="p-5 flex items-center justify-between text-sm">
          <span class="text-gray-500"><i class="ph ph-map-pin mr-1 text-parish-600"></i><?= html_escape($chapel['barangay'] ?: 'Alburquerque, Bohol') ?></span>
          <i class="ph ph-arrow-right text-parish-700"></i>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($projects)): ?>
<!-- Parish Projects -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-7">
    <div>
      <div class="heritage-kicker text-gold-600">Building together</div>
      <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-2">Parish projects you can follow and support.</h2>
      <p class="text-gray-600 mt-2 max-w-2xl">See current improvements, outreach initiatives and community projects, with verified donation progress shown transparently.</p>
    </div>
    <a href="<?= site_url('projects') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-parish-700 hover:underline">View all projects <i class="ph ph-arrow-right"></i></a>
  </div>

  <div class="grid md:grid-cols-3 gap-5">
    <?php foreach ($projects as $project):
      $project_img = !empty($project['cover_image']) ? base_url($project['cover_image']) : $hero_img;
    ?>
      <a href="<?= site_url('projects/' . $project['slug']) ?>" class="group rounded-3xl overflow-hidden bg-white border border-stonewarm-200 hover:border-parish-200 hover:shadow-heritage transition duration-300">
        <div class="relative h-44 overflow-hidden bg-parish-900">
          <img src="<?= html_escape($project_img) ?>" alt="<?= html_escape($project['title']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-700">
          <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-transparent"></div>
          <?php if (!empty($project['category'])): ?><div class="absolute bottom-4 left-4 text-[10px] uppercase tracking-wider font-bold text-gold-200"><?= html_escape($project['category']) ?></div><?php endif; ?>
        </div>
        <div class="p-5">
          <h3 class="font-bold text-lg text-parish-900"><?= html_escape($project['title']) ?></h3>
          <p class="text-sm text-gray-500 mt-2 line-clamp-2"><?= html_escape($project['short_description'] ?: mb_strimwidth(strip_tags($project['description']),0,110,'…')) ?></p>
          <?php if ((float)$project['goal_amount'] > 0): ?>
            <div class="mt-4">
              <div class="flex justify-between text-[11px]"><span class="font-semibold text-gray-700"><?= peso($project['raised_amount']) ?> raised</span><span class="text-gray-400"><?= number_format((float)$project['progress_percent'],1) ?>%</span></div>
              <div class="h-2 rounded-full bg-gray-100 overflow-hidden mt-2"><div class="h-full bg-parish-600 rounded-full" style="width:<?= min(100,(float)$project['progress_percent']) ?>%"></div></div>
            </div>
          <?php endif; ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Support -->
<section class="bg-parish-900 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-7">
    <div class="max-w-2xl">
      <div class="heritage-kicker text-gold-300">Stewardship</div>
      <h2 class="text-3xl font-bold mt-2">Help preserve our parish and support its mission.</h2>
      <p class="text-white/65 mt-3 leading-relaxed">Your generosity supports parish ministries, community outreach, church care and the continuing life of this historic place of worship.</p>
    </div>
    <a href="<?= site_url('donate') ?>" class="flex-shrink-0 inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-gold-500 hover:bg-gold-600 text-white font-semibold shadow-lg transition"><i class="ph ph-heart"></i> Support the Parish</a>
  </div>
</section>
