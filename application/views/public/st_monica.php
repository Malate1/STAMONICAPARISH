<?php
$st_monica_img = 'https://commons.wikimedia.org/wiki/Special:FilePath/Andrea%20del%20Verrocchio%20-%20Saint%20Monica%20-%20WGA24998.jpg';

$timeline = [
  ['331','Born in Tagaste','Natawo sa Tagaste',
   'Monica was born into a Christian family in Tagaste, in present-day Algeria. Her early formation planted the faith that would guide her through marriage and motherhood.',
   'Si Monica natawo sa usa ka Kristohanong pamilya sa Tagaste, sa karon nga Algeria. Ang iyang sayong pagkaporma sa pagtuo maoy nahimong giya niya sa kaminyoon ug pagka-inahan.'],
  ['Marriage','A wife of patient faith','Asawa nga mapailubon sa pagtuo',
   'She married Patricius, who was not yet a Christian. Through prayer, patience, and example, Monica helped lead her husband toward the Christian faith.',
   'Naminyo siya kang Patricius, nga dili pa Kristohanon. Pinaagi sa pag-ampo, pagpailub, ug maayong ehemplo, nakatabang si Monica sa pagdala sa iyang bana ngadto sa pagtuong Kristohanon.'],
  ['3 children','Mother of a family','Inahan sa usa ka pamilya',
   'Monica and Patricius had three children: Augustine, Navigius, and a daughter whose name is not known to us. Augustine would later become bishop of Hippo and one of the great teachers of the Church.',
   'Si Monica ug Patricius adunay tulo ka anak: Augustine, Navigius, ug usa ka anak nga babaye kansang ngalan wala na nato mahibaloi. Sa ulahi, si Augustine nahimong obispo sa Hippo ug usa sa bantugang magtutudlo sa Simbahan.'],
  ['Years of prayer','She never gave up on Augustine','Wala siya mobiya kang Augustine',
   'When Augustine wandered far from the faith of his childhood, Monica continued praying for him. Her persistent prayer and hope became one of the best-known examples of a mother accompanying a child through difficulty.',
   'Sa dihang nalayo si Augustine sa pagtuo sa iyang pagkabata, nagpadayon si Monica sa pag-ampo alang kaniya. Ang iyang paglahutay ug paglaum nahimong usa sa labing nailhang ehemplo sa usa ka inahan nga nag-uban sa anak taliwala sa kalisdanan.'],
  ['387','Augustine is baptized','Gibunyagan si Augustine',
   'Monica lived to see Augustine embrace the Catholic faith and receive baptism from St. Ambrose in Milan in 387.',
   'Nakita ni Monica nga gidawat ni Augustine ang pagtuong Katoliko ug gibunyagan ni San Ambrosio sa Milan niadtong 387.'],
  ['Ostia','Her final journey','Ang iyang kataposang panaw',
   'While preparing to return to North Africa, Monica became ill at Ostia near Rome. After a profound final conversation with Augustine about eternal life, she died there in 387 at about fifty-six years of age.',
   'Samtang nangandam sa pagbalik sa North Africa, nasakit si Monica sa Ostia duol sa Roma. Human sa lawom nga kataposang panag-istoryahanay kang Augustine mahitungod sa kinabuhing walay kataposan, namatay siya didto niadtong 387 sa edad nga mga kalim-an ug unom.']
];
?>
<section x-data="{ lang: localStorage.getItem('stmonica-life-lang') || 'en' }"
         x-init="$watch('lang', value => localStorage.setItem('stmonica-life-lang', value))">

  <div class="relative overflow-hidden bg-parish-900">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_15%,rgba(200,144,29,.18),transparent_32%)]"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
      <div class="grid lg:grid-cols-[1.05fr_.72fr] gap-10 lg:gap-16 items-center">
        <div class="text-white">
          <div class="heritage-kicker text-gold-300">Our Patroness</div>
          <h1 class="text-4xl sm:text-6xl font-bold tracking-[-.04em] leading-[1.02] mt-3">
            <span x-show="lang==='en'">The Life of St. Monica</span>
            <span x-show="lang==='ceb'" x-cloak>Ang Kinabuhi ni Santa Monica</span>
          </h1>
          <p class="text-white/70 text-lg sm:text-xl leading-relaxed mt-6 max-w-2xl">
            <span x-show="lang==='en'">A story of patient love, persistent prayer, family trials, and unwavering trust in God.</span>
            <span x-show="lang==='ceb'" x-cloak>Usa ka sugilanon sa mapailubon nga gugma, makanunayong pag-ampo, mga pagsulay sa pamilya, ug lig-on nga pagsalig sa Dios.</span>
          </p>
          <div class="mt-7 inline-flex rounded-full bg-white/10 border border-white/15 p-1">
            <button @click="lang='en'" class="px-4 py-2 rounded-full text-sm font-semibold transition" :class="lang==='en' ? 'bg-white text-parish-900' : 'text-white/70'">English</button>
            <button @click="lang='ceb'" class="px-4 py-2 rounded-full text-sm font-semibold transition" :class="lang==='ceb' ? 'bg-white text-parish-900' : 'text-white/70'">Bisaya / Cebuano</button>
          </div>
          <div class="mt-9 grid grid-cols-3 gap-3 max-w-2xl">
            <div class="rounded-2xl bg-white/5 border border-white/10 p-4"><div class="text-2xl font-bold text-gold-200">331</div><div class="text-xs text-white/55 mt-1">Tagaste</div></div>
            <div class="rounded-2xl bg-white/5 border border-white/10 p-4"><div class="text-2xl font-bold text-gold-200">387</div><div class="text-xs text-white/55 mt-1">Ostia</div></div>
            <div class="rounded-2xl bg-white/5 border border-white/10 p-4"><div class="text-2xl font-bold text-gold-200">Aug 27</div><div class="text-xs text-white/55 mt-1"><span x-show="lang==='en'">Feast Day</span><span x-show="lang==='ceb'" x-cloak>Adlaw sa Pista</span></div></div>
          </div>
        </div>

        <figure class="max-w-sm mx-auto lg:ml-auto">
          <div class="rounded-[2rem] overflow-hidden border border-gold-300/20 shadow-2xl bg-black/20 aspect-[4/5]">
            <img src="<?= $st_monica_img ?>" alt="Historic painting of Saint Monica" class="w-full h-full object-cover" loading="eager">
          </div>
          <figcaption class="text-[11px] text-white/40 mt-3 text-center">Public-domain artwork of St. Monica via Wikimedia Commons.</figcaption>
        </figure>
      </div>
    </div>
  </div>

  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid lg:grid-cols-[.72fr_1.28fr] gap-10">
      <div>
        <div class="heritage-kicker text-gold-600"><span x-show="lang==='en'">Who was St. Monica?</span><span x-show="lang==='ceb'" x-cloak>Kinsa si Santa Monica?</span></div>
        <div class="heritage-rule mt-3"></div>
        <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-5 leading-tight">
          <span x-show="lang==='en'">A mother remembered for faithful love and prayer.</span>
          <span x-show="lang==='ceb'" x-cloak>Usa ka inahan nga gihandom tungod sa matinud-anong gugma ug pag-ampo.</span>
        </h2>
      </div>
      <div class="text-gray-700 leading-relaxed text-[1.02rem]">
        <div x-show="lang==='en'">
          <p>St. Monica is best known as the mother of St. Augustine of Hippo, but her own life is a powerful Christian witness. She lived through the ordinary but difficult responsibilities of marriage, motherhood, family conflict, and concern for her children.</p>
          <p class="mt-4">Her holiness grew through years of prayer, patience, sacrifice, forgiveness, and trust in God. Her story has become a source of hope for parents and families who continue praying when change seems slow or impossible.</p>
        </div>
        <div x-show="lang==='ceb'" x-cloak>
          <p>Si Santa Monica labing nailhan isip inahan ni San Augustine sa Hippo, apan ang iyang kaugalingong kinabuhi usa usab ka kusgan nga Kristohanong pagpamatuod. Nakaagi siya sa lisod nga mga responsibilidad sa kaminyoon, pagka-inahan, kalisod sa pamilya, ug kabalaka alang sa mga anak.</p>
          <p class="mt-4">Ang iyang pagkabalaan mitubo pinaagi sa daghang tuig sa pag-ampo, pagpailub, sakripisyo, pagpasaylo, ug pagsalig sa Dios. Ang iyang sugilanon nahimong tinubdan sa paglaum alang sa mga ginikanan ug pamilya nga nagpadayon sa pag-ampo bisan hinay o daw imposible ang kausaban.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-white border-y border-stonewarm-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="text-center mb-10">
        <div class="heritage-kicker text-gold-600"><span x-show="lang==='en'">Her journey</span><span x-show="lang==='ceb'" x-cloak>Ang iyang panaw</span></div>
        <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-2"><span x-show="lang==='en'">A life marked by perseverance</span><span x-show="lang==='ceb'" x-cloak>Kinabuhi nga gimarkahan sa paglahutay</span></h2>
      </div>

      <div class="space-y-4">
        <?php foreach ($timeline as $item): ?>
          <article class="grid sm:grid-cols-[120px_1fr] gap-5 rounded-3xl border border-stonewarm-200 bg-stonewarm-50/35 p-5 sm:p-7 hover:border-parish-200 hover:shadow-soft transition">
            <div><div class="text-xl font-bold text-gold-600"><?= html_escape($item[0]) ?></div><div class="w-10 h-0.5 bg-gold-200 mt-3"></div></div>
            <div>
              <h3 class="text-xl font-bold text-parish-900"><span x-show="lang==='en'"><?= html_escape($item[1]) ?></span><span x-show="lang==='ceb'" x-cloak><?= html_escape($item[2]) ?></span></h3>
              <p class="text-gray-600 leading-relaxed mt-2"><span x-show="lang==='en'"><?= html_escape($item[3]) ?></span><span x-show="lang==='ceb'" x-cloak><?= html_escape($item[4]) ?></span></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid lg:grid-cols-2 gap-6">
      <div class="rounded-[2rem] bg-parish-900 text-white p-7 sm:p-9 shadow-heritage">
        <div class="heritage-kicker text-gold-300"><span x-show="lang==='en'">Her spiritual legacy</span><span x-show="lang==='ceb'" x-cloak>Ang iyang espirituhanong kabilin</span></div>
        <h2 class="text-3xl font-bold mt-3"><span x-show="lang==='en'">Prayer without giving up.</span><span x-show="lang==='ceb'" x-cloak>Pag-ampo nga dili mohunong.</span></h2>
        <p class="text-white/70 leading-relaxed mt-5" x-show="lang==='en'">The Church remembers Monica as a model of Christian motherhood and persevering prayer. Her life reminds us that love may require patience, conversion cannot be forced, and faithfulness matters even when results are not immediate.</p>
        <p class="text-white/70 leading-relaxed mt-5" x-show="lang==='ceb'" x-cloak>Gihandom sa Simbahan si Monica isip ehemplo sa Kristohanong pagka-inahan ug makanunayong pag-ampo. Ang iyang kinabuhi nagpahinumdom nga ang gugma nagkinahanglan og pagpailub ug nga importante ang pagkamatinud-anon bisan dili dayon makita ang resulta.</p>
      </div>

      <div class="rounded-[2rem] bg-gold-50 border border-gold-100 p-7 sm:p-9">
        <div class="heritage-kicker text-gold-700"><span x-show="lang==='en'">St. Monica and our parish</span><span x-show="lang==='ceb'" x-cloak>Si Santa Monica ug ang atong parokya</span></div>
        <h2 class="text-3xl font-bold text-parish-900 mt-3"><span x-show="lang==='en'">A patroness for families and hope.</span><span x-show="lang==='ceb'" x-cloak>Patrona sa pamilya ug paglaum.</span></h2>
        <p class="text-gray-700 leading-relaxed mt-5" x-show="lang==='en'">Her life gives Sta. Monica Parish a natural spiritual identity: a community that prays for families, accompanies those who struggle, welcomes those returning to faith, and remains hopeful through difficulty.</p>
        <p class="text-gray-700 leading-relaxed mt-5" x-show="lang==='ceb'" x-cloak>Ang iyang kinabuhi naghatag sa Sta. Monica Parish og espirituhanong identidad: usa ka komunidad nga nag-ampo alang sa pamilya, nag-uban sa mga naglisod, nagdawat sa mga mibalik sa pagtuo, ug nagpabiling malaumon taliwala sa kalisdanan.</p>
        <div class="mt-7 flex flex-wrap gap-3">
          <a href="<?= site_url('prayers') ?>" class="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-parish-800 text-white font-semibold hover:bg-parish-900 transition"><i class="ph ph-hands-praying"></i> <span x-show="lang==='en'">Prayers &amp; Novena</span><span x-show="lang==='ceb'" x-cloak>Mga Pag-ampo &amp; Nobena</span></a>
          <a href="<?= site_url('about') ?>" class="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-white border border-gold-200 text-parish-800 font-semibold hover:bg-gold-100 transition"><i class="ph ph-landmark"></i> <span x-show="lang==='en'">Parish Heritage</span><span x-show="lang==='ceb'" x-cloak>Kasaysayan sa Parokya</span></a>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-stonewarm-100/70 border-t border-stonewarm-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-9 text-sm text-gray-600">
      <div class="font-semibold text-parish-900"><span x-show="lang==='en'">Historical references</span><span x-show="lang==='ceb'" x-cloak>Mga basihanan sa kasaysayan</span></div>
      <p class="mt-1"><span x-show="lang==='en'">This overview follows Vatican reflections on St. Monica and historical materials from the Augustinian tradition.</span><span x-show="lang==='ceb'" x-cloak>Kining pagsaysay nagsunod sa mga pagpamalandong sa Vatican bahin kang Santa Monica ug sa mga kasaysayang materyal sa tradisyong Augustinian.</span></p>
      <div class="mt-3 flex flex-wrap gap-4">
        <a href="https://www.vatican.va/content/benedict-xvi/en/angelus/2006/documents/hf_ben-xvi_ang_20060827.html" target="_blank" rel="noopener" class="text-parish-700 font-semibold hover:text-parish-900">Vatican reflection ↗</a>
        <a href="https://augustinian.org/august-27/" target="_blank" rel="noopener" class="text-parish-700 font-semibold hover:text-parish-900">Augustinian biography ↗</a>
      </div>
    </div>
  </div>
</section>
