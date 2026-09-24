<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Santa_Monica_Church_Alburquerque_inside_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
$is_parishioner = !empty($current_user) && defined('ROLE_PARISHIONER') && (int)$current_user['role_id'] === (int)ROLE_PARISHIONER;
$qr = !empty($gcash['gcash_qr_image']) ? base_url(ltrim($gcash['gcash_qr_image'],'/')) : null;
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[380px] flex items-end">
  <img src="<?= $hero_img ?>" alt="Interior of Santa Monica Parish Church" class="absolute inset-0 w-full h-full object-cover object-center">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900/96 via-parish-900/78 to-black/25"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Stewardship & generosity</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Give to Sta. Monica Parish</h1>
    <p class="text-white/70 mt-3 max-w-2xl text-lg">Support parish projects, ministries, church care and community outreach. Choose where your gift goes and submit it through the parish’s verified GCash workflow.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-18">
  <div class="grid lg:grid-cols-[1.05fr_.95fr] gap-8 lg:gap-12 items-start">
    <div>
      <div class="heritage-kicker text-gold-600">Ways to give</div>
      <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-3">Choose a purpose for your donation.</h2>
      <p class="text-gray-600 leading-relaxed mt-4 text-lg">You may support a specific parish project or make an unrestricted gift to the General Parish Fund for pastoral and operational needs.</p>

      <div class="grid sm:grid-cols-2 gap-4 mt-8">
        <div class="rounded-2xl bg-white border border-stonewarm-200 p-5">
          <i class="ph ph-church text-2xl text-parish-700"></i>
          <h3 class="font-bold text-parish-900 mt-3">Church Care</h3>
          <p class="text-sm text-gray-500 mt-1.5">Maintenance, restoration and improvements to parish facilities.</p>
        </div>
        <div class="rounded-2xl bg-white border border-stonewarm-200 p-5">
          <i class="ph ph-hand-heart text-2xl text-parish-700"></i>
          <h3 class="font-bold text-parish-900 mt-3">Community Outreach</h3>
          <p class="text-sm text-gray-500 mt-1.5">Charitable, pastoral and community-based programs.</p>
        </div>
        <div class="rounded-2xl bg-white border border-stonewarm-200 p-5">
          <i class="ph ph-users-three text-2xl text-parish-700"></i>
          <h3 class="font-bold text-parish-900 mt-3">Parish Ministries</h3>
          <p class="text-sm text-gray-500 mt-1.5">Formation, youth, family and ministry activities.</p>
        </div>
        <div class="rounded-2xl bg-white border border-stonewarm-200 p-5">
          <i class="ph ph-heart-straight text-2xl text-parish-700"></i>
          <h3 class="font-bold text-parish-900 mt-3">General Parish Fund</h3>
          <p class="text-sm text-gray-500 mt-1.5">Flexible support for priority parish needs and mission.</p>
        </div>
      </div>

      <div class="mt-8 rounded-2xl border border-parish-100 bg-parish-50/60 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="text-xs uppercase tracking-wider text-parish-600 font-bold">General Parish Fund</div>
            <div class="text-2xl font-bold text-parish-900 mt-1"><?= peso($general_raised) ?> verified</div>
            <p class="text-xs text-gray-500 mt-1">Verified general donations recorded through Parish Connect.</p>
          </div>
          <?php if ($is_parishioner): ?>
            <a href="<?= site_url('my/donations/new') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-parish-800 hover:bg-parish-900 text-white text-sm font-semibold"><i class="ph ph-heart"></i> Give to General Fund</a>
          <?php else: ?>
            <a href="<?= site_url('login') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-parish-800 hover:bg-parish-900 text-white text-sm font-semibold"><i class="ph ph-sign-in"></i> Log In to Give</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <aside class="rounded-[2rem] bg-parish-900 text-white p-7 sm:p-9 shadow-heritage">
      <div class="heritage-kicker text-gold-300">Official GCash</div>
      <h2 class="text-2xl font-bold mt-2">Parish giving details</h2>
      <p class="text-sm text-white/60 mt-2">Use only the official parish account shown here. Parish Connect asks for your reference number and proof so staff can verify the donation.</p>

      <div class="mt-7 bg-white rounded-3xl p-5 text-center min-h-[250px] flex items-center justify-center">
        <?php if ($qr): ?>
          <img src="<?= html_escape($qr) ?>" alt="Sta. Monica Parish GCash QR code" class="max-w-[230px] max-h-[230px] object-contain mx-auto">
        <?php else: ?>
          <div class="text-gray-300">
            <i class="ph ph-qr-code text-7xl"></i>
            <div class="text-xs mt-3 text-gray-400">GCash QR image has not been configured yet.</div>
          </div>
        <?php endif; ?>
      </div>

      <div class="mt-5 grid sm:grid-cols-2 gap-3">
        <div class="rounded-xl bg-white/10 border border-white/10 p-4">
          <div class="text-[10px] uppercase tracking-wider text-white/40">Account Name</div>
          <div class="font-semibold mt-1"><?= html_escape($gcash['gcash_account_name'] ?? 'To be configured') ?></div>
        </div>
        <div class="rounded-xl bg-white/10 border border-white/10 p-4">
          <div class="text-[10px] uppercase tracking-wider text-white/40">GCash Number</div>
          <div class="font-semibold mt-1"><?= html_escape($gcash['gcash_account_number'] ?? 'To be configured') ?></div>
        </div>
      </div>

      <div class="mt-6 rounded-xl bg-gold-400/10 border border-gold-300/15 p-4 text-xs text-white/65 leading-relaxed">
        <strong class="text-gold-200 block mb-1">How verification works</strong>
        1. Choose a project or General Parish Fund.<br>
        2. Enter your donation amount.<br>
        3. Send the amount through GCash.<br>
        4. Submit the GCash reference and screenshot.<br>
        5. Parish staff verifies the gift and issues the recorded receipt number.
      </div>
    </aside>
  </div>
</section>

<?php if (!empty($projects)): ?>
<section class="bg-stonewarm-50 border-y border-stonewarm-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-7">
      <div>
        <div class="heritage-kicker text-gold-600">Give with purpose</div>
        <h2 class="text-3xl font-bold text-parish-900 mt-2">Active parish projects</h2>
        <p class="text-gray-600 mt-2">Choose the project you want your verified donation to support.</p>
      </div>
      <a href="<?= site_url('projects') ?>" class="text-sm font-semibold text-parish-700 hover:underline">View all projects <i class="ph ph-arrow-right ml-1"></i></a>
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
      <?php foreach ($projects as $p):
        $img = !empty($p['cover_image']) ? base_url($p['cover_image']) : $hero_img;
      ?>
      <article class="rounded-3xl overflow-hidden bg-white border border-stonewarm-200">
        <a href="<?= site_url('projects/' . $p['slug']) ?>" class="block relative h-48 overflow-hidden bg-parish-900">
          <img src="<?= html_escape($img) ?>" alt="<?= html_escape($p['title']) ?>" class="w-full h-full object-cover">
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
          <?php if (!empty($p['category'])): ?><div class="absolute bottom-4 left-4 text-[10px] uppercase tracking-wider font-bold text-gold-200"><?= html_escape($p['category']) ?></div><?php endif; ?>
        </a>
        <div class="p-5">
          <a href="<?= site_url('projects/' . $p['slug']) ?>" class="font-bold text-lg text-parish-900 hover:text-parish-700"><?= html_escape($p['title']) ?></a>
          <p class="text-sm text-gray-500 mt-2 line-clamp-2"><?= html_escape($p['short_description'] ?: mb_strimwidth(strip_tags($p['description']),0,120,'…')) ?></p>

          <?php if ((float)$p['goal_amount'] > 0): ?>
            <div class="mt-4">
              <div class="flex justify-between text-xs"><span class="font-semibold text-gray-700"><?= peso($p['raised_amount']) ?></span><span class="text-gray-400"><?= number_format((float)$p['progress_percent'],1) ?>%</span></div>
              <div class="h-2 rounded-full bg-gray-100 overflow-hidden mt-2"><div class="h-full rounded-full bg-parish-600" style="width:<?= min(100,(float)$p['progress_percent']) ?>%"></div></div>
            </div>
          <?php endif; ?>

          <?php if ($is_parishioner): ?>
            <a href="<?= site_url('my/donations/new/' . $p['id']) ?>" class="mt-5 inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold"><i class="ph ph-heart"></i> Donate to Project</a>
          <?php else: ?>
            <a href="<?= site_url('login') ?>" class="mt-5 inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-parish-50 hover:bg-parish-100 text-parish-700 text-sm font-semibold"><i class="ph ph-sign-in"></i> Log In to Donate</a>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
  <div class="rounded-[2rem] bg-white border border-stonewarm-200 p-7 sm:p-10">
    <div class="grid md:grid-cols-[.8fr_1.2fr] gap-7 items-center">
      <div>
        <i class="ph ph-shield-check text-4xl text-parish-700"></i>
        <h2 class="text-2xl font-bold text-parish-900 mt-3">Transparent parish giving</h2>
      </div>
      <div class="text-sm text-gray-600 leading-relaxed">
        <p>Project progress shown on this website is based on donations whose payment records have been verified by parish staff. Pending or rejected payment submissions are not included in public totals.</p>
        <p class="mt-3">For large gifts, in-kind donations, sponsorships, or questions about official receipts, please contact the parish office directly.</p>
        <a href="<?= site_url('contact') ?>" class="mt-4 inline-flex items-center gap-2 text-parish-700 font-semibold hover:underline">Contact the Parish Office <i class="ph ph-arrow-right"></i></a>
      </div>
    </div>
  </div>
</section>
