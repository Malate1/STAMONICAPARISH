<?php
$campaign_title = !empty($campaign) ? $campaign['title'] : 'General Parish Fund';
?>

<div class="max-w-5xl mx-auto">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div>
      <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Stewardship</div>
      <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Make a Donation</h1>
      <p class="text-gray-500 text-sm mt-1">Your gift will be recorded and verified by the parish office before it is included in project totals.</p>
    </div>
    <a href="<?= site_url('projects') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-parish-200 bg-white text-parish-700 text-sm font-semibold hover:bg-parish-50">
      <i class="ph ph-folder-open"></i> View Projects
    </a>
  </div>

  <div class="grid xl:grid-cols-[.9fr_1.1fr] gap-6">
    <section class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="p-6 sm:p-7 border-b border-gray-100">
        <div class="w-12 h-12 rounded-2xl bg-parish-50 text-parish-700 flex items-center justify-center text-2xl"><i class="ph ph-heart"></i></div>
        <h2 class="text-xl font-semibold text-gray-900 mt-4"><?= html_escape($campaign_title) ?></h2>
        <?php if (!empty($campaign['short_description'])): ?>
          <p class="text-sm text-gray-500 mt-2 leading-relaxed"><?= html_escape($campaign['short_description']) ?></p>
        <?php else: ?>
          <p class="text-sm text-gray-500 mt-2 leading-relaxed">Support the parish’s pastoral mission, ministries, church care and community needs.</p>
        <?php endif; ?>

        <?php if (!empty($campaign) && (float)$campaign['goal_amount'] > 0): ?>
          <div class="mt-5">
            <div class="flex items-center justify-between text-xs">
              <span class="font-semibold text-gray-700"><?= peso($campaign['raised_amount']) ?> raised</span>
              <span class="text-gray-400">Goal <?= peso($campaign['goal_amount']) ?></span>
            </div>
            <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden mt-2">
              <div class="h-full rounded-full bg-parish-600" style="width:<?= min(100,(float)$campaign['progress_percent']) ?>%"></div>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <form id="donation-form" class="p-6 sm:p-7 space-y-5">
        <input type="hidden" name="campaign_id" value="<?= !empty($campaign) ? (int)$campaign['id'] : 0 ?>">

        <div>
          <label class="text-xs font-semibold text-gray-600">Donation Amount (₱)</label>
          <input required type="number" min="1" step="0.01" name="amount" placeholder="500.00" class="mt-2 w-full px-4 py-3.5 rounded-xl border border-gray-200 text-lg font-semibold focus:ring-2 focus:ring-parish-100 focus:border-parish-300 outline-none">
          <div class="flex flex-wrap gap-2 mt-2">
            <?php foreach ([100,500,1000,2000] as $amount): ?>
              <button type="button" onclick="$('[name=amount]').val('<?= $amount ?>')" class="px-3 py-1.5 rounded-full bg-gray-50 hover:bg-parish-50 border border-gray-100 text-xs text-gray-600"><?= peso($amount) ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600">Message / Intention <span class="text-gray-300">(optional)</span></label>
          <textarea name="message" maxlength="255" rows="3" placeholder="A short message for the parish…" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea>
        </div>

        <label class="flex items-start gap-3 rounded-xl bg-gray-50 border border-gray-100 p-4">
          <input type="checkbox" name="is_anonymous" value="1" class="mt-0.5 rounded border-gray-300 text-parish-700">
          <span><strong class="block text-sm text-gray-800">Record my gift as anonymous</strong><span class="text-xs text-gray-400">The parish office will still see your account for payment verification, but your name does not need to be used for public donor recognition.</span></span>
        </label>

        <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-4 text-xs text-blue-900/80 leading-relaxed">
          <strong class="block text-blue-900">Next step: GCash verification</strong>
          After continuing, you will see the parish GCash details and upload your GCash reference number and payment proof.
        </div>

        <button type="submit" class="w-full py-3.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold">
          Continue to GCash <i class="ph ph-arrow-right ml-1"></i>
        </button>
      </form>
    </section>

    <section class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-7">
      <h2 class="font-semibold text-gray-900">My Donation History</h2>
      <p class="text-xs text-gray-400 mt-1">Donations become part of public project progress only after parish verification.</p>

      <div class="mt-5 divide-y divide-gray-100">
        <?php if (empty($donations)): ?>
          <div class="text-center py-10 text-sm text-gray-400">
            <i class="ph ph-heart text-3xl text-gray-300"></i>
            <p class="mt-2">No donations recorded yet.</p>
          </div>
        <?php endif; ?>

        <?php foreach ($donations as $d): ?>
          <div class="py-4 flex items-start justify-between gap-4">
            <div>
              <div class="text-sm font-semibold text-gray-800"><?= html_escape($d['campaign_title'] ?: 'General Parish Fund') ?></div>
              <div class="text-xs text-gray-400 mt-1"><?= format_date($d['created_at']) ?><?= !empty($d['is_anonymous']) ? ' · Anonymous' : '' ?></div>
              <?php if (!empty($d['receipt_no'])): ?><div class="text-[11px] text-emerald-600 mt-1">Receipt: <?= html_escape($d['receipt_no']) ?></div><?php endif; ?>
            </div>
            <div class="text-right">
              <div class="font-semibold text-gray-900"><?= peso($d['amount']) ?></div>
              <div class="mt-1 text-[11px] <?= ($d['payment_status'] ?? '') === 'payment_verified' ? 'text-emerald-600' : 'text-gray-400' ?>"><?= status_label($d['payment_status'] ?? 'awaiting_payment') ?></div>
              <?php if (empty($d['payment_status']) || in_array($d['payment_status'], ['awaiting_payment','rejected'], true)): ?>
                <a href="<?= site_url('my/payments/pay/donation/' . $d['id']) ?>" class="inline-flex items-center gap-1 text-[11px] font-semibold text-parish-700 hover:underline mt-1">Continue GCash <i class="ph ph-arrow-right"></i></a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>

<script>
$('#donation-form').on('submit',function(e){
  e.preventDefault();
  var btn=$(this).find('button[type=submit]');
  var original=btn.html();
  btn.prop('disabled',true).addClass('opacity-60').html('<i class="ph ph-spinner-gap animate-spin mr-1"></i> Preparing donation…');

  $.post('<?= site_url('my/donations/store') ?>',$(this).serialize(),function(res){
    if(res.success){
      toastr.success(res.message);
      window.location.href=res.redirect;
    }else{
      Swal.fire({icon:'error',title:'Could not continue',text:res.message || 'Please review your donation.',confirmButtonColor:'#235a38'});
      btn.prop('disabled',false).removeClass('opacity-60').html(original);
    }
  }).fail(function(){
    Swal.fire({icon:'error',title:'Could not continue',text:'The server did not accept the donation.',confirmButtonColor:'#235a38'});
    btn.prop('disabled',false).removeClass('opacity-60').html(original);
  });
});
</script>
