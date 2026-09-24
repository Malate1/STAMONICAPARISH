<?php
$status_counts = [
    'pending' => 0,
    'approved' => 0,
    'ready_for_reading' => 0,
    'completed' => 0,
    'cancelled' => 0,
];

foreach ($intentions as $row) {
    $key = $row['status'] === 'listed' ? 'ready_for_reading' : $row['status'];
    if (isset($status_counts[$key])) $status_counts[$key]++;
}

$grouped = [];
foreach ($intentions as $row) {
    $date_key = $row['mass_date'] ?: 'undated';
    $time_key = $row['mass_time'] ?: '00:00:00';
    $group_key = $date_key . '|' . $row['mass_schedule_id'] . '|' . $time_key;
    if (!isset($grouped[$group_key])) {
        $grouped[$group_key] = [
            'mass_date' => $row['mass_date'],
            'mass_schedule_id' => $row['mass_schedule_id'],
            'mass_title' => $row['mass_title'],
            'mass_time' => $row['mass_time'],
            'language' => $row['language'] ?? null,
            'location' => $row['location'] ?? null,
            'items' => [],
        ];
    }
    $grouped[$group_key]['items'][] = $row;
}
?>

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
  <div>
    <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Parish Secretary</div>
    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Mass Intentions</h1>
    <p class="text-gray-500 text-sm mt-1">Review requests, prepare the final reading list, and give the commentator a clean Mass-specific sheet.</p>
  </div>
  <a href="<?= site_url('staff/mass-intention-reader') ?>" target="_blank"
     class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold">
    <i class="ph ph-file-text"></i> Open Commentator Sheet
  </a>
</div>

<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
  <?php
  $summary = [
      ['pending','Pending','ph-clock','bg-amber-50 text-amber-700'],
      ['approved','Approved','ph-check-circle','bg-emerald-50 text-emerald-700'],
      ['ready_for_reading','Ready','ph-microphone-stage','bg-blue-50 text-blue-700'],
      ['completed','Completed','ph-checks','bg-green-50 text-green-700'],
      ['cancelled','Cancelled','ph-x-circle','bg-gray-100 text-gray-600'],
  ];
  foreach ($summary as $s):
  ?>
  <div class="rounded-2xl border border-gray-100 bg-white p-4">
    <div class="w-9 h-9 rounded-xl <?= $s[3] ?> flex items-center justify-center"><i class="ph <?= $s[2] ?>"></i></div>
    <div class="text-2xl font-bold text-gray-900 mt-3"><?= (int) $status_counts[$s[0]] ?></div>
    <div class="text-xs text-gray-500 mt-0.5"><?= $s[1] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4 sm:p-5 mb-6">
  <div class="flex gap-3">
    <div class="w-10 h-10 rounded-xl bg-white text-blue-700 flex items-center justify-center flex-shrink-0">
      <i class="ph ph-info text-xl"></i>
    </div>
    <div class="text-sm text-blue-900/80 leading-relaxed">
      <div class="font-semibold text-blue-900">Workflow</div>
      <p class="mt-1"><strong>Pending</strong> → Secretary checks the request → <strong>Approved</strong> → final spelling/date check → <strong>Ready for Reading</strong> → appears on the commentator sheet → <strong>Completed</strong> after Mass.</p>
    </div>
  </div>
</div>

<?php if (empty($intentions)): ?>
  <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
    <div class="w-14 h-14 rounded-2xl bg-parish-50 text-parish-700 flex items-center justify-center text-2xl mx-auto"><i class="ph ph-hands-praying"></i></div>
    <h2 class="font-semibold text-gray-800 mt-4">No Mass intentions yet</h2>
    <p class="text-sm text-gray-400 mt-1">Submitted intentions will appear here for parish review.</p>
  </div>
<?php endif; ?>

<div class="space-y-5">
  <?php foreach ($grouped as $group): ?>
    <section class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="px-5 sm:px-6 py-4 bg-gray-50/70 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="text-[11px] uppercase tracking-wider text-parish-600 font-bold">
            <?= $group['mass_date'] ? format_date($group['mass_date'], 'l, M j, Y') : 'Legacy / Date not assigned' ?>
          </div>
          <div class="font-semibold text-gray-900 mt-1">
            <?= html_escape($group['mass_title'] ?: 'Mass') ?>
            <?php if ($group['mass_time']): ?> · <?= date('g:i A', strtotime($group['mass_time'])) ?><?php endif; ?>
          </div>
          <div class="text-xs text-gray-400 mt-1">
            <?= html_escape($group['location'] ?: 'Main Church') ?>
            <?php if (!empty($group['language'])): ?> · <?= html_escape($group['language']) ?><?php endif; ?>
            · <?= count($group['items']) ?> intention<?= count($group['items']) === 1 ? '' : 's' ?>
          </div>
        </div>

        <?php if ($group['mass_date']): ?>
          <a href="<?= site_url('staff/mass-intention-reader') . '?date=' . rawurlencode($group['mass_date']) . '&schedule_id=' . (int) $group['mass_schedule_id'] ?>"
             target="_blank"
             class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-parish-200 bg-white text-parish-700 text-xs font-semibold hover:bg-parish-50">
            <i class="ph ph-file-text"></i> Reader Sheet
          </a>
        <?php endif; ?>
      </div>

      <div class="divide-y divide-gray-100">
        <?php foreach ($group['items'] as $i): ?>
          <?php $display_status = $i['status'] === 'listed' ? 'ready_for_reading' : $i['status']; ?>
          <div class="p-5 sm:p-6 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?= status_badge_class($display_status) ?>"><?= status_label($display_status) ?></span>
                <span class="text-[11px] uppercase tracking-wide text-gray-400"><?= html_escape(ucwords(str_replace('_',' ',$i['intention_type']))) ?></span>
              </div>

              <div class="text-base font-semibold text-gray-900 mt-2"><?= html_escape($i['offered_for']) ?></div>
              <div class="text-xs text-gray-400 mt-1">
                Requested by <?= html_escape($i['requestor_name']) ?>
                <?php if (!empty($i['requestor_contact'])): ?> · <?= html_escape($i['requestor_contact']) ?><?php endif; ?>
                · submitted <?= format_date($i['created_at']) ?>
              </div>
            </div>

            <div class="flex flex-wrap gap-2 xl:justify-end">
              <?php if (empty($i['mass_date'])): ?>
                <div class="w-full xl:w-auto flex flex-col sm:flex-row gap-2">
                  <select id="legacy-mass-<?= (int)$i['id'] ?>" class="min-w-[260px] px-3 py-2 rounded-lg border border-amber-200 bg-amber-50 text-xs text-gray-700">
                    <option value="">Assign actual Mass date & time…</option>
                    <?php foreach ($available_occurrences as $occ): ?>
                      <option value="<?= (int)$occ['id'] ?>|<?= html_escape($occ['mass_date']) ?>">
                        <?= html_escape($occ['date_label']) ?> · <?= html_escape($occ['time_label']) ?> — <?= html_escape($occ['title']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <button onclick="assignMass(<?= (int)$i['id'] ?>)" class="px-3.5 py-2 rounded-lg bg-amber-100 text-amber-800 text-xs font-semibold hover:bg-amber-200">
                    Assign Mass
                  </button>
                </div>
              <?php elseif ($display_status === 'pending'): ?>
                <button onclick="updateIntention(<?= (int)$i['id'] ?>,'approved')" class="px-3.5 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold hover:bg-emerald-100">
                  <i class="ph ph-check mr-1"></i> Approve
                </button>
                <button onclick="updateIntention(<?= (int)$i['id'] ?>,'cancelled')" class="px-3.5 py-2 rounded-lg bg-gray-100 text-gray-600 text-xs font-semibold hover:bg-gray-200">
                  Cancel
                </button>
              <?php elseif ($display_status === 'approved'): ?>
                <button onclick="updateIntention(<?= (int)$i['id'] ?>,'ready_for_reading')" class="px-3.5 py-2 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold hover:bg-blue-100">
                  <i class="ph ph-microphone-stage mr-1"></i> Ready for Reading
                </button>
                <button onclick="updateIntention(<?= (int)$i['id'] ?>,'cancelled')" class="px-3.5 py-2 rounded-lg bg-gray-100 text-gray-600 text-xs font-semibold hover:bg-gray-200">
                  Cancel
                </button>
              <?php elseif ($display_status === 'ready_for_reading'): ?>
                <button onclick="updateIntention(<?= (int)$i['id'] ?>,'completed')" class="px-3.5 py-2 rounded-lg bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100">
                  <i class="ph ph-checks mr-1"></i> Mark Completed
                </button>
                <button onclick="updateIntention(<?= (int)$i['id'] ?>,'approved')" class="px-3.5 py-2 rounded-lg bg-amber-50 text-amber-700 text-xs font-semibold hover:bg-amber-100">
                  Return to Approved
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
</div>

<script>
function assignMass(id){
  var value = String($('#legacy-mass-' + id).val() || '');
  var parts = value.split('|');
  if(parts.length !== 2){
    Swal.fire({icon:'warning', title:'Choose a Mass', text:'Select the actual upcoming Mass date and time first.', confirmButtonColor:'#235a38'});
    return;
  }

  $.post('<?= site_url('staff/mass-intention/assign-mass') ?>', {
    id:id,
    mass_schedule_id:parts[0],
    mass_date:parts[1]
  }, function(res){
    if(res.success){
      toastr.success(res.message);
      setTimeout(function(){ location.reload(); }, 650);
    } else {
      Swal.fire({icon:'error', title:'Could not assign Mass', text:res.message || 'Please try again.', confirmButtonColor:'#235a38'});
    }
  }).fail(function(){
    Swal.fire({icon:'error', title:'Could not assign Mass', text:'The server did not accept the update.', confirmButtonColor:'#235a38'});
  });
}

function updateIntention(id, status){
  var labels = {
    approved: 'Approve this Mass intention?',
    ready_for_reading: 'Add this intention to the commentator sheet?',
    completed: 'Mark this intention completed after Mass?',
    cancelled: 'Cancel this Mass intention?'
  };

  Swal.fire({
    icon: status === 'cancelled' ? 'warning' : 'question',
    title: labels[status] || 'Update Mass intention?',
    showCancelButton: true,
    confirmButtonText: 'Yes, continue',
    confirmButtonColor: '#235a38'
  }).then(function(result){
    if(!result.isConfirmed) return;

    $.post('<?= site_url('staff/mass_intention/update_status') ?>', { id:id, status:status }, function(res){
      if(res.success){
        toastr.success(res.message);
        setTimeout(function(){ location.reload(); }, 650);
      } else {
        Swal.fire({icon:'error', title:'Could not update', text:res.message || 'Please try again.', confirmButtonColor:'#235a38'});
      }
    }).fail(function(){
      Swal.fire({icon:'error', title:'Could not update', text:'The server did not accept the update.', confirmButtonColor:'#235a38'});
    });
  });
}
</script>
