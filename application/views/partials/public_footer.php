<?php
$footer_is_parishioner = !empty($current_user) && (int)$current_user['role_id'] === (int)ROLE_PARISHIONER;
$footer_restricted = !empty($current_user) && !$footer_is_parishioner;
$footer_settings = $public_settings ?? [];
$facebook_url = trim((string)($footer_settings['facebook_url'] ?? ''));
$parish_name = trim((string)($footer_settings['parish_name'] ?? '')) ?: 'Sta. Monica Parish Church';
$parish_address = trim((string)($footer_settings['parish_address'] ?? '')) ?: 'Poblacion, Alburquerque, Bohol, Philippines';
$parish_contact = trim((string)($footer_settings['parish_contact'] ?? ''));
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
          <?= html_escape($parish_address) ?>
        </div>

        <div class="mt-6">
          <div class="text-[10px] uppercase tracking-[.16em] text-white/35 font-bold">Follow us</div>
          <div class="mt-2 flex items-center gap-2">
            <?php if ($facebook_url !== ''): ?>
              <a href="<?= html_escape($facebook_url) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Sta. Monica Parish Church on Facebook" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/10 border border-white/10 text-sm text-white/80 hover:bg-white hover:text-parish-900 transition">
                <i class="ph ph-facebook-logo text-lg"></i>
                Facebook
              </a>
            <?php else: ?>
              <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 text-sm text-white/35" title="Facebook page link has not been configured yet">
                <i class="ph ph-facebook-logo text-lg"></i>
                Facebook
              </span>
            <?php endif; ?>
          </div>
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
      <div>
        <div>&copy; <?= date('Y') ?> <?= html_escape($parish_name) ?>. All rights reserved.</div>
        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2">
          <button type="button" onclick="openFooterLegal('terms')" class="hover:text-gold-200 underline underline-offset-4 transition">Terms &amp; Conditions</button>
          <button type="button" onclick="openFooterLegal('privacy')" class="hover:text-gold-200 underline underline-offset-4 transition">Privacy Policy</button>
        </div>
      </div>
      <div class="max-w-3xl lg:text-right leading-relaxed">
        Heritage photography used on this site: Patrick Roque / Wikimedia Commons, licensed under
        <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener" class="underline hover:text-white">CC BY-SA 4.0</a>.
        <a href="https://commons.wikimedia.org/wiki/Category:Saint_Monica_Church_(Alburquerque,_Bohol)" target="_blank" rel="noopener" class="underline hover:text-white">View source gallery</a>.
      </div>
    </div>
  </div>
</footer>

<!-- Footer legal modals -->
<div id="footer-terms-modal" class="hidden fixed inset-0 z-[90] items-center justify-center p-3 sm:p-5" role="dialog" aria-modal="true" aria-labelledby="footer-terms-title">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px]" onclick="closeFooterLegal('terms')"></div>
  <div class="relative w-full max-w-3xl max-h-[92vh] bg-white rounded-[1.5rem] shadow-2xl overflow-hidden flex flex-col">
    <div class="flex-shrink-0 px-5 sm:px-7 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
      <div>
        <div class="text-[10px] uppercase tracking-[.16em] text-gold-600 font-bold">Public Website</div>
        <h2 id="footer-terms-title" class="text-xl sm:text-2xl font-bold text-parish-900 mt-1">Terms &amp; Conditions</h2>
        <p class="text-xs text-gray-400 mt-1">Last updated: September 25, 2026</p>
      </div>
      <button type="button" onclick="closeFooterLegal('terms')" class="w-9 h-9 rounded-xl hover:bg-gray-100 text-gray-500 flex items-center justify-center" aria-label="Close Terms and Conditions">
        <i class="ph ph-x text-xl"></i>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto px-5 sm:px-7 py-6 text-sm text-gray-600 leading-relaxed space-y-6 scrollbar-thin">
      <section>
        <h3 class="font-bold text-gray-900">1. Use of this website</h3>
        <p class="mt-2">This website and Parish Connect services are provided by <?= html_escape($parish_name) ?> to share parish information and support parishioner services such as bookings, sacramental record requests, Mass intentions, donations, announcements, chapel information and related parish transactions.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">2. Accounts and submitted information</h3>
        <p class="mt-2">Users are responsible for providing accurate information and protecting their account credentials. Parishioner-only services must be submitted using a parishioner account. Staff, administrator and priest accounts have separate permissions and must not be used to submit parishioner applications.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">3. Bookings, requests and schedules</h3>
        <p class="mt-2">Submitting an online request does not by itself guarantee a sacrament, service, appointment, certificate, Mass intention or schedule. Requests remain subject to parish review, documentary requirements, priest or facility availability, payment verification where applicable, and parish pastoral or administrative policies.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">4. Payments and donations</h3>
        <p class="mt-2">GCash references and proof of payment submitted through the website are reviewed by authorized parish staff. A payment or donation is treated as verified only after parish confirmation. Incorrect references, duplicate submissions or insufficient proof may be returned for correction.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">5. Sacramental and archival records</h3>
        <p class="mt-2">Digital sacramental records and scanned archival references are intended to support parish administration and searching. Original parish registers, canonical records and other official source documents remain subject to parish and diocesan record-keeping practices. If a discrepancy is found, parish staff may require review of the original register before issuing a certificate or making a correction.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">6. Acceptable use</h3>
        <p class="mt-2">Users must not attempt to access records, accounts or administrative functions for which they are not authorized, upload unlawful or malicious content, impersonate another person, interfere with website operation, or misuse parish information.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">7. Website information and external links</h3>
        <p class="mt-2">The parish aims to keep schedules and information accurate, but Mass times, events, office services and other details may change. Public announcements and direct parish communication should be checked for the latest information. Links to third-party services such as Facebook, GCash or map providers are governed by those services' own terms.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">8. Changes and contact</h3>
        <p class="mt-2">These terms may be updated as parish services or website features change. Questions about these terms may be directed to the parish office through the <a href="<?= site_url('contact') ?>" class="text-parish-700 font-semibold hover:underline">Contact page</a><?php if ($parish_contact !== ''): ?> or <?= html_escape($parish_contact) ?><?php endif; ?>.</p>
      </section>
    </div>

    <div class="flex-shrink-0 px-5 sm:px-7 py-4 border-t border-gray-100 bg-white flex justify-end">
      <button type="button" onclick="closeFooterLegal('terms')" class="px-5 py-2.5 rounded-xl bg-parish-800 hover:bg-parish-900 text-white text-sm font-semibold">Close</button>
    </div>
  </div>
</div>

<div id="footer-privacy-modal" class="hidden fixed inset-0 z-[90] items-center justify-center p-3 sm:p-5" role="dialog" aria-modal="true" aria-labelledby="footer-privacy-title">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px]" onclick="closeFooterLegal('privacy')"></div>
  <div class="relative w-full max-w-3xl max-h-[92vh] bg-white rounded-[1.5rem] shadow-2xl overflow-hidden flex flex-col">
    <div class="flex-shrink-0 px-5 sm:px-7 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
      <div>
        <div class="text-[10px] uppercase tracking-[.16em] text-gold-600 font-bold">Data &amp; Privacy</div>
        <h2 id="footer-privacy-title" class="text-xl sm:text-2xl font-bold text-parish-900 mt-1">Privacy Policy</h2>
        <p class="text-xs text-gray-400 mt-1">Last updated: September 25, 2026</p>
      </div>
      <button type="button" onclick="closeFooterLegal('privacy')" class="w-9 h-9 rounded-xl hover:bg-gray-100 text-gray-500 flex items-center justify-center" aria-label="Close Privacy Policy">
        <i class="ph ph-x text-xl"></i>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto px-5 sm:px-7 py-6 text-sm text-gray-600 leading-relaxed space-y-6 scrollbar-thin">
      <section>
        <h3 class="font-bold text-gray-900">1. Information we may collect</h3>
        <p class="mt-2">Depending on the service used, <?= html_escape($parish_name) ?> may collect account information, contact details, family or sacramental information, booking details, certificate requests, Mass intentions, donation information, uploaded documents, GCash reference numbers and proof-of-payment images, messages submitted to the parish, and technical session information needed to operate the website securely.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">2. Why the parish uses this information</h3>
        <p class="mt-2">Information is used to process parish services, verify identity and requirements, maintain sacramental and administrative records, coordinate schedules, verify payments and donations, issue receipts or certificates, send service-related notifications, answer inquiries, maintain security, and improve parish operations.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">3. Sensitive parish and sacramental information</h3>
        <p class="mt-2">Sacramental records, scanned register pages and related historical documents may contain personal and family information. Access to these records is restricted according to account role and parish responsibilities. Public visitors cannot browse private sacramental register pages through the website.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">4. Payments</h3>
        <p class="mt-2">The website may record the amount, payment reference number, proof image, verification status and official receipt information for GCash transactions. The parish website does not need your GCash password or PIN and should never ask you to submit them.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">5. Sharing and access</h3>
        <p class="mt-2">Personal information is intended for authorized parish personnel who need it to perform their responsibilities. Information may also pass through hosting, email, messaging, payment or other service providers when necessary to provide the requested service. The parish does not make private sacramental or account information publicly available merely because it is stored digitally.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">6. Data retention and archival records</h3>
        <p class="mt-2">Different records may need to be retained for different periods. Sacramental and archival records may have long-term or permanent ecclesiastical record-keeping value, while temporary operational data may be retained only as needed for parish administration, accountability, security or applicable requirements.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">7. Security</h3>
        <p class="mt-2">The parish uses role-based access and other administrative safeguards to limit access to protected information. No online system can guarantee absolute security, so users should protect their passwords, avoid sharing account access, and report suspicious activity to the parish office.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">8. Your requests regarding personal information</h3>
        <p class="mt-2">If you believe personal information in the system is incorrect, or you have a question about how information is being used, contact the parish office. Some historical or sacramental entries cannot simply be erased because they form part of an official parish or ecclesiastical register; corrections may require verification against the original record and applicable parish or diocesan procedure.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">9. Children and family records</h3>
        <p class="mt-2">Some parish services necessarily involve minors, such as baptism, confirmation, catechesis or family records. Information for minors should be submitted by an appropriate parent, guardian or authorized parish representative where applicable and should be limited to what is needed for the parish service.</p>
      </section>

      <section>
        <h3 class="font-bold text-gray-900">10. Contact</h3>
        <p class="mt-2">Privacy questions or correction requests may be sent through the <a href="<?= site_url('contact') ?>" class="text-parish-700 font-semibold hover:underline">Contact page</a><?php if ($parish_contact !== ''): ?> or by contacting the parish office at <?= html_escape($parish_contact) ?><?php endif; ?>.</p>
      </section>
    </div>

    <div class="flex-shrink-0 px-5 sm:px-7 py-4 border-t border-gray-100 bg-white flex justify-end">
      <button type="button" onclick="closeFooterLegal('privacy')" class="px-5 py-2.5 rounded-xl bg-parish-800 hover:bg-parish-900 text-white text-sm font-semibold">Close</button>
    </div>
  </div>
</div>

<script>
(function(){
  var activeFooterLegal = null;

  window.openFooterLegal = function(type){
    var id = type === 'privacy' ? 'footer-privacy-modal' : 'footer-terms-modal';
    var modal = document.getElementById(id);
    if (!modal) return;
    activeFooterLegal = type;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  };

  window.closeFooterLegal = function(type){
    var id = type === 'privacy' ? 'footer-privacy-modal' : 'footer-terms-modal';
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
    activeFooterLegal = null;
  };

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && activeFooterLegal) {
      window.closeFooterLegal(activeFooterLegal);
    }
  });
})();
</script>
