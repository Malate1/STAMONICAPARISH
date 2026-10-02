<?php
$base = (int)$current_user['role_id'] === ROLE_SECRETARY ? 'staff' : 'admin';
$schema_ready = !empty($schema_ready);
$workflow_ready = !empty($workflow_ready);
$batches = $batches ?? [];
$pages_by_batch = $pages_by_batch ?? [];
$total_batches = count($batches);
$total_pages = 0;
$total_records = 0;
foreach ($batches as $batch) {
    $total_pages += (int)($batch['page_count'] ?? 0);
    $total_records += (int)($batch['record_count'] ?? 0);
}
?>

<div class="max-w-7xl mx-auto">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div>
      <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Sacramental Records</div>
      <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Legacy Parish Archive</h1>
      <p class="text-sm text-gray-500 mt-1 max-w-3xl">Digitize older register books progressively while preserving the original physical register as the source of truth. Upload source pages, import draft entries, then verify them against the register.</p>
    </div>
    <?php if ($schema_ready && $workflow_ready): ?>
      <div class="flex flex-wrap gap-2">
        <a href="<?= site_url($base . '/archive/template') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 text-sm font-semibold hover:bg-gray-50">
          <i class="ph ph-file-csv"></i> CSV Template
        </a>
        <button type="button" onclick="openBatchModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold">
          <i class="ph ph-folder-plus"></i> New Archive Batch
        </button>
      </div>
    <?php endif; ?>
  </div>

  <?php if (!$workflow_ready || !$schema_ready): ?>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 sm:p-6 mb-6">
      <div class="flex gap-4">
        <div class="w-11 h-11 rounded-xl bg-white text-amber-700 flex items-center justify-center text-xl flex-shrink-0"><i class="ph ph-warning-circle"></i></div>
        <div>
          <h2 class="font-semibold text-amber-900">Archive database setup is not complete</h2>
          <p class="text-sm text-amber-800/80 mt-1">Run these migrations in phpMyAdmin, in this order, before using the archive:</p>
          <div class="mt-3 space-y-1.5 text-xs font-mono text-amber-900">
            <div>database/migrations/20260925_sacramental_record_workflow.sql</div>
            <div>database/migrations/20260925_legacy_archive.sql</div>
          </div>
          <p class="text-xs text-amber-800/70 mt-3">Do not run <code>database/schema.sql</code> on an existing production database.</p>
        </div>
      </div>
    </div>
  <?php else: ?>

  <div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
      <div class="flex items-center justify-between gap-3">
        <div>
          <div class="text-xs text-gray-400">Archive Batches</div>
          <div class="text-2xl font-bold text-gray-900 mt-1"><?= $total_batches ?></div>
        </div>
        <div class="w-11 h-11 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center text-xl"><i class="ph ph-books"></i></div>
      </div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
      <div class="flex items-center justify-between gap-3">
        <div>
          <div class="text-xs text-gray-400">Scanned Source Pages</div>
          <div class="text-2xl font-bold text-gray-900 mt-1"><?= $total_pages ?></div>
        </div>
        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-xl"><i class="ph ph-files"></i></div>
      </div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
      <div class="flex items-center justify-between gap-3">
        <div>
          <div class="text-xs text-gray-400">Digitized Records</div>
          <div class="text-2xl font-bold text-gray-900 mt-1"><?= $total_records ?></div>
        </div>
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl"><i class="ph ph-check-circle"></i></div>
      </div>
    </div>
  </div>

  <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4 sm:p-5 mb-6">
    <div class="flex gap-3">
      <i class="ph ph-info text-blue-700 text-xl mt-0.5"></i>
      <div class="text-xs text-blue-900/80 leading-relaxed">
        <strong class="text-blue-900">Recommended migration flow:</strong>
        create one batch per register/book range → upload the scanned source pages → import or encode records as <strong>Draft</strong> → compare each record with the original page → verify the record → mark the whole batch Completed only after no Draft entries remain.
      </div>
    </div>
  </div>

  <?php if (empty($batches)): ?>
    <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
      <div class="w-14 h-14 rounded-2xl bg-parish-50 text-parish-700 flex items-center justify-center text-2xl mx-auto"><i class="ph ph-books"></i></div>
      <h2 class="font-semibold text-gray-800 mt-4">No archive batches yet</h2>
      <p class="text-sm text-gray-400 mt-1 max-w-xl mx-auto">Start with one manageable register, such as a recent Baptism book or a frequently requested year range. You do not need to encode the entire historical archive before using the system.</p>
      <button type="button" onclick="openBatchModal()" class="mt-5 px-5 py-2.5 rounded-xl bg-parish-700 text-white text-sm font-semibold">Create First Batch</button>
    </div>
  <?php else: ?>
    <div class="space-y-5">
      <?php foreach ($batches as $batch): ?>
        <?php
          $batch_id = (int)$batch['id'];
          $pages = $pages_by_batch[$batch_id] ?? [];
          $status = $batch['status'];
          $badge = $status === 'completed'
            ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
            : ($status === 'review' ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-amber-50 text-amber-700 border-amber-100');
        ?>
        <section class="rounded-2xl border border-gray-100 bg-white overflow-hidden" x-data="{open: <?= $status === 'completed' ? 'false' : 'true' ?>}">
          <div class="p-5 sm:p-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold"><?= html_escape($batch['batch_code']) ?></span>
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[10px] font-bold <?= $badge ?>"><?= html_escape(status_label($status)) ?></span>
                  <?php if (!empty($batch['record_type'])): ?>
                    <span class="inline-flex px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-[10px] font-semibold"><?= html_escape(ucfirst($batch['record_type'])) ?></span>
                  <?php endif; ?>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 mt-2"><?= html_escape($batch['name']) ?></h2>
                <div class="flex flex-wrap gap-x-5 gap-y-1 mt-2 text-xs text-gray-500">
                  <?php if (!empty($batch['source_label'])): ?><span><i class="ph ph-book-open mr-1"></i><?= html_escape($batch['source_label']) ?></span><?php endif; ?>
                  <?php if (!empty($batch['physical_location'])): ?><span><i class="ph ph-map-pin mr-1"></i><?= html_escape($batch['physical_location']) ?></span><?php endif; ?>
                  <?php if (!empty($batch['year_from']) || !empty($batch['year_to'])): ?><span><i class="ph ph-calendar-blank mr-1"></i><?= html_escape(($batch['year_from'] ?: '?') . '–' . ($batch['year_to'] ?: '?')) ?></span><?php endif; ?>
                </div>
                <?php if (!empty($batch['notes'])): ?><p class="text-xs text-gray-400 mt-2 max-w-3xl"><?= nl2br(html_escape($batch['notes'])) ?></p><?php endif; ?>
              </div>

              <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                <span class="px-3 py-2 rounded-xl bg-gray-50 text-xs text-gray-600"><strong><?= (int)$batch['page_count'] ?></strong> page<?= (int)$batch['page_count'] === 1 ? '' : 's' ?></span>
                <span class="px-3 py-2 rounded-xl bg-gray-50 text-xs text-gray-600"><strong><?= (int)$batch['record_count'] ?></strong> record<?= (int)$batch['record_count'] === 1 ? '' : 's' ?></span>
                <button type="button" @click="open=!open" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50"><span x-text="open?'Collapse':'Manage'"></span></button>
              </div>
            </div>
          </div>

          <div x-show="open" class="border-t border-gray-100">
            <div class="p-5 sm:p-6 grid xl:grid-cols-[1fr_.72fr] gap-6">
              <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                  <div>
                    <h3 class="font-semibold text-gray-800">Source Register Pages</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Private scans are served only through authenticated staff access.</p>
                  </div>
                  <?php if ($status !== 'completed'): ?>
                    <button type="button" onclick="openPageModal(<?= $batch_id ?>, <?= json_encode($batch['name']) ?>)" class="px-3 py-2 rounded-lg bg-parish-50 text-parish-700 text-xs font-semibold hover:bg-parish-100"><i class="ph ph-upload-simple mr-1"></i>Upload Page</button>
                  <?php endif; ?>
                </div>

                <?php if (empty($pages)): ?>
                  <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-6 text-center text-xs text-gray-400">No source scans uploaded for this batch yet.</div>
                <?php else: ?>
                  <div class="grid sm:grid-cols-2 gap-3">
                    <?php foreach ($pages as $page): ?>
                      <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                        <div class="flex items-start gap-3">
                          <div class="w-9 h-9 rounded-lg bg-white border border-gray-100 text-parish-700 flex items-center justify-center flex-shrink-0"><i class="ph ph-file-image"></i></div>
                          <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-gray-700 truncate"><?= html_escape($page['page_label'] ?: $page['original_name']) ?></div>
                            <div class="text-[11px] text-gray-400 mt-1">Book <?= html_escape($page['registry_book'] ?: '—') ?> · Page <?= html_escape($page['registry_page'] ?: '—') ?></div>
                            <?php if (!empty($page['notes'])): ?><div class="text-[11px] text-gray-400 mt-1 line-clamp-2"><?= html_escape($page['notes']) ?></div><?php endif; ?>
                            <div class="flex items-center gap-3 mt-3">
                              <a href="<?= site_url('secure-file/archive/' . (int)$page['id']) ?>" target="_blank" rel="noopener" class="text-xs font-semibold text-parish-700 hover:underline">View scan</a>
                              <?php if ($status !== 'completed'): ?><button type="button" onclick="deleteArchivePage(<?= (int)$page['id'] ?>)" class="text-xs font-semibold text-red-500 hover:underline">Remove</button><?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

              <div class="space-y-4">
                <div class="rounded-2xl border border-gray-100 p-5">
                  <h3 class="font-semibold text-gray-800">Batch Workflow</h3>
                  <p class="text-xs text-gray-400 mt-1">Encoding allows import/upload. Review is for record verification. Completed locks the batch.</p>
                  <div class="mt-4 flex flex-wrap gap-2">
                    <?php if ($status === 'encoding'): ?>
                      <button type="button" onclick="changeBatchStatus(<?= $batch_id ?>,'review')" class="px-3 py-2 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold">Move to Review</button>
                    <?php elseif ($status === 'review'): ?>
                      <button type="button" onclick="changeBatchStatus(<?= $batch_id ?>,'encoding')" class="px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-xs font-semibold">Return to Encoding</button>
                      <button type="button" onclick="changeBatchStatus(<?= $batch_id ?>,'completed')" class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Mark Completed</button>
                    <?php else: ?>
                      <button type="button" onclick="changeBatchStatus(<?= $batch_id ?>,'review')" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-600 text-xs font-semibold">Reopen for Review</button>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if ($status === 'encoding'): ?>
                <div class="rounded-2xl border border-gray-100 p-5">
                  <h3 class="font-semibold text-gray-800">Import Draft Records</h3>
                  <p class="text-xs text-gray-400 mt-1">CSV imports are always saved as Draft and must be verified against the original register.</p>
                  <form class="archive-import-form mt-4 space-y-3" data-batch-id="<?= $batch_id ?>" enctype="multipart/form-data">
                    <input type="hidden" name="batch_id" value="<?= $batch_id ?>">
                    <input required type="file" name="csv_file" accept=".csv,text/csv" class="w-full text-xs text-gray-500">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-xs font-semibold"><i class="ph ph-file-arrow-up mr-1"></i>Import CSV</button>
                  </form>
                  <a href="<?= site_url($base . '/archive/template') ?>" class="block text-center text-xs text-parish-700 hover:underline mt-3">Download the expected CSV columns</a>
                </div>
                <?php endif; ?>

                <a href="<?= site_url($base . '/record') ?>" class="flex items-center justify-between gap-3 rounded-2xl bg-parish-50 border border-parish-100 p-4 text-sm font-semibold text-parish-800 hover:bg-parish-100">
                  <span><i class="ph ph-archive mr-2"></i>Open Sacramental Records</span><i class="ph ph-arrow-right"></i>
                </a>
              </div>
            </div>
          </div>
        </section>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php if ($schema_ready && $workflow_ready): ?>
<div id="batch-modal" class="hidden fixed inset-0 z-[80] items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeBatchModal()"></div>
  <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl max-h-[92vh] overflow-y-auto">
    <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
      <div><h2 class="font-semibold text-gray-900">Create Archive Batch</h2><p class="text-xs text-gray-400 mt-1">Group related register pages and records into one manageable digitization batch.</p></div>
      <button type="button" onclick="closeBatchModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 text-gray-400"><i class="ph ph-x"></i></button>
    </div>
    <form id="batch-form" class="p-6 space-y-4">
      <div>
        <label class="text-xs font-semibold text-gray-600">Batch Name</label>
        <input required name="name" placeholder="e.g. Baptism Register 1980–1989" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="text-xs font-semibold text-gray-600">Record Type</label><select name="record_type" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200"><option value="">Mixed / Not specified</option><option value="baptism">Baptism</option><option value="confirmation">Confirmation</option><option value="communion">Communion</option><option value="marriage">Marriage</option><option value="funeral">Funeral</option></select></div>
        <div><label class="text-xs font-semibold text-gray-600">Source / Book Label</label><input name="source_label" placeholder="Book B-1980" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></div>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="text-xs font-semibold text-gray-600">Year From</label><input type="number" min="1500" max="<?= date('Y') ?>" name="year_from" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></div>
        <div><label class="text-xs font-semibold text-gray-600">Year To</label><input type="number" min="1500" max="<?= date('Y') ?>" name="year_to" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></div>
      </div>
      <div><label class="text-xs font-semibold text-gray-600">Physical Location</label><input name="physical_location" placeholder="e.g. Registry Cabinet A · Shelf 2" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></div>
      <div><label class="text-xs font-semibold text-gray-600">Notes</label><textarea name="notes" rows="3" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm" placeholder="Condition, missing pages, naming conventions, etc."></textarea></div>
      <div class="pt-4 border-t border-gray-100 flex justify-end gap-2"><button type="button" onclick="closeBatchModal()" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600">Cancel</button><button type="submit" class="px-5 py-2.5 rounded-xl bg-parish-700 text-white text-sm font-semibold">Create Batch</button></div>
    </form>
  </div>
</div>

<div id="page-modal" class="hidden fixed inset-0 z-[80] items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closePageModal()"></div>
  <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl">
    <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
      <div><h2 class="font-semibold text-gray-900">Upload Register Page</h2><p id="page-modal-batch" class="text-xs text-gray-400 mt-1"></p></div>
      <button type="button" onclick="closePageModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 text-gray-400"><i class="ph ph-x"></i></button>
    </div>
    <form id="page-form" class="p-6 space-y-4" enctype="multipart/form-data">
      <input type="hidden" name="batch_id" id="page-batch-id">
      <div class="grid sm:grid-cols-2 gap-4"><div><label class="text-xs font-semibold text-gray-600">Registry Book</label><input name="registry_book" placeholder="B-1980" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></div><div><label class="text-xs font-semibold text-gray-600">Registry Page</label><input name="registry_page" placeholder="12" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></div></div>
      <div><label class="text-xs font-semibold text-gray-600">Page Label</label><input name="page_label" placeholder="Page 12 · Entries 31–36" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></div>
      <div><label class="text-xs font-semibold text-gray-600">Scanned Page</label><input required type="file" name="source_page" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" class="mt-2 w-full text-xs text-gray-500"><p class="text-[11px] text-gray-400 mt-1">JPG, PNG or PDF. Stored privately; not directly accessible from the uploads folder.</p></div>
      <div><label class="text-xs font-semibold text-gray-600">Notes</label><textarea name="notes" rows="2" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea></div>
      <div class="pt-4 border-t border-gray-100 flex justify-end gap-2"><button type="button" onclick="closePageModal()" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600">Cancel</button><button type="submit" class="px-5 py-2.5 rounded-xl bg-parish-700 text-white text-sm font-semibold">Upload Page</button></div>
    </form>
  </div>
</div>

<script>
function openBatchModal(){
  $('#batch-modal').removeClass('hidden').addClass('flex');
  document.body.classList.add('overflow-hidden');
  setTimeout(function(){ if(window.initParishSelect2) window.initParishSelect2($('#batch-modal')); }, 20);
}
function closeBatchModal(){ $('#batch-modal').addClass('hidden').removeClass('flex'); document.body.classList.remove('overflow-hidden'); }
function openPageModal(id,name){
  $('#page-batch-id').val(id);
  $('#page-modal-batch').text(name || 'Archive batch');
  $('#page-modal').removeClass('hidden').addClass('flex');
  document.body.classList.add('overflow-hidden');
}
function closePageModal(){ $('#page-modal').addClass('hidden').removeClass('flex'); document.body.classList.remove('overflow-hidden'); $('#page-form')[0].reset(); }

$('#batch-form').on('submit', function(e){
  e.preventDefault();
  var $btn=$(this).find('button[type="submit"]');
  $btn.prop('disabled',true).addClass('opacity-60');
  $.post('<?= site_url($base . '/archive/batch-store') ?>', $(this).serialize())
    .done(function(res){ if(res.success){ toastr.success(res.message); setTimeout(function(){location.reload();},500); } else { toastr.error(res.message); } })
    .fail(function(){ toastr.error('The archive batch could not be saved.'); })
    .always(function(){ $btn.prop('disabled',false).removeClass('opacity-60'); });
});

$('#page-form').on('submit', function(e){
  e.preventDefault();
  var data=new FormData(this), $btn=$(this).find('button[type="submit"]');
  $btn.prop('disabled',true).addClass('opacity-60');
  $.ajax({url:'<?= site_url($base . '/archive/page-upload') ?>',type:'POST',data:data,processData:false,contentType:false})
    .done(function(res){ if(res.success){ toastr.success(res.message); setTimeout(function(){location.reload();},500); } else { toastr.error(res.message); } })
    .fail(function(){ toastr.error('The register page could not be uploaded.'); })
    .always(function(){ $btn.prop('disabled',false).removeClass('opacity-60'); });
});

$('.archive-import-form').on('submit', function(e){
  e.preventDefault();
  var form=this, data=new FormData(form), $btn=$(form).find('button[type="submit"]');
  Swal.fire({icon:'question',title:'Import these legacy records?',text:'Imported rows will be saved as Draft and must be checked against the source register.',showCancelButton:true,confirmButtonText:'Import Draft Records',confirmButtonColor:'#235a38'})
    .then(function(result){
      if(!result.isConfirmed) return;
      $btn.prop('disabled',true).addClass('opacity-60');
      $.ajax({url:'<?= site_url($base . '/archive/import-csv') ?>',type:'POST',data:data,processData:false,contentType:false})
        .done(function(res){ if(res.success){ Swal.fire({icon:'success',title:'Import complete',text:res.message,confirmButtonColor:'#235a38'}).then(function(){location.reload();}); } else { Swal.fire({icon:'error',title:'Import failed',text:res.message,confirmButtonColor:'#235a38'}); } })
        .fail(function(){ Swal.fire({icon:'error',title:'Import failed',text:'The CSV could not be processed.',confirmButtonColor:'#235a38'}); })
        .always(function(){ $btn.prop('disabled',false).removeClass('opacity-60'); });
    });
});

function changeBatchStatus(id,status){
  var labels={review:'move this batch to Review',encoding:'reopen this batch for Encoding',completed:'mark this batch Completed'};
  Swal.fire({icon:'question',title:'Confirm archive workflow change',text:'Are you sure you want to '+(labels[status]||'change this batch')+'?',showCancelButton:true,confirmButtonText:'Continue',confirmButtonColor:'#235a38'})
    .then(function(result){
      if(!result.isConfirmed) return;
      $.post('<?= site_url($base . '/archive/batch-status/') ?>'+id,{status:status},function(res){
        if(res.success){ toastr.success(res.message); setTimeout(function(){location.reload();},500); }
        else toastr.error(res.message);
      });
    });
}

function deleteArchivePage(id){
  Swal.fire({icon:'warning',title:'Remove this source scan?',text:'The system will refuse removal if sacramental records are already linked to this page.',showCancelButton:true,confirmButtonText:'Remove Page',confirmButtonColor:'#b91c1c'})
    .then(function(result){
      if(!result.isConfirmed) return;
      $.post('<?= site_url($base . '/archive/page-delete/') ?>'+id,{},function(res){
        if(res.success){ toastr.success(res.message); setTimeout(function(){location.reload();},500); }
        else toastr.error(res.message);
      });
    });
}
</script>
<?php endif; ?>
