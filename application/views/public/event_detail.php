<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Santa_Monica_Church_Alburquerque_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
$event_cover = !empty($item['cover_image']) ? base_url($item['cover_image']) : $hero_img;
$event_end = !empty($item['end_date']) ? $item['end_date'] : $item['event_date'];
$duration_days = max(1, (int)((strtotime($event_end) - strtotime($item['event_date'])) / 86400) + 1);

$activity_type_meta = [
    'mass'       => ['Mass', 'ph-church'],
    'novena'     => ['Novena', 'ph-hands-praying'],
    'devotion'   => ['Devotion', 'ph-hands-praying'],
    'prayer'     => ['Prayer', 'ph-hands-praying'],
    'liturgy'    => ['Liturgy', 'ph-cross'],
    'confession' => ['Confession', 'ph-cross'],
    'procession' => ['Procession', 'ph-path'],
    'fellowship' => ['Fellowship', 'ph-users-three'],
    'program'    => ['Program', 'ph-microphone-stage'],
    'music'      => ['Music', 'ph-music-notes'],
    'outreach'   => ['Outreach', 'ph-hand-heart'],
    'meeting'    => ['Meeting', 'ph-users'],
    'activity'   => ['Activity', 'ph-calendar-check'],
];
?>
<article>
  <section class="relative overflow-hidden bg-parish-900 min-h-[340px] flex items-end">
    <img src="<?= html_escape($event_cover) ?>" alt="<?= html_escape($item['title']) ?>" class="absolute inset-0 w-full h-full object-cover opacity-55">
    <div class="absolute inset-0 bg-gradient-to-r from-parish-900 via-parish-900/85 to-parish-900/40"></div>
    <div class="relative max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-12 sm:py-14 text-white">
      <a href="<?= site_url('events') ?>" class="inline-flex items-center gap-2 text-sm text-white/65 hover:text-white transition"><i class="ph ph-arrow-left"></i> Back to Events</a>
      <div class="mt-6 flex items-center gap-4">
        <div class="w-20 h-20 rounded-2xl bg-white text-parish-900 flex flex-col items-center justify-center shadow-lg flex-shrink-0">
          <div class="text-[10px] font-bold uppercase tracking-wider text-gold-600"><?= date('M', strtotime($item['event_date'])) ?></div>
          <div class="text-3xl font-bold leading-none"><?= date('d', strtotime($item['event_date'])) ?></div>
        </div>
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <?php if ($item['category']): ?><div class="text-[11px] text-gold-200 font-semibold uppercase tracking-wider"><?= html_escape($item['category']) ?></div><?php endif; ?>
            <?php if (!empty($item['is_seasonal'])): ?><span class="px-2 py-0.5 rounded-full bg-white/10 border border-white/15 text-[9px] font-bold uppercase tracking-wider">Seasonal <?= (int)($item['season_year'] ?? date('Y', strtotime($item['event_date']))) ?></span><?php endif; ?>
          </div>
          <h1 class="text-3xl sm:text-5xl font-bold tracking-tight leading-tight mt-1"><?= html_escape($item['title']) ?></h1>
        </div>
      </div>
      <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-sm text-white/65">
        <span class="inline-flex items-center gap-2"><i class="ph ph-calendar-blank"></i><?= format_date($item['event_date']) ?><?php if ($event_end !== $item['event_date']): ?> – <?= format_date($event_end) ?><?php endif; ?><?= $item['event_time'] ? ', ' . date('g:i A', strtotime($item['event_time'])) : '' ?></span>
        <?php if ($duration_days > 1): ?><span class="inline-flex items-center gap-2"><i class="ph ph-clock"></i><?= $duration_days ?> days</span><?php endif; ?>
        <span class="inline-flex items-center gap-2"><i class="ph ph-map-pin"></i><?= html_escape($item['location'] ?: 'TBA') ?></span>
      </div>
    </div>
  </section>

  <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
    <div class="grid lg:grid-cols-[1fr_.42fr] gap-8">
      <div class="rounded-[2rem] bg-white border border-stonewarm-200 p-7 sm:p-10 shadow-soft">
        <div class="heritage-kicker text-gold-600">About this event</div>
        <div class="prose prose-sm sm:prose max-w-none mt-5 text-gray-700 leading-relaxed whitespace-pre-line">
          <?= nl2br(html_escape($item['description'])) ?>
        </div>
      </div>

      <aside class="space-y-4">
        <div class="rounded-3xl bg-parish-50 border border-parish-100 p-6">
          <div class="heritage-kicker text-gold-600">Event details</div>
          <div class="mt-5 space-y-4 text-sm">
            <div class="flex gap-3"><i class="ph ph-calendar-blank text-parish-700 text-lg"></i><div><div class="font-semibold text-gray-800"><?= format_date($item['event_date']) ?><?php if ($event_end !== $item['event_date']): ?> – <?= format_date($event_end) ?><?php endif; ?></div><div class="text-gray-500 mt-0.5"><?= $duration_days > 1 ? $duration_days . '-day duration' : 'One-day event' ?><?= $item['event_time'] ? ' · ' . date('g:i A', strtotime($item['event_time'])) : '' ?></div></div></div>
            <div class="flex gap-3"><i class="ph ph-map-pin text-parish-700 text-lg"></i><div><div class="font-semibold text-gray-800"><?= html_escape($item['location'] ?: 'TBA') ?></div><div class="text-gray-500 mt-0.5">Alburquerque, Bohol</div></div></div>
          </div>
        </div>
      </aside>
    </div>

    <?php if (!empty($activities)): ?>
    <section class="mt-8 rounded-[2rem] bg-white border border-stonewarm-200 p-7 sm:p-10 shadow-soft">
      <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
          <div class="heritage-kicker text-gold-600">Parish program</div>
          <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-2">Masses, activities & community gatherings</h2>
          <p class="text-gray-600 mt-2 max-w-2xl">This schedule is prepared by the parish for this year’s celebration. Times and activities may be updated by the parish office.</p>
        </div>
        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-parish-50 text-parish-700 text-xs font-semibold self-start">
          <i class="ph ph-list-checks"></i> <?= count($activities) ?> program item<?= count($activities) === 1 ? '' : 's' ?>
        </div>
      </div>

      <div class="mt-8 relative">
        <div class="absolute left-[19px] top-4 bottom-4 w-px bg-stonewarm-200 hidden sm:block"></div>

        <div class="space-y-4">
          <?php foreach ($activities as $activity):
            $meta = $activity_type_meta[$activity['activity_type']] ?? [ucwords(str_replace('_',' ', $activity['activity_type'] ?: 'Activity')), 'ph-calendar-check'];
            $a_end = !empty($activity['activity_end_date']) ? $activity['activity_end_date'] : $activity['activity_date'];
            $a_days = max(1, (int)((strtotime($a_end) - strtotime($activity['activity_date'])) / 86400) + 1);
          ?>
          <article class="relative sm:pl-14">
            <div class="hidden sm:flex absolute left-0 top-4 w-10 h-10 rounded-full bg-white border border-stonewarm-200 shadow-sm items-center justify-center text-parish-700 z-10">
              <i class="ph <?= html_escape($meta[1]) ?>"></i>
            </div>

            <div class="rounded-2xl border <?= !empty($activity['is_featured']) ? 'border-gold-200 bg-gold-50/35' : 'border-stonewarm-200 bg-stonewarm-50/35' ?> p-5 sm:p-6">
              <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-stonewarm-200 text-[10px] uppercase tracking-wider font-bold text-parish-700">
                      <i class="ph <?= html_escape($meta[1]) ?>"></i> <?= html_escape($meta[0]) ?>
                    </span>
                    <?php if (!empty($activity['is_featured'])): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gold-100 text-gold-700 text-[10px] uppercase tracking-wider font-bold"><i class="ph ph-star"></i> Featured</span>
                    <?php endif; ?>
                  </div>

                  <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-3"><?= html_escape($activity['title']) ?></h3>

                  <?php if (!empty($activity['description'])): ?>
                    <p class="text-sm text-gray-600 leading-relaxed mt-2 whitespace-pre-line"><?= nl2br(html_escape($activity['description'])) ?></p>
                  <?php endif; ?>
                </div>

                <div class="lg:min-w-[220px] text-sm text-gray-600 space-y-2">
                  <div class="flex gap-2"><i class="ph ph-calendar-blank text-parish-700 mt-0.5"></i><span><strong class="text-gray-800"><?= format_date($activity['activity_date']) ?></strong><?php if ($a_end !== $activity['activity_date']): ?> – <?= format_date($a_end) ?><?php endif; ?><?php if ($a_days > 1): ?><span class="block text-[11px] text-gray-400 mt-0.5"><?= $a_days ?> days</span><?php endif; ?></span></div>

                  <?php if (!empty($activity['start_time'])): ?>
                    <div class="flex gap-2"><i class="ph ph-clock text-parish-700 mt-0.5"></i><span><?= date('g:i A', strtotime($activity['start_time'])) ?><?php if (!empty($activity['end_time'])): ?> – <?= date('g:i A', strtotime($activity['end_time'])) ?><?php endif; ?></span></div>
                  <?php endif; ?>

                  <?php if (!empty($activity['location'])): ?>
                    <div class="flex gap-2"><i class="ph ph-map-pin text-parish-700 mt-0.5"></i><span><?= html_escape($activity['location']) ?></span></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($item['allow_registration']): ?>
    <div class="mt-8 rounded-[2rem] bg-parish-900 text-white p-7 sm:p-9" x-data="{ submitting: false }">
      <div class="grid lg:grid-cols-[.7fr_1.3fr] gap-7 items-center">
        <div>
          <div class="heritage-kicker text-gold-300">Join this activity</div>
          <h2 class="text-2xl font-bold mt-2">Register for this event</h2>
          <p class="text-sm text-white/60 mt-2"><?= $registration_count ?><?= $item['registration_limit'] ? ' / ' . $item['registration_limit'] : '' ?> registered</p>
        </div>
        <form id="event-register-form" class="grid sm:grid-cols-2 gap-3">
          <input type="hidden" name="event_id" value="<?= $item['id'] ?>">
          <input required name="full_name" placeholder="Full Name" class="px-4 py-3 rounded-xl border border-white/15 bg-white/10 text-sm text-white placeholder-white/40 focus:ring-2 focus:ring-gold-300 outline-none">
          <input required name="contact_number" placeholder="Contact Number" class="px-4 py-3 rounded-xl border border-white/15 bg-white/10 text-sm text-white placeholder-white/40 focus:ring-2 focus:ring-gold-300 outline-none">
          <button :disabled="submitting" type="submit" class="sm:col-span-2 px-5 py-3 rounded-xl bg-gold-500 hover:bg-gold-600 text-white text-sm font-semibold transition">Register for Event</button>
        </form>
      </div>
    </div>
    <script>
      $('#event-register-form').on('submit', function(e){
        e.preventDefault();
        $.post('<?= site_url('home/ajax_register_event') ?>', $(this).serialize(), function(res){
          if(res.success){ Swal.fire({icon:'success', title:'Registered!', text: res.message, confirmButtonColor:'#235a38'}); }
          else { toastr.error(res.message); }
        });
      });
    </script>
    <?php endif; ?>
  </section>
</article>
