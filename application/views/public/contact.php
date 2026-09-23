<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Santa_Monica_Church_Alburquerque_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[360px] flex items-end">
  <img src="<?= $hero_img ?>" alt="Santa Monica Parish Church, Alburquerque, Bohol" class="absolute inset-0 w-full h-full object-cover object-center">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900/95 via-parish-900/68 to-black/15"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Plan your visit</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Visit &amp; Contact</h1>
    <p class="text-white/70 mt-3 max-w-2xl text-lg">Whether you are coming for Mass, a sacrament, parish records or a heritage visit, we look forward to welcoming you to Alburquerque.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
  <div class="grid lg:grid-cols-[.85fr_1.15fr] gap-8 lg:gap-12">
    <div class="space-y-5">
      <div class="rounded-[2rem] bg-parish-900 text-white p-7 sm:p-8 shadow-heritage">
        <div class="heritage-kicker text-gold-300">Find us</div>
        <h2 class="text-2xl font-bold mt-3">Sta. Monica Parish Church</h2>
        <div class="mt-6 space-y-4 text-sm text-white/70">
          <div class="flex gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-gold-300 flex-shrink-0"><i class="ph ph-map-pin text-xl"></i></div>
            <div><div class="font-semibold text-white">Poblacion, Alburquerque</div><div class="mt-0.5">Bohol, Philippines</div></div>
          </div>
          <div class="flex gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-gold-300 flex-shrink-0"><i class="ph ph-church text-xl"></i></div>
            <div><div class="font-semibold text-white">Roman Catholic Parish</div><div class="mt-0.5">A place of worship and heritage since 1842</div></div>
          </div>
          <div class="flex gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-gold-300 flex-shrink-0"><i class="ph ph-clock text-xl"></i></div>
            <div><div class="font-semibold text-white">Planning a parish office visit?</div><div class="mt-0.5">Send an inquiry first for current office availability.</div></div>
          </div>
        </div>
        <a href="https://www.google.com/maps/search/?api=1&query=9.6227,123.9121" target="_blank" rel="noopener" class="mt-7 inline-flex items-center gap-2 px-5 py-3 rounded-full bg-white text-parish-900 font-semibold hover:bg-gold-50 transition">
          <i class="ph ph-navigation-arrow"></i> Get Directions
        </a>
      </div>

      <div class="rounded-3xl bg-white border border-stonewarm-200 p-6">
        <div class="heritage-kicker text-gold-600">Before you come</div>
        <div class="mt-4 space-y-3 text-sm text-gray-600">
          <a href="<?= site_url('mass-schedule') ?>" class="flex items-center justify-between rounded-xl bg-stonewarm-50 p-4 hover:bg-parish-50 transition"><span class="flex items-center gap-2"><i class="ph ph-clock text-parish-700"></i> Check Mass times</span><i class="ph ph-arrow-right"></i></a>
          <a href="<?= site_url('announcements') ?>" class="flex items-center justify-between rounded-xl bg-stonewarm-50 p-4 hover:bg-parish-50 transition"><span class="flex items-center gap-2"><i class="ph ph-megaphone text-parish-700"></i> Check schedule notices</span><i class="ph ph-arrow-right"></i></a>
          <a href="<?= site_url('sacraments') ?>" class="flex items-center justify-between rounded-xl bg-stonewarm-50 p-4 hover:bg-parish-50 transition"><span class="flex items-center gap-2"><i class="ph ph-file-text text-parish-700"></i> Review sacrament services</span><i class="ph ph-arrow-right"></i></a>
        </div>
      </div>
    </div>

    <div class="grid gap-5">
      <div class="rounded-[2rem] bg-white border border-stonewarm-200 p-6 sm:p-8 shadow-soft">
        <div class="heritage-kicker text-gold-600">Parish office</div>
        <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-2">Send us a message</h2>
        <p class="text-sm text-gray-500 mt-2 mb-6">Ask about schedules, sacraments, records, bookings, ministries or parish activities.</p>
        <form id="contact-form" class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="text-xs font-semibold text-gray-500">Your Name</label>
            <input required name="name" placeholder="Full name" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-stonewarm-200 bg-stonewarm-50/40 text-sm focus:ring-2 focus:ring-parish-200 focus:border-parish-300 outline-none">
          </div>
          <div>
            <label class="text-xs font-semibold text-gray-500">Email Address</label>
            <input required type="email" name="email" placeholder="you@example.com" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-stonewarm-200 bg-stonewarm-50/40 text-sm focus:ring-2 focus:ring-parish-200 focus:border-parish-300 outline-none">
          </div>
          <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-gray-500">Message</label>
            <textarea required name="message" rows="5" placeholder="How can the parish office help you?" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-stonewarm-200 bg-stonewarm-50/40 text-sm focus:ring-2 focus:ring-parish-200 focus:border-parish-300 outline-none"></textarea>
          </div>
          <button type="submit" class="sm:col-span-2 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-parish-800 hover:bg-parish-900 text-white text-sm font-semibold transition"><i class="ph ph-paper-plane-tilt"></i> Send Message</button>
        </form>
      </div>

      <div class="rounded-[2rem] bg-gold-50 border border-gold-100 p-6 sm:p-8">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-2xl bg-white text-gold-600 flex items-center justify-center text-2xl shadow-sm flex-shrink-0"><i class="ph ph-hands-praying"></i></div>
          <div class="flex-1">
            <div class="heritage-kicker text-gold-700">Prayer request</div>
            <h2 class="text-2xl font-bold text-parish-900 mt-1">Let us pray with you.</h2>
            <p class="text-sm text-gray-600 mt-2 mb-5">Share an intention with the parish. You may keep your request confidential.</p>
            <form id="prayer-form" class="space-y-3">
              <input name="requestor_name" placeholder="Your Name (optional)" class="w-full px-4 py-3 rounded-xl border border-gold-200 bg-white text-sm focus:ring-2 focus:ring-gold-200 outline-none">
              <textarea required name="request_text" rows="4" placeholder="Share your prayer intention…" class="w-full px-4 py-3 rounded-xl border border-gold-200 bg-white text-sm focus:ring-2 focus:ring-gold-200 outline-none"></textarea>
              <label class="flex items-center gap-2 text-xs text-gray-600">
                <input type="checkbox" name="is_confidential" value="1" checked class="rounded border-gray-300 text-parish-700"> Keep this request confidential
              </label>
              <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gold-600 hover:bg-gold-700 text-white text-sm font-semibold transition"><i class="ph ph-heart"></i> Submit Prayer Request</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  $('#contact-form').on('submit', function(e){
    e.preventDefault();
    $.post('<?= site_url('home/ajax_contact') ?>', $(this).serialize(), function(res){
      if(res.success){ Swal.fire({icon:'success', title:'Message Sent', text: res.message, confirmButtonColor:'#235a38'}); $('#contact-form')[0].reset(); }
      else { toastr.error(res.message); }
    });
  });
  $('#prayer-form').on('submit', function(e){
    e.preventDefault();
    $.post('<?= site_url('home/ajax_prayer_request') ?>', $(this).serialize(), function(res){
      if(res.success){ Swal.fire({icon:'success', title:'Received', text: res.message, confirmButtonColor:'#235a38'}); $('#prayer-form')[0].reset(); }
      else { toastr.error(res.message); }
    });
  });
</script>
