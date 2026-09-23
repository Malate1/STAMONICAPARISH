<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/c/c8/Santa_Monica_Church_Alburquerque_with_convent_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
$interior_img = 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Santa_Monica_Church_Alburquerque_inside_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
?>

<section class="relative min-h-[500px] flex items-end overflow-hidden bg-parish-900">
  <img src="<?= $hero_img ?>" alt="Santa Monica Parish Church and convent" class="absolute inset-0 w-full h-full object-cover">
  <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/45 to-black/20"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
    <div class="max-w-3xl text-white">
      <div class="heritage-kicker text-gold-200">Our heritage</div>
      <h1 class="text-4xl sm:text-6xl font-bold tracking-[-.04em] leading-tight mt-3">A parish shaped by faith, history and the people of Alburquerque.</h1>
      <p class="mt-5 text-lg text-white/75 max-w-2xl">From a humble nineteenth-century chapel to an enduring coral-stone landmark, Sta. Monica Parish continues to serve as a spiritual home and a witness to Bohol's heritage.</p>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
  <div class="grid lg:grid-cols-[.9fr_1.1fr] gap-12 lg:gap-20 items-start">
    <div class="lg:sticky lg:top-32">
      <div class="heritage-kicker text-gold-600">The parish story</div>
      <div class="heritage-rule mt-3"></div>
      <h2 class="public-section-title text-parish-900 mt-5">Nearly two centuries of worship and community.</h2>
      <p class="text-gray-600 leading-relaxed mt-6 text-lg">The parish traces its beginnings to 1842, when a chapel, convento and school were erected while the settlement was still associated with Baclayon. That early community would grow into the town of Alburquerque and, in time, an independent parish dedicated to Santa Monica.</p>
      <p class="text-gray-600 leading-relaxed mt-4">The present church reflects generations of construction, devotion and craftsmanship. Its wide grounds, distinctive arcade, convento and richly painted interior make the complex both a living parish and an important part of Bohol's cultural landscape.</p>
    </div>

    <div class="space-y-4">
      <?php
      $timeline = [
        ['1842', 'Beginnings as a visita', 'A chapel, convento and school were established while the community was connected to Baclayon.'],
        ['1856', 'A sturdier church rises', 'A larger church was constructed on the eastern side of the plaza as the settlement continued to grow.'],
        ['1861', 'Alburquerque becomes a town', 'The settlement was formally elevated and adopted the name Alburquerque.'],
        ['1869', 'Parish inauguration', 'Sta. Monica was formally inaugurated as a parish, serving the growing local Catholic community.'],
        ['1885', 'The stone church takes shape', 'Work on the present church structure advanced under Fr. Manuel Muro using the established three-aisled plan.'],
        ['1932', 'Ceiling paintings completed', 'Ray Francia painted the church ceiling, adding one of the interior features still associated with the church today.'],
        ['2013', 'Cultural recognition', 'The National Museum of the Philippines declared the church an Important Cultural Property.'],
        ['2014', 'Historical marker unveiled', 'A historical marker recognizing the church story was unveiled at the facade.'],
      ];
      foreach ($timeline as $i => $item):
      ?>
      <div class="group grid grid-cols-[82px_1fr] sm:grid-cols-[110px_1fr] gap-5 rounded-2xl bg-white border border-stonewarm-200 p-5 sm:p-6 hover:border-parish-200 hover:shadow-soft transition">
        <div>
          <div class="text-2xl sm:text-3xl font-bold text-gold-600"><?= $item[0] ?></div>
          <div class="w-8 h-0.5 bg-gold-200 mt-3 group-hover:w-12 transition-all"></div>
        </div>
        <div>
          <h3 class="font-bold text-lg text-parish-900"><?= $item[1] ?></h3>
          <p class="text-sm text-gray-600 leading-relaxed mt-1.5"><?= $item[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="bg-parish-900 text-white overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 grid lg:grid-cols-2 gap-12 items-center">
    <div class="heritage-photo rounded-[2rem] overflow-hidden aspect-[4/3] order-2 lg:order-1">
      <img src="<?= $interior_img ?>" alt="Interior nave of Santa Monica Parish Church" class="w-full h-full object-cover" loading="lazy">
    </div>
    <div class="order-1 lg:order-2">
      <div class="heritage-kicker text-gold-300">Architecture &amp; sacred art</div>
      <h2 class="text-4xl sm:text-5xl font-bold tracking-tight mt-4">Look closer and the building tells its own story.</h2>
      <div class="mt-7 grid sm:grid-cols-2 gap-4">
        <div class="rounded-2xl bg-white/7 border border-white/10 p-5">
          <i class="ph ph-columns text-2xl text-gold-300"></i>
          <h3 class="font-semibold mt-3">Coral-stone church</h3>
          <p class="text-sm text-white/60 mt-1.5 leading-relaxed">The church complex is known for its masonry, traditional Bohol church form and commanding presence beside the open grounds.</p>
        </div>
        <div class="rounded-2xl bg-white/7 border border-white/10 p-5">
          <i class="ph ph-bridge text-2xl text-gold-300"></i>
          <h3 class="font-semibold mt-3">Church-convento arcade</h3>
          <p class="text-sm text-white/60 mt-1.5 leading-relaxed">A graceful row of arches visually connects the church and convento and is one of the complex's distinctive features.</p>
        </div>
        <div class="rounded-2xl bg-white/7 border border-white/10 p-5">
          <i class="ph ph-paint-brush text-2xl text-gold-300"></i>
          <h3 class="font-semibold mt-3">Painted ceiling</h3>
          <p class="text-sm text-white/60 mt-1.5 leading-relaxed">The interior ceiling was painted by Ray Francia in 1932, enriching the devotional atmosphere of the nave.</p>
        </div>
        <div class="rounded-2xl bg-white/7 border border-white/10 p-5">
          <i class="ph ph-bell text-2xl text-gold-300"></i>
          <h3 class="font-semibold mt-3">Bell tower &amp; parish grounds</h3>
          <p class="text-sm text-white/60 mt-1.5 leading-relaxed">The tower, broad plaza and surrounding parish buildings create a memorable heritage ensemble in Poblacion.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
  <div class="grid lg:grid-cols-[1.1fr_.9fr] gap-10 items-center rounded-[2rem] bg-white border border-stonewarm-200 p-7 sm:p-10 lg:p-12 shadow-soft">
    <div>
      <div class="heritage-kicker text-gold-600">A living parish</div>
      <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-3">Heritage remains meaningful because the community is still here.</h2>
      <p class="mt-4 text-gray-600 leading-relaxed">Sta. Monica Parish remains an active Catholic community where families celebrate Mass, receive the sacraments, gather for parish activities, serve through ministries and preserve records that connect generations.</p>
      <div class="mt-7 flex flex-wrap gap-3">
        <a href="<?= site_url('mass-schedule') ?>" class="px-5 py-3 rounded-full bg-parish-800 text-white font-semibold hover:bg-parish-900 transition">Join Us for Mass</a>
        <a href="<?= site_url('ministries') ?>" class="px-5 py-3 rounded-full border border-parish-200 text-parish-800 font-semibold hover:bg-parish-50 transition">Explore Ministries</a>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div class="rounded-2xl bg-parish-50 p-5">
        <div class="text-3xl font-bold text-parish-800">1842</div>
        <div class="text-sm text-gray-600 mt-1">parish roots</div>
      </div>
      <div class="rounded-2xl bg-gold-50 p-5">
        <div class="text-3xl font-bold text-gold-700">1869</div>
        <div class="text-sm text-gray-600 mt-1">formal parish inauguration</div>
      </div>
      <div class="rounded-2xl bg-stonewarm-100 p-5">
        <div class="text-3xl font-bold text-parish-800">1932</div>
        <div class="text-sm text-gray-600 mt-1">ceiling paintings</div>
      </div>
      <div class="rounded-2xl bg-parish-900 text-white p-5">
        <div class="text-3xl font-bold text-gold-200">2013</div>
        <div class="text-sm text-white/65 mt-1">Important Cultural Property</div>
      </div>
    </div>
  </div>

  <div class="mt-8 text-xs text-gray-400 leading-relaxed">
    Historical summary prepared from published heritage references, including the National Museum of the Philippines and the documented history of Alburquerque Church. Historical details may be further refined by the parish using its own archives.
  </div>
</section>
