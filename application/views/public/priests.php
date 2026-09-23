<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Santa_Monica_Church_Alburquerque_inside_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';

$priest_photo = static function ($avatar) {
    if (empty($avatar)) {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $avatar)) {
        return $avatar;
    }

    return base_url(ltrim($avatar, '/'));
};
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[380px] flex items-end">
  <img src="<?= $hero_img ?>" alt="Interior of Sta. Monica Parish Church" class="absolute inset-0 w-full h-full object-cover object-center">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900/95 via-parish-900/75 to-black/20"></div>

  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Parish leadership</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Our Parish Priests</h1>
    <p class="text-white/70 mt-3 max-w-2xl text-lg">
      Meet the priests who serve Sta. Monica Parish Church through worship, sacramental ministry, pastoral care and community leadership.
    </p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5 mb-10">
    <div>
      <div class="heritage-kicker text-gold-600">Serving the community</div>
      <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-2">Clergy of Sta. Monica Parish</h2>
      <p class="text-gray-500 mt-2 max-w-2xl">
        Public priest profiles are maintained by the parish office and are displayed here for parishioners, visitors and families seeking pastoral assistance.
      </p>
    </div>
    <a href="<?= site_url('contact') ?>" class="inline-flex items-center gap-2 px-5 py-3 rounded-full border border-parish-200 bg-white text-parish-800 font-semibold hover:bg-parish-50 transition self-start">
      <i class="ph ph-chat-circle"></i> Contact Parish Office
    </a>
  </div>

  <?php if (empty($priests)): ?>
    <div class="rounded-[2rem] bg-white border border-stonewarm-200 p-10 sm:p-14 text-center shadow-soft">
      <div class="w-20 h-20 mx-auto rounded-3xl bg-parish-50 text-parish-700 flex items-center justify-center text-4xl">
        <i class="ph ph-church"></i>
      </div>
      <h3 class="text-2xl font-bold text-parish-900 mt-5">Priest profiles are being prepared</h3>
      <p class="text-gray-500 mt-2 max-w-xl mx-auto">
        The parish office can publish priest information from the Accounts section once the clergy profiles are ready.
      </p>
    </div>
  <?php else: ?>
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
      <?php foreach ($priests as $priest): ?>
        <?php
          $photo = $priest_photo($priest['avatar'] ?? null);
          $full_name = trim(($priest['title'] ?: 'Rev. Fr.') . ' ' . $priest['first_name'] . ' ' . $priest['last_name']);
          $initials_text = strtoupper(substr($priest['first_name'], 0, 1) . substr($priest['last_name'], 0, 1));
        ?>
        <article class="group rounded-[2rem] bg-white border border-stonewarm-200 overflow-hidden hover:border-parish-200 hover:shadow-heritage transition duration-300">
          <div class="relative aspect-[4/3] bg-gradient-to-br from-parish-800 to-parish-900 overflow-hidden">
            <?php if ($photo): ?>
              <img src="<?= html_escape($photo) ?>" alt="<?= html_escape($full_name) ?>" class="w-full h-full object-cover group-hover:scale-[1.025] transition duration-700">
              <div class="absolute inset-0 bg-gradient-to-t from-parish-900/75 via-transparent to-transparent"></div>
            <?php else: ?>
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-28 h-28 rounded-full bg-white/10 border border-white/15 text-white flex items-center justify-center text-4xl font-bold">
                  <?= html_escape($initials_text) ?>
                </div>
              </div>
              <div class="absolute inset-0 opacity-20 flex items-center justify-center">
                <i class="ph ph-church text-[14rem] text-white translate-x-24 translate-y-16"></i>
              </div>
            <?php endif; ?>

            <div class="absolute left-5 right-5 bottom-5">
              <span class="inline-flex items-center gap-1.5 rounded-full bg-gold-400 text-parish-900 px-3 py-1 text-[10px] font-bold uppercase tracking-wider shadow-sm">
                <i class="ph-fill ph-cross"></i>
                <?= html_escape($priest['position'] ?: 'Priest') ?>
              </span>
            </div>
          </div>

          <div class="p-6 sm:p-7">
            <h3 class="text-xl font-bold text-parish-900"><?= html_escape($full_name) ?></h3>

            <?php if (!empty($priest['bio'])): ?>
              <p class="text-sm text-gray-600 leading-relaxed mt-3">
                <?= nl2br(html_escape($priest['bio'])) ?>
              </p>
            <?php else: ?>
              <p class="text-sm text-gray-500 leading-relaxed mt-3">
                Serving the parish community through the celebration of the sacraments, pastoral care and parish ministry.
              </p>
            <?php endif; ?>

            <div class="mt-5 pt-5 border-t border-stonewarm-200 space-y-3 text-sm">
              <?php if (!empty($priest['email'])): ?>
                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center flex-shrink-0">
                    <i class="ph ph-envelope-simple"></i>
                  </div>
                  <div class="min-w-0">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</div>
                    <a href="mailto:<?= html_escape($priest['email']) ?>" class="text-parish-700 hover:text-parish-900 break-all"><?= html_escape($priest['email']) ?></a>
                  </div>
                </div>
              <?php endif; ?>

              <?php if (!empty($priest['mobile_number'])): ?>
                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center flex-shrink-0">
                    <i class="ph ph-phone"></i>
                  </div>
                  <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Contact</div>
                    <div class="text-gray-700"><?= html_escape($priest['mobile_number']) ?></div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="bg-parish-900 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
      <div class="heritage-kicker text-gold-300">Pastoral assistance</div>
      <h2 class="text-2xl sm:text-3xl font-bold mt-2">Need to speak with the parish office or a priest?</h2>
      <p class="text-white/65 mt-2 max-w-2xl">
        For sacramental preparation, appointments, blessings or pastoral concerns, contact the parish office so your request can be coordinated properly.
      </p>
    </div>
    <a href="<?= site_url('contact') ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white text-parish-900 font-semibold hover:bg-gold-50 transition flex-shrink-0">
      Contact the Parish <i class="ph ph-arrow-right"></i>
    </a>
  </div>
</section>
