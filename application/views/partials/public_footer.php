<?php
$footer_is_parishioner = !empty($current_user) && (int)$current_user['role_id'] === (int)ROLE_PARISHIONER;
$footer_restricted = !empty($current_user) && !$footer_is_parishioner;
?>
<footer class="bg-parish-900 text-white mt-0">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid sm:grid-cols-2 lg:grid-cols-[1.2fr_.8fr_.8fr_1fr] gap-10 lg:gap-12">
      <div>
        <div class="flex items-center gap-3 mb-5">
          <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/10 overflow-hidden shadow-sm">
            <img src="<?= base_url('favicon.svg') ?>" alt="Sta. Monica Parish Church logo" class="w-full h-full object-cover">
          </div>
          <div>
            <div class="font-bold text-lg">Sta. Monica Parish Church</div>
            <div class="text-[10px] text-gold-300 tracking-[.18em] uppercase">Alburquerque · Bohol</div>
          </div>
        </div>
        <p class="text-sm text-white/60 leading-relaxed max-w-sm">A living Catholic parish and heritage landmark where faith, history and community continue to meet.</p>
        <div class="mt-5 inline-flex items-center gap-2 text-sm text-white/70">
          <i class="ph ph-map-pin text-gold-300"></i>
          Poblacion, Alburquerque, Bohol, Philippines
        </div>
      </div>

      <div>
        <div class="text-sm font-semibold text-white mb-4">Visit &amp; Explore</div>
        <ul class="space-y-2.5 text-sm text-white/60">
          <li><a href="<?= site_url('about') ?>" class="hover:text-gold-200 transition">Church Heritage</a></li>
          <li><a href="<?= site_url('mass-schedule') ?>" class="hover:text-gold-200 transition">Mass Schedule</a></li>
          <li><a href="<?= site_url('announcements') ?>" class="hover:text-gold-200 transition">Announcements</a></li>
          <li><a href="<?= site_url('events') ?>" class="hover:text-gold-200 transition">Parish Events</a></li>
          <li><a href="<?= site_url('chapels') ?>" class="hover:text-gold-200 transition">Chapels &amp; GSK</a></li>
          <li><a href="<?= site_url('parish-organization') ?>" class="hover:text-gold-200 transition">Parish Organization</a></li>
          <li><a href="<?= site_url('projects') ?>" class="hover:text-gold-200 transition">Parish Projects</a></li>
          <li><a href="<?= site_url('priests') ?>" class="hover:text-gold-200 transition">Our Priests</a></li>
          <li><a href="<?= site_url('st-monica') ?>" class="hover:text-gold-200 transition">Life of St. Monica</a></li>
          <li><a href="<?= site_url('prayers') ?>" class="hover:text-gold-200 transition">Prayers &amp; Novena</a></li>
          <li><a href="<?= site_url('contact') ?>" class="hover:text-gold-200 transition">Plan Your Visit</a></li>
        </ul>
      </div>

      <div>
        <div class="text-sm font-semibold text-white mb-4">Parish Services</div>
        <ul class="space-y-2.5 text-sm text-white/60">
          <li><a href="<?= site_url('sacraments') ?>" class="hover:text-gold-200 transition">Sacraments &amp; Services</a></li>
          <?php if ($footer_restricted): ?>
            <li><span class="inline-flex items-center gap-1.5 text-white/35 cursor-not-allowed" title="Parishioner account required"><i class="ph ph-lock-key text-[11px]"></i>Baptism Booking</span></li>
            <li><span class="inline-flex items-center gap-1.5 text-white/35 cursor-not-allowed" title="Parishioner account required"><i class="ph ph-lock-key text-[11px]"></i>Wedding Booking</span></li>
            <li><span class="inline-flex items-center gap-1.5 text-white/35 cursor-not-allowed" title="Parishioner account required"><i class="ph ph-lock-key text-[11px]"></i>Request Certificate</span></li>
          <?php else: ?>
            <li><a href="<?= site_url('my/bookings/new/baptism') ?>" class="hover:text-gold-200 transition">Baptism</a></li>
            <li><a href="<?= site_url('my/bookings/new/wedding') ?>" class="hover:text-gold-200 transition">Wedding</a></li>
            <li><a href="<?= site_url('my/certificates/new') ?>" class="hover:text-gold-200 transition">Request Certificate</a></li>
          <?php endif; ?>
          <li><a href="<?= site_url('donate') ?>" class="hover:text-gold-200 transition">Support the Parish</a></li>
        </ul>
      </div>

      <div>
        <div class="text-sm font-semibold text-white mb-4">Need Assistance?</div>
        <p class="text-sm text-white/60 leading-relaxed">Questions about Masses, sacraments, records or parish activities can be sent directly to the parish office.</p>
        <a href="<?= site_url('contact') ?>" class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-white text-parish-900 text-sm font-semibold hover:bg-gold-50 transition">
          Contact Parish Office <i class="ph ph-arrow-right"></i>
        </a>
      </div>
    </div>

    <div class="mt-12 pt-7 border-t border-white/10 flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between text-xs text-white/40">
      <div>&copy; <?= date('Y') ?> Sta. Monica Parish Church. All rights reserved.</div>
      <div class="max-w-3xl lg:text-right leading-relaxed">
        Heritage photography used on this site: Patrick Roque / Wikimedia Commons, licensed under
        <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener" class="underline hover:text-white">CC BY-SA 4.0</a>.
        <a href="https://commons.wikimedia.org/wiki/Category:Saint_Monica_Church_(Alburquerque,_Bohol)" target="_blank" rel="noopener" class="underline hover:text-white">View source gallery</a>.
      </div>
    </div>
  </div>
</footer>
