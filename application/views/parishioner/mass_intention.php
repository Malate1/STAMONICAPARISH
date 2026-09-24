<div class="grid xl:grid-cols-[1.05fr_.95fr] gap-6">
  <section class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <div class="p-6 sm:p-7 border-b border-gray-100">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-2xl bg-parish-50 text-parish-700 flex items-center justify-center text-2xl flex-shrink-0">
          <i class="ph ph-hands-praying"></i>
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Submit Mass Intention</h1>
          <p class="text-sm text-gray-400 mt-1">Offer a Mass for thanksgiving, healing, special intentions, or the souls of the faithful departed.</p>
        </div>
      </div>
    </div>

    <form id="intention-form" class="p-6 sm:p-7 space-y-5">
      <div>
        <label class="text-xs font-semibold text-gray-600">Intention Type</label>
        <select required name="intention_type" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm focus:ring-2 focus:ring-parish-100 focus:border-parish-300 outline-none">
          <option value="">Select intention type…</option>
          <option value="thanksgiving">Thanksgiving</option>
          <option value="birthday">Birthday</option>
          <option value="healing">Healing</option>
          <option value="special">Special Intention</option>
          <option value="safe_travel">Safe Travel</option>
          <option value="souls_departed">Souls of the Faithful Departed</option>
          <option value="anniversary">Wedding Anniversary</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div>
        <label class="text-xs font-semibold text-gray-600">Offered For</label>
        <input required name="offered_for" maxlength="255" placeholder="Name(s) to be included in the intention"
               class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-parish-100 focus:border-parish-300 outline-none">
        <p class="text-[11px] text-gray-400 mt-1.5">Please enter names exactly as you want the parish office and commentator to read them.</p>
      </div>

      <div>
        <label class="text-xs font-semibold text-gray-600">Choose an Upcoming Mass</label>
        <select required id="mass-occurrence" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm focus:ring-2 focus:ring-parish-100 focus:border-parish-300 outline-none">
          <option value="">Select actual Mass date &amp; time…</option>
          <?php foreach ($masses as $m): ?>
            <option value="<?= (int)$m['id'] ?>|<?= html_escape($m['mass_date']) ?>">
              <?= html_escape($m['date_label']) ?> · <?= html_escape($m['time_label']) ?> — <?= html_escape($m['title']) ?>
              <?php if (!empty($m['language'])): ?> · <?= html_escape($m['language']) ?><?php endif; ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="hidden" name="mass_schedule_id" id="mass-schedule-id">
        <input type="hidden" name="mass_date" id="mass-date">

        <?php if (empty($masses)): ?>
          <div class="mt-3 rounded-xl bg-amber-50 border border-amber-100 p-3 text-xs text-amber-800">
            No Mass is currently open for online intentions. Please contact the parish office.
          </div>
        <?php else: ?>
          <p class="text-[11px] text-gray-400 mt-1.5">Online intentions close <?= (int)$cutoff_minutes ?> minute<?= (int)$cutoff_minutes === 1 ? '' : 's' ?> before the selected Mass.</p>
        <?php endif; ?>
      </div>

      <div class="rounded-2xl bg-blue-50/60 border border-blue-100 p-4">
        <div class="flex gap-3">
          <i class="ph ph-info text-blue-700 text-lg mt-0.5"></i>
          <div class="text-xs text-blue-900/80 leading-relaxed">
            <div class="font-semibold text-blue-900">What happens after you submit?</div>
            <p class="mt-1">The parish secretary reviews your request. Once finalized, it is marked <strong>Ready for Reading</strong> and appears on the commentator’s sheet for that exact Mass.</p>
          </div>
        </div>
      </div>

      <button type="submit" <?= empty($masses) ? 'disabled' : '' ?>
              class="w-full py-3.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed transition">
        <i class="ph ph-paper-plane-tilt mr-1"></i> Submit Mass Intention
      </button>
    </form>
  </section>

  <section class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <div class="p-6 sm:p-7 border-b border-gray-100">
      <h2 class="text-lg font-semibold text-gray-900">My Mass Intentions</h2>
      <p class="text-sm text-gray-400 mt-1">Track the review and reading status of your submitted intentions.</p>
    </div>

    <div class="divide-y divide-gray-100">
      <?php if (empty($intentions)): ?>
        <div class="text-center py-14 px-6">
          <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center text-xl mx-auto"><i class="ph ph-list-checks"></i></div>
          <p class="text-sm text-gray-400 mt-3">No Mass intentions submitted yet.</p>
        </div>
      <?php endif; ?>

      <?php foreach ($intentions as $i): ?>
        <?php $display_status = $i['status'] === 'listed' ? 'ready_for_reading' : $i['status']; ?>
        <article class="p-5 sm:p-6">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="text-[11px] uppercase tracking-wide text-parish-600 font-semibold"><?= html_escape(ucwords(str_replace('_',' ',$i['intention_type']))) ?></div>
              <div class="font-semibold text-gray-900 mt-1"><?= html_escape($i['offered_for']) ?></div>

              <div class="text-xs text-gray-500 mt-2 flex flex-wrap gap-x-3 gap-y-1">
                <?php if (!empty($i['mass_date'])): ?>
                  <span><i class="ph ph-calendar-blank mr-1"></i><?= format_date($i['mass_date'], 'D, M j, Y') ?></span>
                <?php endif; ?>
                <?php if (!empty($i['mass_time'])): ?>
                  <span><i class="ph ph-clock mr-1"></i><?= date('g:i A', strtotime($i['mass_time'])) ?></span>
                <?php endif; ?>
                <?php if (!empty($i['mass_title'])): ?>
                  <span><?= html_escape($i['mass_title']) ?></span>
                <?php endif; ?>
              </div>
            </div>

            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?= status_badge_class($display_status) ?> whitespace-nowrap">
              <?= status_label($display_status) ?>
            </span>
          </div>

          <div class="mt-3 text-[11px] text-gray-400">
            <?php if ($display_status === 'pending'): ?>
              Waiting for parish office review.
            <?php elseif ($display_status === 'approved'): ?>
              Approved by the parish office; final reading preparation is pending.
            <?php elseif ($display_status === 'ready_for_reading'): ?>
              Finalized and included on the commentator’s reader sheet.
            <?php elseif ($display_status === 'completed'): ?>
              This intention has been recorded as completed after Mass.
            <?php elseif ($display_status === 'cancelled'): ?>
              This intention was cancelled.
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<script>
$('#mass-occurrence').on('change', function(){
  var value = String($(this).val() || '');
  var parts = value.split('|');
  $('#mass-schedule-id').val(parts.length === 2 ? parts[0] : '');
  $('#mass-date').val(parts.length === 2 ? parts[1] : '');
});

$('#intention-form').on('submit', function(e){
  e.preventDefault();

  if(!$('#mass-schedule-id').val() || !$('#mass-date').val()){
    Swal.fire({icon:'warning', title:'Choose a Mass', text:'Please select the actual Mass date and time for this intention.', confirmButtonColor:'#235a38'});
    return;
  }

  var $btn = $(this).find('button[type="submit"]');
  var original = $btn.html();
  $btn.prop('disabled', true).html('<i class="ph ph-spinner-gap animate-spin mr-1"></i> Submitting…');

  $.post('<?= site_url('my/mass-intentions/store') ?>', $(this).serialize(), function(res){
    if(res.success){
      toastr.success(res.message);
      setTimeout(function(){ location.reload(); }, 900);
    } else {
      $btn.prop('disabled', false).html(original);
      Swal.fire({icon:'error', title:'Could not submit', text:res.message || 'Please try again.', confirmButtonColor:'#235a38'});
    }
  }).fail(function(){
    $btn.prop('disabled', false).html(original);
    Swal.fire({icon:'error', title:'Could not submit', text:'The server did not accept the request.', confirmButtonColor:'#235a38'});
  });
});
</script>
