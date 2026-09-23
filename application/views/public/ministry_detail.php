<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/c/c8/Santa_Monica_Church_Alburquerque_with_convent_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
?>
<article>
  <section class="relative overflow-hidden bg-parish-900 min-h-[320px] flex items-end">
    <img src="<?= $hero_img ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-35">
    <div class="absolute inset-0 bg-gradient-to-r from-parish-900 via-parish-900/85 to-parish-900/40"></div>
    <div class="relative max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-12 sm:py-14 text-white">
      <a href="<?= site_url('ministries') ?>" class="inline-flex items-center gap-2 text-sm text-white/65 hover:text-white transition"><i class="ph ph-arrow-left"></i> Back to Ministries</a>
      <div class="heritage-kicker text-gold-200 mt-7">Serve with the parish</div>
      <h1 class="text-3xl sm:text-5xl font-bold tracking-tight mt-2"><?= html_escape($item['name']) ?></h1>
      <?php if ($item['contact_person']): ?><div class="text-sm text-white/60 mt-3 inline-flex items-center gap-2"><i class="ph ph-user"></i> Contact: <?= html_escape($item['contact_person']) ?></div><?php endif; ?>
    </div>
  </section>

  <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
    <div class="grid lg:grid-cols-[1.05fr_.95fr] gap-8 items-start">
      <div class="rounded-[2rem] bg-white border border-stonewarm-200 p-7 sm:p-10 shadow-soft">
        <div class="heritage-kicker text-gold-600">About the ministry</div>
        <div class="prose prose-sm sm:prose max-w-none mt-5 text-gray-700 leading-relaxed whitespace-pre-line">
          <?= nl2br(html_escape($item['description'])) ?>
        </div>
      </div>

      <div class="rounded-[2rem] bg-parish-50 border border-parish-100 p-7 sm:p-8">
        <div class="w-12 h-12 rounded-2xl bg-white text-parish-700 flex items-center justify-center text-2xl shadow-sm"><i class="ph ph-users-three"></i></div>
        <div class="heritage-kicker text-gold-600 mt-5">Interested to join?</div>
        <h2 class="text-2xl font-bold text-parish-900 mt-1">Introduce yourself to the ministry.</h2>
        <p class="text-sm text-gray-600 mt-2 mb-5">Send your contact details and the parish can follow up with next steps.</p>
        <form id="ministry-interest-form" class="space-y-3">
          <input type="hidden" name="ministry_id" value="<?= $item['id'] ?>">
          <input required name="full_name" placeholder="Full Name" class="w-full px-4 py-3 rounded-xl border border-parish-100 bg-white text-sm focus:ring-2 focus:ring-parish-200 outline-none">
          <input required name="contact_number" placeholder="Contact Number" class="w-full px-4 py-3 rounded-xl border border-parish-100 bg-white text-sm focus:ring-2 focus:ring-parish-200 outline-none">
          <textarea name="message" placeholder="Tell us why you're interested (optional)" class="w-full px-4 py-3 rounded-xl border border-parish-100 bg-white text-sm focus:ring-2 focus:ring-parish-200 outline-none" rows="4"></textarea>
          <button type="submit" class="w-full px-5 py-3 rounded-xl bg-parish-800 hover:bg-parish-900 text-white text-sm font-semibold transition">Send Interest</button>
        </form>
      </div>
    </div>
  </section>

  <script>
    $('#ministry-interest-form').on('submit', function(e){
      e.preventDefault();
      $.post('<?= site_url('home/ajax_ministry_interest') ?>', $(this).serialize(), function(res){
        if(res.success){ Swal.fire({icon:'success', title:'Thank you!', text: res.message, confirmButtonColor:'#235a38'}); $('#ministry-interest-form')[0].reset(); }
        else { toastr.error(res.message); }
      });
    });
  </script>
</article>
