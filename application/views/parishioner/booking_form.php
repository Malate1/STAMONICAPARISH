<?php
$allow_special = !empty($service['allow_special_booking']);
$has_regular = !empty($schedule_rules);
$min_advance = max(0, (int) ($service['min_advance_days'] ?? 1));
$max_advance = max($min_advance, (int) ($service['max_advance_days'] ?? 365));
$min_date = date('Y-m-d', strtotime('+' . $min_advance . ' day'));
$max_date = date('Y-m-d', strtotime('+' . $max_advance . ' day'));
$weekday_names = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

$weeks_label = static function ($value) {
    $labels = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th'];
    $out = [];
    foreach (array_filter(array_map('intval', explode(',', (string) $value))) as $n) {
        if (isset($labels[$n])) $out[] = $labels[$n];
    }
    return implode(' & ', $out);
};
?>

<div class="max-w-5xl mx-auto">
  <a href="<?= site_url('my/bookings') ?>" class="inline-flex items-center gap-1.5 text-sm text-parish-700 hover:text-parish-900">
    <i class="ph ph-arrow-left"></i> Back to My Bookings
  </a>

  <div class="mt-4 rounded-[1.75rem] bg-gradient-to-r from-parish-900 via-parish-800 to-parish-700 text-white p-6 sm:p-8 shadow-sm">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center text-2xl flex-shrink-0">
          <i class="ph <?= $service['service_key'] === 'wedding' ? 'ph-rings' : ($service['service_key'] === 'baptism' ? 'ph-drop' : 'ph-calendar-check') ?>"></i>
        </div>
        <div>
          <div class="text-xs uppercase tracking-[.14em] text-gold-200 font-semibold">Sacramental / Parish Booking</div>
          <h1 class="text-2xl sm:text-3xl font-bold mt-1"><?= html_escape($service['name']) ?></h1>
          <p class="text-sm text-white/65 mt-2 max-w-2xl"><?= html_escape($service['description']) ?></p>
        </div>
      </div>

      <div class="flex flex-wrap gap-2 md:justify-end">
        <?php if ($has_regular): ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-emerald-400/15 border border-emerald-300/20 text-emerald-100 text-xs font-semibold">
            <i class="ph ph-calendar-heart"></i> Regular schedule available
          </span>
        <?php endif; ?>
        <?php if ($allow_special): ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-gold-400/15 border border-gold-300/20 text-gold-100 text-xs font-semibold">
            <i class="ph ph-sparkle"></i> Special booking <?= peso($service['special_fee']) ?>
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <form id="booking-form" enctype="multipart/form-data" class="mt-6 space-y-6">
    <input type="hidden" name="service_type_id" value="<?= $service['id'] ?>">
    <input type="hidden" name="booking_type" id="booking-type">
    <input type="hidden" name="schedule_start" id="schedule-start">
    <input type="hidden" name="schedule_rule_id" id="schedule-rule-id">

    <!-- Step 1: availability -->
    <section class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center gap-4">
        <div class="w-9 h-9 rounded-xl bg-parish-700 text-white flex items-center justify-center font-bold text-sm">1</div>
        <div>
          <h2 class="font-semibold text-gray-900">Choose an available schedule</h2>
          <p class="text-xs text-gray-400 mt-0.5">Only open time slots are selectable. Existing church bookings are blocked automatically.</p>
        </div>
      </div>

      <div class="p-6 sm:p-8">
        <div class="grid <?= $allow_special ? 'md:grid-cols-2' : 'grid-cols-1' ?> gap-4">
          <?php if ($has_regular): ?>
          <button type="button" id="type-regular" onclick="setBookingType('regular')" class="booking-type-card text-left rounded-2xl border-2 border-gray-100 p-5 hover:border-parish-200 transition">
            <div class="flex items-start gap-4">
              <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl flex-shrink-0"><i class="ph ph-calendar-heart"></i></div>
              <div class="flex-1">
                <div class="flex items-center justify-between gap-3">
                  <h3 class="font-semibold text-gray-900">Regular / Parish Schedule</h3>
                  <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold">FREE / SET FEE</span>
                </div>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">Choose from recurring dates published by the parish office. These slots use the configured regular schedule and fee.</p>
              </div>
            </div>
          </button>
          <?php endif; ?>

          <?php if ($allow_special): ?>
          <button type="button" id="type-special" onclick="setBookingType('special')" class="booking-type-card text-left rounded-2xl border-2 border-gray-100 p-5 hover:border-gold-200 transition">
            <div class="flex items-start gap-4">
              <div class="w-11 h-11 rounded-xl bg-gold-50 text-gold-700 flex items-center justify-center text-xl flex-shrink-0"><i class="ph ph-sparkle"></i></div>
              <div class="flex-1">
                <div class="flex items-center justify-between gap-3">
                  <h3 class="font-semibold text-gray-900">Special Booking</h3>
                  <span class="px-2.5 py-1 rounded-full bg-gold-100 text-gold-700 text-[11px] font-bold"><?= peso($service['special_fee']) ?></span>
                </div>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">Choose another eligible date and then select from available times configured by the parish.</p>
              </div>
            </div>
          </button>
          <?php endif; ?>
        </div>

        <?php if ($has_regular): ?>
        <div class="mt-5 rounded-2xl bg-parish-50/60 border border-parish-100 p-4">
          <div class="text-[11px] uppercase tracking-wider font-semibold text-parish-700 mb-2">Published Regular Schedule</div>
          <div class="flex flex-wrap gap-2">
            <?php foreach ($schedule_rules as $rule): ?>
              <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-parish-100 text-xs text-gray-600">
                <i class="ph ph-calendar-dots text-parish-700"></i>
                <?= html_escape($weeks_label($rule['week_numbers'])) ?> <?= html_escape($weekday_names[(int) $rule['day_of_week']]) ?>
                ·
                <?= $rule['start_time'] ? date('g:i A', strtotime($rule['start_time'])) : '<span class="text-amber-600">time pending</span>' ?>
                ·
                <strong class="<?= (float) $rule['fee_amount'] === 0.0 ? 'text-emerald-700' : 'text-gold-700' ?>">
                  <?= (float) $rule['fee_amount'] === 0.0 ? 'FREE' : peso($rule['fee_amount']) ?>
                </strong>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Regular slot panel -->
        <div id="regular-panel" class="hidden mt-6">
          <div class="flex items-center justify-between gap-3 mb-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Next available regular slots</h3>
              <p class="text-xs text-gray-400 mt-0.5">Unavailable or fully booked schedules are automatically hidden.</p>
            </div>
            <button type="button" onclick="loadRegularSlots()" class="text-xs font-semibold text-parish-700 hover:text-parish-900"><i class="ph ph-arrows-clockwise"></i> Refresh</button>
          </div>
          <div id="regular-slots" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3"></div>
          <div id="regular-message" class="hidden rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-500"></div>
        </div>

        <!-- Special availability panel -->
        <div id="special-panel" class="hidden mt-6">
          <div class="rounded-2xl border border-gold-100 bg-gold-50/35 p-4 sm:p-5 mb-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div>
                <div class="text-[11px] uppercase tracking-wider font-semibold text-gold-700">Availability-first special booking</div>
                <p class="text-xs text-gray-500 mt-1">Only dates with at least one open church time are shown. Regular parish-schedule dates and conflicting sacramental bookings are excluded automatically.</p>
              </div>
              <div class="text-sm font-bold text-gold-800 whitespace-nowrap"><?= peso($service['special_fee']) ?> special fee</div>
            </div>
          </div>

          <div class="grid lg:grid-cols-[1fr_.95fr] gap-5 items-start">
            <div>
              <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                  <h3 class="text-sm font-semibold text-gray-800">Next available special dates</h3>
                  <p class="text-xs text-gray-400 mt-0.5">Choose from dates that currently have an open slot.</p>
                </div>
                <button type="button" onclick="loadSpecialDates()" class="text-xs font-semibold text-parish-700 hover:text-parish-900"><i class="ph ph-arrows-clockwise"></i> Refresh</button>
              </div>
              <div id="special-dates" class="grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
              <div id="special-date-message" class="hidden rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-500"></div>
            </div>

            <div class="lg:sticky lg:top-24">
              <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                  <h3 class="text-sm font-semibold text-gray-800">Available times</h3>
                  <p id="special-time-help" class="text-xs text-gray-400 mt-0.5">Choose an available date first.</p>
                </div>
              </div>
              <div id="special-slots" class="grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
              <div id="special-message" class="rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-500">
                Select one of the available dates to see its open times.
              </div>
            </div>
          </div>
        </div>

        <!-- Selection summary -->
        <div id="schedule-summary" class="hidden mt-6 rounded-2xl bg-parish-900 text-white p-5">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <div class="w-11 h-11 rounded-xl bg-white/10 text-gold-300 flex items-center justify-center text-xl"><i class="ph ph-calendar-check"></i></div>
              <div>
                <div class="text-[10px] uppercase tracking-wider text-gold-300 font-semibold">Selected Schedule</div>
                <div id="selected-schedule-label" class="font-semibold mt-1"></div>
                <div id="selected-schedule-meta" class="text-xs text-white/60 mt-0.5"></div>
              </div>
            </div>
            <button type="button" onclick="clearSlotSelection()" class="text-xs text-white/60 hover:text-white"><i class="ph ph-x"></i> Change</button>
          </div>
        </div>
      </div>
    </section>

    <!-- Step 2: booking details -->
    <section class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center gap-4">
        <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center font-bold text-sm">2</div>
        <div>
          <h2 class="font-semibold text-gray-900"><?= html_escape($service['name']) ?> details</h2>
          <p class="text-xs text-gray-400 mt-0.5">Provide the information needed by the parish office.</p>
        </div>
      </div>

      <div class="p-6 sm:p-8 space-y-5">
        <?php if ($service['service_key'] === 'baptism'): ?>
          <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Child Information</h3>
            <div class="grid sm:grid-cols-2 gap-4">
              <input required name="child_name" placeholder="Child's Full Name" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">
              <input required type="date" name="child_birth_date" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Parents' Information</h3>
            <div class="grid sm:grid-cols-2 gap-4">
              <input required name="father_name" placeholder="Father's Full Name" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">
              <input required name="mother_name" placeholder="Mother's Full Name" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Godparents / Sponsors</h3>
            <textarea name="sponsors" rows="3" placeholder="List of godparents (one per line)" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea>
          </div>

        <?php elseif ($service['service_key'] === 'wedding'): ?>
          <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Bride &amp; Groom Information</h3>
            <div class="grid sm:grid-cols-2 gap-4">
              <input required name="bride_name" placeholder="Bride's Full Name" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">
              <input required name="groom_name" placeholder="Groom's Full Name" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Additional Details</h3>
            <textarea name="sponsor_information" rows="3" placeholder="Principal sponsors, notes, or other information" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea>
          </div>
          <div class="bg-amber-50 border border-amber-100 text-amber-800 text-xs rounded-xl p-4 flex gap-2">
            <i class="ph ph-info text-lg flex-shrink-0"></i>
            <div>
              Wedding applications remain subject to documentary requirements, canonical interview, priest/parish review, and payment verification when applicable. Selecting an available slot reserves the requested church time while the application is being processed.
            </div>
          </div>

        <?php elseif ($service['service_key'] === 'funeral'): ?>
          <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Deceased Information</h3>
            <div class="grid sm:grid-cols-2 gap-4">
              <input required name="deceased_name" placeholder="Name of Deceased" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">
              <input required type="date" name="date_of_death" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">
              <input name="funeral_home" placeholder="Funeral Home" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">
              <input name="cemetery" placeholder="Cemetery" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>
          </div>

        <?php elseif ($service['service_key'] === 'confirmation'): ?>
          <input required name="confirmand_name" placeholder="Confirmand's Full Name" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">

        <?php elseif (in_array($service['service_key'], ['house_blessing', 'vehicle_blessing'])): ?>
          <textarea required name="blessing_details" rows="3" placeholder="Address / Vehicle details" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea>

        <?php else: ?>
          <textarea name="notes" rows="4" placeholder="Additional notes for the parish office" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea>
        <?php endif; ?>
      </div>
    </section>

    <!-- Step 3: requirements and submit -->
    <section class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center gap-4">
        <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center font-bold text-sm">3</div>
        <div>
          <h2 class="font-semibold text-gray-900">Requirements &amp; submission</h2>
          <p class="text-xs text-gray-400 mt-0.5">Upload available documents now; the parish office will review the application.</p>
        </div>
      </div>

      <div class="p-6 sm:p-8">
        <?php if (!empty($requirements)): ?>
          <div class="space-y-3">
            <?php foreach ($requirements as $req): ?>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 rounded-xl border border-gray-100 bg-gray-50/40">
              <div class="w-9 h-9 rounded-lg bg-white border border-gray-100 text-gray-400 flex items-center justify-center flex-shrink-0"><i class="ph ph-paperclip"></i></div>
              <div class="flex-1">
                <div class="text-sm text-gray-700">
                  <?= html_escape($req['label']) ?>
                  <?= $req['is_required'] ? ' <span class="text-red-500">*</span>' : ' <span class="text-gray-400 text-xs">(optional)</span>' ?>
                </div>
              </div>
              <input type="file" name="documents[]" class="text-xs max-w-full">
              <input type="hidden" name="requirement_id[]" value="<?= $req['id'] ?>">
            </div>
            <?php endforeach; ?>
          </div>
          <p class="text-xs text-gray-400 mt-3">Accepted formats: JPG, PNG, PDF. Physical/original copies may still be required by the parish office.</p>
        <?php else: ?>
          <div class="rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-500">No online document uploads are configured for this service.</div>
        <?php endif; ?>

        <div class="mt-6 border-t border-gray-100 pt-6">
          <button id="submit-booking" type="submit" disabled class="w-full sm:w-auto sm:min-w-[240px] py-3.5 px-6 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed transition">
            <i class="ph ph-paper-plane-tilt mr-1"></i> Submit Application
          </button>
          <p id="submit-help" class="text-xs text-gray-400 mt-2">Choose an available schedule first.</p>
        </div>
      </div>
    </section>
  </form>
</div>

<script>
var selectedSlot = null;
var currentBookingType = null;

function peso(value){
  return '₱' + Number(value || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
function escapeHtml(value){
  return $('<div>').text(value == null ? '' : value).html();
}

function setBookingType(type){
  currentBookingType = type;
  clearSlotSelection();
  $('#booking-type').val(type);

  $('.booking-type-card').removeClass('border-parish-500 bg-parish-50 border-gold-400 bg-gold-50');
  $('#regular-panel, #special-panel').addClass('hidden');

  if(type === 'regular'){
    $('#type-regular').addClass('border-parish-500 bg-parish-50');
    $('#regular-panel').removeClass('hidden');
    loadRegularSlots();
  } else {
    $('#type-special').addClass('border-gold-400 bg-gold-50');
    $('#special-panel').removeClass('hidden');
    loadSpecialDates();
  }
}

function renderSlotButton(slot, type){
  var feeText = Number(slot.fee || 0) === 0 ? 'FREE' : peso(slot.fee);
  var remaining = parseInt(slot.remaining || 1, 10);
  var capacityText = parseInt(slot.capacity || 1,10) > 1
    ? '<div class="text-[10px] text-gray-400 mt-1">' + remaining + ' place' + (remaining === 1 ? '' : 's') + ' left</div>'
    : '';

  return '<button type="button" class="available-slot text-left rounded-xl border border-gray-200 bg-white p-4 hover:border-parish-300 hover:shadow-sm transition" ' +
    'data-type="' + escapeHtml(type) + '" ' +
    'data-datetime="' + escapeHtml(slot.datetime) + '" ' +
    'data-rule="' + escapeHtml(slot.schedule_rule_id || '') + '" ' +
    'data-fee="' + escapeHtml(slot.fee) + '" ' +
    'data-date-label="' + escapeHtml(slot.date_label) + '" ' +
    'data-time="' + escapeHtml(slot.time) + '">' +
      '<div class="flex items-center justify-between gap-2">' +
        '<div class="text-sm font-semibold text-gray-800">' + escapeHtml(slot.date_label) + '</div>' +
        '<span class="text-[10px] font-bold px-2 py-1 rounded-full ' + (Number(slot.fee || 0) === 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gold-100 text-gold-700') + '">' + feeText + '</span>' +
      '</div>' +
      '<div class="text-lg font-bold text-parish-800 mt-2">' + escapeHtml(slot.time) + '</div>' +
      '<div class="text-[11px] text-gray-400 mt-1">' + escapeHtml(slot.rule_name || (type === 'regular' ? 'Regular Schedule' : 'Special Booking')) + '</div>' +
      capacityText +
    '</button>';
}

function bindSlotButtons(){
  $('.available-slot').off('click').on('click', function(){
    var $btn = $(this);
    selectedSlot = {
      booking_type: $btn.data('type'),
      datetime: $btn.data('datetime'),
      schedule_rule_id: $btn.data('rule') || '',
      fee: Number($btn.data('fee') || 0),
      date_label: $btn.data('date-label'),
      time: $btn.data('time')
    };

    $('.available-slot').removeClass('border-parish-600 ring-2 ring-parish-100 bg-parish-50');
    $btn.addClass('border-parish-600 ring-2 ring-parish-100 bg-parish-50');

    $('#booking-type').val(selectedSlot.booking_type);
    $('#schedule-start').val(selectedSlot.datetime);
    $('#schedule-rule-id').val(selectedSlot.schedule_rule_id);

    var typeLabel = selectedSlot.booking_type === 'regular' ? 'Regular / Parish Schedule' : 'Special Booking';
    $('#selected-schedule-label').text(selectedSlot.date_label + ' · ' + selectedSlot.time);
    $('#selected-schedule-meta').text(typeLabel + ' · ' + (selectedSlot.fee === 0 ? 'FREE' : peso(selectedSlot.fee)));
    $('#schedule-summary').removeClass('hidden');
    $('#submit-booking').prop('disabled', false);
    $('#submit-help').text(selectedSlot.fee === 0 ? 'No booking fee is assigned to this selected schedule.' : 'Applicable booking fee: ' + peso(selectedSlot.fee));
  });
}

function clearSlotSelection(){
  selectedSlot = null;
  $('#schedule-start, #schedule-rule-id').val('');
  $('.available-slot').removeClass('border-parish-600 ring-2 ring-parish-100 bg-parish-50');
  $('#schedule-summary').addClass('hidden');
  $('#submit-booking').prop('disabled', true);
  $('#submit-help').text('Choose an available schedule first.');
}

function loadRegularSlots(){
  $('#regular-slots').html(
    '<div class="sm:col-span-2 lg:col-span-3 py-8 text-center text-sm text-gray-400"><i class="ph ph-spinner-gap animate-spin mr-1"></i> Checking parish availability…</div>'
  );
  $('#regular-message').addClass('hidden');

  $.post('<?= site_url('my/bookings/availability') ?>', {
    service_type_id: <?= (int) $service['id'] ?>,
    booking_type: 'regular'
  }, function(res){
    $('#regular-slots').empty();
    if(res.success && res.slots && res.slots.length){
      res.slots.forEach(function(slot){
        $('#regular-slots').append(renderSlotButton(slot, 'regular'));
      });
      bindSlotButtons();
    } else {
      $('#regular-message').removeClass('hidden').text(res.message || 'No regular slots are currently available.');
    }
  }).fail(function(){
    $('#regular-slots').empty();
    $('#regular-message').removeClass('hidden').text('Unable to check availability. Please try again.');
  });
}

function loadSpecialDates(){
  clearSlotSelection();
  $('#booking-type').val('special');
  $('#special-dates').html('<div class="col-span-2 sm:col-span-3 py-8 text-center text-sm text-gray-400"><i class="ph ph-spinner-gap animate-spin mr-1"></i> Checking available dates…</div>');
  $('#special-date-message').addClass('hidden');
  $('#special-slots').empty();
  $('#special-message').removeClass('hidden').text('Select one of the available dates to see its open times.');
  $('#special-time-help').text('Choose an available date first.');

  $.post('<?= site_url('my/bookings/availability') ?>', {
    service_type_id: <?= (int) $service['id'] ?>,
    booking_type: 'special'
  }, function(res){
    $('#special-dates').empty();
    if(res.success && res.dates && res.dates.length){
      res.dates.forEach(function(item){
        $('#special-dates').append(
          '<button type="button" class="available-special-date text-left rounded-xl border border-gray-200 bg-white p-4 hover:border-gold-300 hover:shadow-sm transition" data-date="' + escapeHtml(item.date) + '" data-label="' + escapeHtml(item.date_label) + '">' +
            '<div class="flex items-center gap-3">' +
              '<div class="w-12 h-12 rounded-xl bg-gold-50 text-gold-700 flex flex-col items-center justify-center flex-shrink-0">' +
                '<span class="text-[9px] uppercase font-bold">' + escapeHtml(item.month_label) + '</span>' +
                '<span class="text-xl font-bold leading-none">' + escapeHtml(item.day_number) + '</span>' +
              '</div>' +
              '<div class="min-w-0">' +
                '<div class="text-sm font-semibold text-gray-800">' + escapeHtml(item.date_label) + '</div>' +
                '<div class="text-[11px] text-gray-400 mt-1">' + escapeHtml(item.available_count) + ' open time' + (parseInt(item.available_count,10) === 1 ? '' : 's') + '</div>' +
              '</div>' +
            '</div>' +
          '</button>'
        );
      });

      $('.available-special-date').off('click').on('click', function(){
        $('.available-special-date').removeClass('border-gold-500 ring-2 ring-gold-100 bg-gold-50');
        $(this).addClass('border-gold-500 ring-2 ring-gold-100 bg-gold-50');
        loadSpecialSlots($(this).data('date'), $(this).data('label'));
      });
    } else {
      $('#special-date-message').removeClass('hidden').text(res.message || 'No special-booking dates are currently available.');
    }
  }).fail(function(){
    $('#special-dates').empty();
    $('#special-date-message').removeClass('hidden').text('Unable to check available dates. Please try again.');
  });
}

function loadSpecialSlots(date, dateLabel){
  clearSlotSelection();
  $('#booking-type').val('special');
  $('#special-slots').html('<div class="col-span-2 sm:col-span-3 py-8 text-center text-sm text-gray-400"><i class="ph ph-spinner-gap animate-spin mr-1"></i> Checking church calendar…</div>');
  $('#special-message').addClass('hidden');
  $('#special-time-help').text('Open times for ' + dateLabel);

  $.post('<?= site_url('my/bookings/availability') ?>', {
    service_type_id: <?= (int) $service['id'] ?>,
    booking_type: 'special',
    date: date
  }, function(res){
    $('#special-slots').empty();
    if(res.success && res.slots && res.slots.length){
      res.slots.forEach(function(slot){
        $('#special-slots').append(renderSlotButton(slot, 'special'));
      });
      bindSlotButtons();
    } else {
      $('#special-message').removeClass('hidden').text(res.message || 'No available times remain on this date. Please choose another date.');
      loadSpecialDates();
    }
  }).fail(function(){
    $('#special-slots').empty();
    $('#special-message').removeClass('hidden').text('Unable to check availability. Please try again.');
  });
}

$('#booking-form').on('submit', function(e){
  e.preventDefault();

  if(!selectedSlot){
    Swal.fire({
      icon:'warning',
      title:'Choose an available schedule',
      text:'Select a date and time before submitting your application.',
      confirmButtonColor:'#235a38'
    });
    return;
  }

  var scheduleText = selectedSlot.date_label + ' at ' + selectedSlot.time;
  var feeText = selectedSlot.fee === 0 ? 'FREE' : peso(selectedSlot.fee);

  Swal.fire({
    icon:'question',
    title:'Submit this booking?',
    html:'<div class="text-sm text-gray-600">Schedule: <strong>' + escapeHtml(scheduleText) + '</strong><br>Booking fee: <strong>' + escapeHtml(feeText) + '</strong></div>',
    showCancelButton:true,
    confirmButtonText:'Submit Application',
    confirmButtonColor:'#235a38',
    cancelButtonText:'Review'
  }).then(function(result){
    if(!result.isConfirmed) return;

    var formData = new FormData(document.getElementById('booking-form'));
    $('#submit-booking').prop('disabled', true);

    $.ajax({
      url:'<?= site_url('my/bookings/store') ?>',
      type:'POST',
      data:formData,
      processData:false,
      contentType:false
    }).done(function(res){
      if(res.success){
        Swal.fire({
          icon:'success',
          title:'Application Submitted',
          text:'Your schedule has been reserved while the parish office reviews your application.',
          confirmButtonColor:'#235a38'
        }).then(function(){ window.location.href = res.redirect; });
      } else {
        $('#submit-booking').prop('disabled', false);
        Swal.fire({
          icon:'error',
          title:'Schedule unavailable',
          text:res.message,
          confirmButtonColor:'#235a38'
        });
        if(res.message && res.message.toLowerCase().indexOf('available') !== -1){
          clearSlotSelection();
          if(currentBookingType === 'regular') loadRegularSlots();
          else loadSpecialDates();
        }
      }
    }).fail(function(){
      $('#submit-booking').prop('disabled', false);
      Swal.fire({icon:'error', title:'Unable to submit', text:'Please try again.', confirmButtonColor:'#235a38'});
    });
  });
});

$(function(){
  <?php if ($has_regular): ?>
    setBookingType('regular');
  <?php elseif ($allow_special): ?>
    setBookingType('special');
  <?php endif; ?>
});
</script>
