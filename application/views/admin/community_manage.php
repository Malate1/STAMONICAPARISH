<?php
$day_names = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$chapel_map = [];
foreach ($chapels as $c) $chapel_map[$c['id']] = $c;
$cluster_map = [];
foreach ($clusters as $c) $cluster_map[$c['id']] = $c;

$person_photo = static function($path){
    return !empty($path) ? base_url($path) : null;
};
?>

<?php if (!$schema_ready): ?>
<div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
  <div class="flex gap-3">
    <i class="ph ph-warning-circle text-xl text-amber-700 mt-0.5"></i>
    <div>
      <div class="font-semibold text-amber-900">Chapels & GSK database upgrade required</div>
      <p class="text-sm text-amber-800/80 mt-1">Run <code class="font-mono">database/migrations/20260924_chapels_gsk_structure.sql</code> in phpMyAdmin, then reload this page.</p>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4 mb-6">
  <div>
    <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Community structure</div>
    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Chapels & GSK</h1>
    <p class="text-gray-500 text-sm mt-1 max-w-3xl">Manage chapel profiles, chapel Mass schedules, Gagmayng Simbahanong Katilingban (GSK) clusters, and the parish → chapel → cluster leadership directory.</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <button onclick="newOfficial('parish')" class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50"><i class="ph ph-user-plus mr-1"></i> Parish Official</button>
    <button onclick="newChapel()" class="px-4 py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold"><i class="ph ph-church mr-1"></i> Add Chapel</button>
  </div>
</div>

<section class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5 mb-7">
  <div class="flex gap-3">
    <i class="ph ph-info text-blue-700 text-xl mt-0.5"></i>
    <div class="text-sm text-blue-950/80 leading-relaxed">
      <strong class="text-blue-950">Flexible GSK structure.</strong>
      Position titles and committee areas are free-text so the parish can use its actual organization. Typical BEC/GSK structures may include a council of leaders and areas such as Worship/Liturgy, Education/Formation, Service/Social Action, Temporalities/Finance and Youth, with smaller neighborhood/family-group clusters under a chapel. Nothing is forced into a fixed set of titles.
    </div>
  </div>
</section>

<section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6 mb-7">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
      <h2 class="font-semibold text-gray-900">Parish Officials</h2>
      <p class="text-xs text-gray-400 mt-1">Parish-level lay leadership and coordinators above the chapel/GSK structure.</p>
    </div>
    <button onclick="newOfficial('parish')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-parish-50 text-parish-700 text-xs font-semibold hover:bg-parish-100"><i class="ph ph-plus"></i> Add Official</button>
  </div>

  <?php if (!$schema_ready || empty($parish_officials)): ?>
    <div class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-400">No parish officials added yet.</div>
  <?php else: ?>
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
      <?php foreach ($parish_officials as $o): $photo=$person_photo($o['photo']); ?>
      <article class="rounded-2xl border border-gray-100 p-4 flex gap-3">
        <div class="w-12 h-12 rounded-xl bg-parish-50 overflow-hidden flex-shrink-0 flex items-center justify-center text-parish-700 font-bold">
          <?php if ($photo): ?><img src="<?= html_escape($photo) ?>" alt="" class="w-full h-full object-cover"><?php else: ?><i class="ph ph-user text-xl"></i><?php endif; ?>
        </div>
        <div class="min-w-0 flex-1">
          <div class="font-semibold text-sm text-gray-800"><?= html_escape($o['full_name']) ?></div>
          <div class="text-xs text-parish-700 mt-0.5"><?= html_escape($o['position_title']) ?></div>
          <?php if ($o['committee_area']): ?><div class="text-[11px] text-gray-400 mt-1"><?= html_escape($o['committee_area']) ?></div><?php endif; ?>
        </div>
        <div class="flex gap-1">
          <button onclick="editOfficial(<?= (int)$o['id'] ?>)" title="Edit" class="w-8 h-8 rounded-lg border border-gray-100 text-parish-700 hover:bg-parish-50"><i class="ph ph-pencil-simple"></i></button>
          <button onclick="deleteOfficial(<?= (int)$o['id'] ?>)" title="Delete" class="w-8 h-8 rounded-lg border border-red-100 text-red-600 hover:bg-red-50"><i class="ph ph-trash"></i></button>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<div class="flex items-center justify-between gap-4 mb-4">
  <div>
    <h2 class="text-xl font-semibold text-gray-900">Chapels</h2>
    <p class="text-xs text-gray-400 mt-1">Each chapel can have its own Mass schedule, officials and GSK clusters.</p>
  </div>
</div>

<?php if (!$schema_ready || empty($chapels)): ?>
  <section class="rounded-2xl bg-white border border-gray-100 p-10 text-center">
    <i class="ph ph-church text-4xl text-gray-300"></i>
    <h3 class="font-semibold text-gray-800 mt-3">No chapels added yet.</h3>
    <p class="text-sm text-gray-400 mt-1">Create the first chapel, then add its Mass schedule, officials and clusters.</p>
    <button onclick="newChapel()" class="mt-4 px-4 py-2.5 rounded-xl bg-parish-700 text-white text-sm font-semibold"><i class="ph ph-plus mr-1"></i> Add Chapel</button>
  </section>
<?php else: ?>
  <div class="space-y-6">
    <?php foreach ($chapels as $chapel):
      $cover = !empty($chapel['cover_image']) ? base_url($chapel['cover_image']) : null;
      $chapel_clusters = array_values(array_filter($clusters, fn($c)=>(int)$c['chapel_id']===(int)$chapel['id']));
    ?>
    <section class="bg-white rounded-[1.5rem] border border-gray-100 overflow-hidden">
      <div class="grid lg:grid-cols-[280px_1fr]">
        <div class="relative min-h-[220px] bg-gradient-to-br from-parish-800 to-parish-950 overflow-hidden">
          <?php if ($cover): ?><img src="<?= html_escape($cover) ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-75"><?php endif; ?>
          <div class="absolute inset-0 bg-gradient-to-t from-parish-950/90 via-parish-900/30 to-transparent"></div>
          <div class="absolute left-5 right-5 bottom-5 text-white">
            <?php if ($chapel['patron_saint']): ?><div class="text-[10px] uppercase tracking-wider text-gold-200 font-bold"><?= html_escape($chapel['patron_saint']) ?></div><?php endif; ?>
            <h3 class="text-xl font-bold mt-1"><?= html_escape($chapel['name']) ?></h3>
            <div class="text-xs text-white/60 mt-2"><?= html_escape($chapel['barangay'] ?: $chapel['address'] ?: 'Alburquerque, Bohol') ?></div>
          </div>
        </div>

        <div class="p-5 sm:p-6">
          <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4">
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $chapel['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>"><?= $chapel['is_active'] ? 'Public' : 'Hidden' ?></span>
                <?php if ($chapel['feast_date']): ?><span class="text-xs text-gray-400"><i class="ph ph-calendar-star mr-1"></i>Fiesta: <?= html_escape($chapel['feast_date']) ?></span><?php endif; ?>
              </div>
              <?php if ($chapel['description']): ?><p class="text-sm text-gray-500 mt-3 max-w-3xl line-clamp-2"><?= html_escape(strip_tags($chapel['description'])) ?></p><?php endif; ?>
            </div>
            <div class="flex flex-wrap gap-2">
              <button onclick="newMass(<?= (int)$chapel['id'] ?>)" class="px-3 py-2 rounded-lg bg-parish-50 text-parish-700 text-xs font-semibold"><i class="ph ph-clock mr-1"></i> Mass</button>
              <button onclick="newCluster(<?= (int)$chapel['id'] ?>)" class="px-3 py-2 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold"><i class="ph ph-tree-structure mr-1"></i> GSK Cluster</button>
              <button onclick="newOfficial('chapel',<?= (int)$chapel['id'] ?>)" class="px-3 py-2 rounded-lg bg-gold-50 text-gold-700 text-xs font-semibold"><i class="ph ph-user-plus mr-1"></i> Official</button>
              <button onclick="editChapel(<?= (int)$chapel['id'] ?>)" class="w-9 h-9 rounded-lg border border-gray-200 text-gray-600"><i class="ph ph-pencil-simple"></i></button>
              <button onclick="deleteChapel(<?= (int)$chapel['id'] ?>)" class="w-9 h-9 rounded-lg border border-red-100 text-red-600"><i class="ph ph-trash"></i></button>
            </div>
          </div>

          <div class="grid xl:grid-cols-3 gap-4 mt-5">
            <div class="rounded-xl bg-gray-50/70 border border-gray-100 p-4">
              <div class="flex items-center justify-between mb-3">
                <div class="text-xs uppercase tracking-wider text-gray-400 font-bold">Mass Schedule</div>
                <span class="text-[10px] text-gray-400"><?= count($chapel['mass_schedules']) ?> item(s)</span>
              </div>
              <?php if (empty($chapel['mass_schedules'])): ?>
                <p class="text-xs text-gray-400">No chapel Mass schedule yet.</p>
              <?php else: ?>
                <div class="space-y-2">
                  <?php foreach ($chapel['mass_schedules'] as $m): ?>
                    <div class="rounded-lg bg-white border border-gray-100 p-3 flex items-start gap-2">
                      <div class="min-w-0 flex-1">
                        <div class="text-xs font-semibold text-gray-700"><?= $day_names[(int)$m['day_of_week']] ?> · <?= date('g:i A',strtotime($m['mass_time'])) ?></div>
                        <div class="text-[11px] text-gray-400 mt-0.5"><?= html_escape($m['title']) ?><?= $m['recurrence_note'] ? ' · '.html_escape($m['recurrence_note']) : '' ?></div>
                      </div>
                      <button onclick="editMass(<?= (int)$m['id'] ?>)" class="text-parish-700"><i class="ph ph-pencil-simple"></i></button>
                      <button onclick="deleteMass(<?= (int)$m['id'] ?>)" class="text-red-500"><i class="ph ph-trash"></i></button>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="rounded-xl bg-gray-50/70 border border-gray-100 p-4">
              <div class="flex items-center justify-between mb-3">
                <div class="text-xs uppercase tracking-wider text-gray-400 font-bold">Chapel Officials</div>
                <span class="text-[10px] text-gray-400"><?= count($chapel['officials']) ?> official(s)</span>
              </div>
              <?php if (empty($chapel['officials'])): ?>
                <p class="text-xs text-gray-400">No chapel officials yet.</p>
              <?php else: ?>
                <div class="space-y-2">
                  <?php foreach ($chapel['officials'] as $o): ?>
                    <div class="rounded-lg bg-white border border-gray-100 p-3 flex items-start gap-2">
                      <div class="min-w-0 flex-1">
                        <div class="text-xs font-semibold text-gray-700"><?= html_escape($o['full_name']) ?></div>
                        <div class="text-[11px] text-gray-400"><?= html_escape($o['position_title']) ?><?= $o['committee_area'] ? ' · '.html_escape($o['committee_area']) : '' ?></div>
                      </div>
                      <button onclick="editOfficial(<?= (int)$o['id'] ?>)" class="text-parish-700"><i class="ph ph-pencil-simple"></i></button>
                      <button onclick="deleteOfficial(<?= (int)$o['id'] ?>)" class="text-red-500"><i class="ph ph-trash"></i></button>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="rounded-xl bg-gray-50/70 border border-gray-100 p-4">
              <div class="flex items-center justify-between mb-3">
                <div class="text-xs uppercase tracking-wider text-gray-400 font-bold">GSK Clusters</div>
                <span class="text-[10px] text-gray-400"><?= count($chapel_clusters) ?> cluster(s)</span>
              </div>
              <?php if (empty($chapel_clusters)): ?>
                <p class="text-xs text-gray-400">No GSK clusters yet.</p>
              <?php else: ?>
                <div class="space-y-2">
                  <?php foreach ($chapel_clusters as $cluster): ?>
                    <div class="rounded-lg bg-white border border-gray-100 p-3">
                      <div class="flex items-start gap-2">
                        <div class="min-w-0 flex-1">
                          <div class="text-xs font-semibold text-gray-700"><?= html_escape($cluster['name']) ?></div>
                          <div class="text-[11px] text-gray-400"><?= html_escape($cluster['coverage_area'] ?: $cluster['code'] ?: 'GSK cluster') ?></div>
                        </div>
                        <button onclick="editCluster(<?= (int)$cluster['id'] ?>)" class="text-parish-700"><i class="ph ph-pencil-simple"></i></button>
                        <button onclick="deleteCluster(<?= (int)$cluster['id'] ?>)" class="text-red-500"><i class="ph ph-trash"></i></button>
                      </div>
                      <button onclick="newOfficial('cluster',<?= (int)$chapel['id'] ?>,<?= (int)$cluster['id'] ?>)" class="mt-2 text-[11px] font-semibold text-parish-700 hover:underline"><i class="ph ph-user-plus mr-1"></i>Add cluster official</button>
                      <?php if (!empty($cluster['officials'])): ?>
                        <div class="mt-2 pt-2 border-t border-gray-100 space-y-1">
                          <?php foreach ($cluster['officials'] as $o): ?>
                            <div class="text-[11px] text-gray-500 flex items-center justify-between gap-2">
                              <span><strong class="text-gray-700"><?= html_escape($o['full_name']) ?></strong> · <?= html_escape($o['position_title']) ?></span>
                              <button onclick="editOfficial(<?= (int)$o['id'] ?>)" class="text-parish-700"><i class="ph ph-pencil-simple"></i></button>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Chapel Modal -->
<div id="chapel-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-3 sm:p-5">
  <div class="absolute inset-0 bg-black/55" onclick="closeModal('chapel-modal')"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[94vh] flex flex-col overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between"><h3 id="chapel-modal-title" class="font-semibold text-gray-900">Add Chapel</h3><button onclick="closeModal('chapel-modal')" class="text-gray-400"><i class="ph ph-x text-xl"></i></button></div>
    <form id="chapel-form" enctype="multipart/form-data" class="flex-1 min-h-0 flex flex-col">
      <div class="overflow-y-auto p-5 sm:p-6 space-y-4">
        <input type="hidden" name="id" id="ch-id">
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2"><label class="text-xs font-medium text-gray-500">Chapel Name</label><input required name="name" id="ch-name" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
          <div><label class="text-xs font-medium text-gray-500">Patron Saint / Title</label><input name="patron_saint" id="ch-patron" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
          <div><label class="text-xs font-medium text-gray-500">Feast / Fiesta Date</label><input name="feast_date" id="ch-feast" placeholder="e.g. May 15" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
          <div class="sm:col-span-2"><label class="text-xs font-medium text-gray-500">Description</label><textarea name="description" id="ch-description" rows="4" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></textarea></div>
          <div><label class="text-xs font-medium text-gray-500">Barangay / Area</label><input name="barangay" id="ch-barangay" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
          <div><label class="text-xs font-medium text-gray-500">Contact Number</label><input name="contact_number" id="ch-contact" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
          <div class="sm:col-span-2"><label class="text-xs font-medium text-gray-500">Address</label><input name="address" id="ch-address" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
          <div class="sm:col-span-2"><label class="text-xs font-medium text-gray-500">Location Notes / Directions</label><input name="location_notes" id="ch-location-notes" placeholder="Landmarks or directions" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
          <div><label class="text-xs font-medium text-gray-500">Latitude <span class="text-gray-300">(optional)</span></label><input name="latitude" id="ch-lat" inputmode="decimal" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
          <div><label class="text-xs font-medium text-gray-500">Longitude <span class="text-gray-300">(optional)</span></label><input name="longitude" id="ch-lng" inputmode="decimal" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
          <div class="sm:col-span-2"><label class="text-xs font-medium text-gray-500">Chapel Photo</label><input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp" class="mt-1.5 w-full text-sm"></div>
          <div><label class="text-xs font-medium text-gray-500">Display Order</label><input type="number" min="0" name="display_order" id="ch-order" value="0" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
          <label class="flex items-center gap-2 text-sm text-gray-600 sm:self-end sm:pb-3"><input type="checkbox" name="is_active" id="ch-active" value="1" checked class="rounded text-parish-700"> Active / public</label>
        </div>
      </div>
      <div class="px-5 sm:px-6 py-4 border-t border-gray-100 flex justify-end gap-3"><button type="button" onclick="closeModal('chapel-modal')" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm">Cancel</button><button class="px-5 py-2.5 rounded-xl bg-parish-700 text-white text-sm font-semibold"><i class="ph ph-floppy-disk mr-1"></i> Save Chapel</button></div>
    </form>
  </div>
</div>

<!-- Mass Modal -->
<div id="mass-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-3">
  <div class="absolute inset-0 bg-black/55" onclick="closeModal('mass-modal')"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between"><h3 id="mass-modal-title" class="font-semibold text-gray-900">Add Chapel Mass</h3><button onclick="closeModal('mass-modal')"><i class="ph ph-x"></i></button></div>
    <form id="mass-form" class="p-5 space-y-4">
      <input type="hidden" name="id" id="m-id">
      <div><label class="text-xs font-medium text-gray-500">Chapel</label><select required name="chapel_id" id="m-chapel" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white"><?php foreach($chapels as $c): ?><option value="<?= (int)$c['id'] ?>"><?= html_escape($c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="text-xs font-medium text-gray-500">Day</label><select name="day_of_week" id="m-day" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white"><?php foreach($day_names as $i=>$d): ?><option value="<?= $i ?>"><?= $d ?></option><?php endforeach; ?></select></div>
        <div><label class="text-xs font-medium text-gray-500">Mass Time</label><input required type="time" name="mass_time" id="m-time" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
      </div>
      <div><label class="text-xs font-medium text-gray-500">Title</label><input name="title" id="m-title" value="Holy Mass" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
      <div class="grid sm:grid-cols-2 gap-4"><div><label class="text-xs font-medium text-gray-500">Language</label><input name="language" id="m-language" placeholder="e.g. Cebuano" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div><div><label class="text-xs font-medium text-gray-500">Display Order</label><input type="number" min="0" name="display_order" id="m-order" value="0" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div></div>
      <div><label class="text-xs font-medium text-gray-500">Recurrence</label><input name="recurrence_note" id="m-recurrence" placeholder="e.g. Every 2nd Sunday / Monthly" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"><p class="text-[10px] text-gray-400 mt-1">Use this when the Mass is not every week.</p></div>
      <div><label class="text-xs font-medium text-gray-500">Notes</label><input name="notes" id="m-notes" placeholder="Special reminders" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
      <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_active" id="m-active" value="1" checked class="rounded text-parish-700"> Active / visible</label>
      <div class="pt-2 flex justify-end gap-3"><button type="button" onclick="closeModal('mass-modal')" class="px-4 py-2.5 rounded-xl border border-gray-200">Cancel</button><button class="px-5 py-2.5 rounded-xl bg-parish-700 text-white font-semibold">Save Schedule</button></div>
    </form>
  </div>
</div>

<!-- Cluster Modal -->
<div id="cluster-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-3">
  <div class="absolute inset-0 bg-black/55" onclick="closeModal('cluster-modal')"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between"><h3 id="cluster-modal-title" class="font-semibold text-gray-900">Add GSK Cluster</h3><button onclick="closeModal('cluster-modal')"><i class="ph ph-x"></i></button></div>
    <form id="cluster-form" class="p-5 space-y-4">
      <input type="hidden" name="id" id="g-id">
      <div><label class="text-xs font-medium text-gray-500">Chapel</label><select required name="chapel_id" id="g-chapel" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white"><?php foreach($chapels as $c): ?><option value="<?= (int)$c['id'] ?>"><?= html_escape($c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="grid sm:grid-cols-2 gap-4"><div><label class="text-xs font-medium text-gray-500">Cluster Name</label><input required name="name" id="g-name" placeholder="e.g. Cluster 1 / Sitio Riverside" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div><div><label class="text-xs font-medium text-gray-500">Code <span class="text-gray-300">(optional)</span></label><input name="code" id="g-code" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div></div>
      <div><label class="text-xs font-medium text-gray-500">Coverage Area</label><input name="coverage_area" id="g-coverage" placeholder="Purok, sitio, streets or family grouping" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
      <div><label class="text-xs font-medium text-gray-500">Meeting / Prayer Schedule</label><input name="meeting_schedule" id="g-meeting" placeholder="e.g. Friday 7:00 PM, rotating homes" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div>
      <div><label class="text-xs font-medium text-gray-500">Description</label><textarea name="description" id="g-description" rows="3" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></textarea></div>
      <div class="grid sm:grid-cols-2 gap-4"><div><label class="text-xs font-medium text-gray-500">Display Order</label><input type="number" min="0" name="display_order" id="g-order" value="0" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div><label class="flex items-center gap-2 text-sm text-gray-600 sm:self-end sm:pb-3"><input type="checkbox" name="is_active" id="g-active" value="1" checked class="rounded text-parish-700"> Active / public</label></div>
      <div class="pt-2 flex justify-end gap-3"><button type="button" onclick="closeModal('cluster-modal')" class="px-4 py-2.5 rounded-xl border border-gray-200">Cancel</button><button class="px-5 py-2.5 rounded-xl bg-parish-700 text-white font-semibold">Save Cluster</button></div>
    </form>
  </div>
</div>

<!-- Official Modal -->
<div id="official-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-3 sm:p-5">
  <div class="absolute inset-0 bg-black/55" onclick="closeModal('official-modal')"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[94vh] flex flex-col overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-gray-100 flex justify-between"><h3 id="official-modal-title" class="font-semibold text-gray-900">Add Official</h3><button onclick="closeModal('official-modal')"><i class="ph ph-x"></i></button></div>
    <form id="official-form" enctype="multipart/form-data" class="flex-1 min-h-0 flex flex-col">
      <div class="overflow-y-auto p-5 sm:p-6 space-y-4">
        <input type="hidden" name="id" id="o-id">
        <div><label class="text-xs font-medium text-gray-500">Leadership Level</label><select name="scope_type" id="o-scope" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white"><option value="parish">Parish Official</option><option value="chapel">Chapel Official</option><option value="cluster">GSK Cluster Official</option></select></div>
        <div id="o-chapel-wrap" class="hidden"><label class="text-xs font-medium text-gray-500">Chapel</label><select name="chapel_id" id="o-chapel" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white"><?php foreach($chapels as $c): ?><option value="<?= (int)$c['id'] ?>"><?= html_escape($c['name']) ?></option><?php endforeach; ?></select></div>
        <div id="o-cluster-wrap" class="hidden"><label class="text-xs font-medium text-gray-500">GSK Cluster</label><select name="cluster_id" id="o-cluster" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white"></select></div>
        <div class="grid sm:grid-cols-2 gap-4"><div><label class="text-xs font-medium text-gray-500">Full Name</label><input required name="full_name" id="o-name" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div><div><label class="text-xs font-medium text-gray-500">Position / Office</label><input required name="position_title" id="o-position" placeholder="e.g. Chapel President, GSK Leader" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div></div>
        <div><label class="text-xs font-medium text-gray-500">Committee / Pastoral Area</label><input name="committee_area" id="o-committee" placeholder="e.g. Worship, Formation, Service, Finance, Youth" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"><p class="text-[10px] text-gray-400 mt-1">Optional and free-text; use the parish's actual terminology.</p></div>
        <div class="grid sm:grid-cols-2 gap-4"><div><label class="text-xs font-medium text-gray-500">Contact Number</label><input name="contact_number" id="o-contact" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div><div><label class="text-xs font-medium text-gray-500">Email</label><input type="email" name="email" id="o-email" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div></div>
        <div><label class="text-xs font-medium text-gray-500">Short Bio / Responsibility</label><textarea name="bio" id="o-bio" maxlength="500" rows="3" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></textarea></div>
        <div class="grid sm:grid-cols-2 gap-4"><div><label class="text-xs font-medium text-gray-500">Term Start</label><input type="date" name="term_start" id="o-start" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div><div><label class="text-xs font-medium text-gray-500">Term End</label><input type="date" name="term_end" id="o-end" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div></div>
        <div><label class="text-xs font-medium text-gray-500">Photo</label><input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="mt-1.5 w-full text-sm"></div>
        <div class="grid sm:grid-cols-2 gap-4"><div><label class="text-xs font-medium text-gray-500">Display Order</label><input type="number" min="0" name="display_order" id="o-order" value="0" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"></div><label class="flex items-center gap-2 text-sm text-gray-600 sm:self-end sm:pb-3"><input type="checkbox" name="is_active" id="o-active" value="1" checked class="rounded text-parish-700"> Active / public</label></div>
      </div>
      <div class="px-5 sm:px-6 py-4 border-t border-gray-100 flex justify-end gap-3"><button type="button" onclick="closeModal('official-modal')" class="px-4 py-2.5 rounded-xl border border-gray-200">Cancel</button><button class="px-5 py-2.5 rounded-xl bg-parish-700 text-white font-semibold">Save Official</button></div>
    </form>
  </div>
</div>

<script>
var clustersData = <?= json_encode(array_map(function($c){ return ['id'=>(int)$c['id'],'chapel_id'=>(int)$c['chapel_id'],'name'=>$c['name']]; }, $clusters)) ?>;

function openModal(id){ $('#'+id).removeClass('hidden').addClass('flex'); $('body').addClass('overflow-hidden'); }
function closeModal(id){ $('#'+id).addClass('hidden').removeClass('flex'); $('body').removeClass('overflow-hidden'); }
function postForm(url,form,success){
  var data = form instanceof FormData ? form : $(form).serialize();
  var opts={url:url,type:'POST',data:data};
  if(form instanceof FormData){ opts.processData=false; opts.contentType=false; }
  $.ajax(opts).done(function(res){
    if(res.success){ toastr.success(res.message); if(success)success(res); }
    else Swal.fire({icon:'error',title:'Could not save',text:res.message||'Please review the form.',confirmButtonColor:'#235a38'});
  }).fail(function(){ Swal.fire({icon:'error',title:'Request failed',text:'The server did not accept the request.',confirmButtonColor:'#235a38'}); });
}
function confirmDelete(url,title){
  Swal.fire({icon:'warning',title:title,text:'This action cannot be undone.',showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#dc2626'}).then(function(r){
    if(!r.isConfirmed)return;
    $.post(url,function(res){ if(res.success){toastr.success(res.message);setTimeout(function(){location.reload();},450);}else Swal.fire({icon:'error',title:'Could not delete',text:res.message||'Please try again.',confirmButtonColor:'#235a38'}); });
  });
}

function newChapel(){ $('#chapel-form')[0].reset(); $('#ch-id').val(''); $('#ch-order').val(0); $('#ch-active').prop('checked',true); $('#chapel-modal-title').text('Add Chapel'); openModal('chapel-modal'); }
function editChapel(id){ $.get('<?= site_url('admin/community/chapel-get/') ?>'+id,function(res){var d=res.data;newChapel();$('#ch-id').val(d.id);$('#ch-name').val(d.name);$('#ch-patron').val(d.patron_saint);$('#ch-feast').val(d.feast_date);$('#ch-description').val(d.description);$('#ch-barangay').val(d.barangay);$('#ch-contact').val(d.contact_number);$('#ch-address').val(d.address);$('#ch-location-notes').val(d.location_notes);$('#ch-lat').val(d.latitude);$('#ch-lng').val(d.longitude);$('#ch-order').val(d.display_order);$('#ch-active').prop('checked',parseInt(d.is_active,10)===1);$('#chapel-modal-title').text('Edit Chapel');}); }
function deleteChapel(id){confirmDelete('<?= site_url('admin/community/chapel-delete/') ?>'+id,'Delete this chapel?');}
$('#chapel-form').on('submit',function(e){e.preventDefault();postForm('<?= site_url('admin/community/chapel-store') ?>',new FormData(this),function(){setTimeout(function(){location.reload();},450);});});

function newMass(chapel){ $('#mass-form')[0].reset(); $('#m-id').val(''); $('#m-chapel').val(chapel||''); $('#m-title').val('Holy Mass'); $('#m-order').val(0); $('#m-active').prop('checked',true); $('#mass-modal-title').text('Add Chapel Mass'); openModal('mass-modal');}
function editMass(id){$.get('<?= site_url('admin/community/mass-get/') ?>'+id,function(res){var d=res.data;newMass(d.chapel_id);$('#m-id').val(d.id);$('#m-day').val(d.day_of_week);$('#m-time').val(String(d.mass_time).slice(0,5));$('#m-title').val(d.title);$('#m-language').val(d.language);$('#m-recurrence').val(d.recurrence_note);$('#m-notes').val(d.notes);$('#m-order').val(d.display_order);$('#m-active').prop('checked',parseInt(d.is_active,10)===1);$('#mass-modal-title').text('Edit Chapel Mass');});}
function deleteMass(id){confirmDelete('<?= site_url('admin/community/mass-delete/') ?>'+id,'Delete this Mass schedule?');}
$('#mass-form').on('submit',function(e){e.preventDefault();postForm('<?= site_url('admin/community/mass-store') ?>',this,function(){setTimeout(function(){location.reload();},400);});});

function newCluster(chapel){$('#cluster-form')[0].reset();$('#g-id').val('');$('#g-chapel').val(chapel||'');$('#g-order').val(0);$('#g-active').prop('checked',true);$('#cluster-modal-title').text('Add GSK Cluster');openModal('cluster-modal');}
function editCluster(id){$.get('<?= site_url('admin/community/cluster-get/') ?>'+id,function(res){var d=res.data;newCluster(d.chapel_id);$('#g-id').val(d.id);$('#g-name').val(d.name);$('#g-code').val(d.code);$('#g-coverage').val(d.coverage_area);$('#g-meeting').val(d.meeting_schedule);$('#g-description').val(d.description);$('#g-order').val(d.display_order);$('#g-active').prop('checked',parseInt(d.is_active,10)===1);$('#cluster-modal-title').text('Edit GSK Cluster');});}
function deleteCluster(id){confirmDelete('<?= site_url('admin/community/cluster-delete/') ?>'+id,'Delete this GSK cluster?');}
$('#cluster-form').on('submit',function(e){e.preventDefault();postForm('<?= site_url('admin/community/cluster-store') ?>',this,function(){setTimeout(function(){location.reload();},400);});});

function refreshOfficialScope(){
  var scope=$('#o-scope').val(), chapel=parseInt($('#o-chapel').val()||0,10);
  $('#o-chapel-wrap').toggleClass('hidden',scope==='parish');
  $('#o-cluster-wrap').toggleClass('hidden',scope!=='cluster');
  if(scope==='cluster'){
    var current=$('#o-cluster').val();
    $('#o-cluster').empty();
    clustersData.filter(function(c){return c.chapel_id===chapel;}).forEach(function(c){$('#o-cluster').append($('<option>').val(c.id).text(c.name));});
    if(current)$('#o-cluster').val(current);
  }
}
$('#o-scope,#o-chapel').on('change',refreshOfficialScope);

function newOfficial(scope,chapel,cluster){
  $('#official-form')[0].reset();$('#o-id').val('');$('#o-scope').val(scope||'parish');if(chapel)$('#o-chapel').val(chapel);$('#o-order').val(0);$('#o-active').prop('checked',true);refreshOfficialScope();if(cluster)$('#o-cluster').val(cluster);$('#official-modal-title').text('Add Official');openModal('official-modal');
}
function editOfficial(id){$.get('<?= site_url('admin/community/official-get/') ?>'+id,function(res){var d=res.data;newOfficial(d.scope_type,d.chapel_id,d.cluster_id);$('#o-id').val(d.id);$('#o-name').val(d.full_name);$('#o-position').val(d.position_title);$('#o-committee').val(d.committee_area);$('#o-contact').val(d.contact_number);$('#o-email').val(d.email);$('#o-bio').val(d.bio);$('#o-start').val(d.term_start);$('#o-end').val(d.term_end);$('#o-order').val(d.display_order);$('#o-active').prop('checked',parseInt(d.is_active,10)===1);$('#official-modal-title').text('Edit Official');refreshOfficialScope();if(d.cluster_id)$('#o-cluster').val(d.cluster_id);});}
function deleteOfficial(id){confirmDelete('<?= site_url('admin/community/official-delete/') ?>'+id,'Delete this official?');}
$('#official-form').on('submit',function(e){e.preventDefault();postForm('<?= site_url('admin/community/official-store') ?>',new FormData(this),function(){setTimeout(function(){location.reload();},400);});});
</script>
