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
          <i class="ph <?= $service['service_key'] === 'wedding' ? 'ph-heart' : ($service['service_key'] === 'baptism' ? 'ph-drop' : 'ph-calendar-check') ?>"></i>
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
        <?php if (!empty($service['requires_priest'])): ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-white/10 border border-white/10 text-white/80 text-xs font-semibold">
            <i class="ph ph-users"></i> <?= (int) $active_priest_count ?> active priest<?= (int) $active_priest_count === 1 ? '' : 's' ?>
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <form id="booking-form" enctype="multipart/form-data" class="mt-6" novalidate>
    <div id="booking-wizard-progress" class="relative z-10 mb-5 rounded-2xl border border-gray-100 bg-white shadow-sm px-4 sm:px-6 py-4">
      <div class="grid grid-cols-3 gap-2 sm:gap-4">
        <button type="button" class="wizard-progress-item flex items-center gap-2 sm:gap-3 text-left" data-progress-step="1" onclick="goToCompletedStep(1)">
          <span class="wizard-progress-number w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-parish-700 text-white flex items-center justify-center font-bold text-xs sm:text-sm flex-shrink-0">1</span>
          <span class="min-w-0">
            <span class="wizard-progress-label block text-[11px] sm:text-sm font-semibold text-parish-800 truncate">Schedule</span>
            <span class="hidden sm:block text-[10px] text-gray-400 mt-0.5">Choose availability</span>
          </span>
        </button>

        <button type="button" class="wizard-progress-item flex items-center gap-2 sm:gap-3 text-left opacity-45 cursor-default" data-progress-step="2" onclick="goToCompletedStep(2)">
          <span class="wizard-progress-number w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-xs sm:text-sm flex-shrink-0">2</span>
          <span class="min-w-0">
            <span class="wizard-progress-label block text-[11px] sm:text-sm font-semibold text-gray-500 truncate">Details</span>
            <span class="hidden sm:block text-[10px] text-gray-400 mt-0.5">Booking information</span>
          </span>
        </button>

        <button type="button" class="wizard-progress-item flex items-center gap-2 sm:gap-3 text-left opacity-45 cursor-default" data-progress-step="3" onclick="goToCompletedStep(3)">
          <span class="wizard-progress-number w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-xs sm:text-sm flex-shrink-0">3</span>
          <span class="min-w-0">
            <span class="wizard-progress-label block text-[11px] sm:text-sm font-semibold text-gray-500 truncate">Review</span>
            <span class="hidden sm:block text-[10px] text-gray-400 mt-0.5">Requirements & submit</span>
          </span>
        </button>
      </div>
      <div class="mt-3 h-1.5 bg-gray-100 rounded-full overflow-hidden">
        <div id="wizard-progress-bar" class="h-full bg-parish-700 rounded-full transition-all duration-300" style="width:33.333%"></div>
      </div>
    </div>
    <input type="hidden" name="service_type_id" value="<?= $service['id'] ?>">
    <input type="hidden" name="booking_type" id="booking-type">
    <input type="hidden" name="schedule_start" id="schedule-start">
    <input type="hidden" name="schedule_rule_id" id="schedule-rule-id">

    <!-- Step 1: availability -->
    <section class="wizard-step bg-white rounded-2xl border border-gray-100 overflow-hidden" data-step="1">
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
                  <h3 class="font-semibold text-gray-900">Parish Regular Schedule</h3>
                  <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold">FREE / SET FEE</span>
                </div>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">Choose this if one of the parish’s regular date-and-time schedules works for you. The published regular fee applies, including FREE schedules.</p>
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
                  <h3 class="font-semibold text-gray-900">Other Available Date &amp; Time</h3>
                  <span class="px-2.5 py-1 rounded-full bg-gold-100 text-gold-700 text-[11px] font-bold"><?= peso($service['special_fee']) ?></span>
                </div>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">Need a different schedule? Pick another open date and time directly. The parish’s Special Booking fee applies.</p>
              </div>
            </div>
          </button>
          <?php endif; ?>
        </div>

        <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/60 p-4 sm:p-5">
          <div class="flex gap-3">
            <div class="w-10 h-10 rounded-xl bg-white text-blue-700 flex items-center justify-center flex-shrink-0"><i class="ph ph-shield-check text-xl"></i></div>
            <div class="text-xs text-blue-900/80 leading-relaxed">
              <div class="font-semibold text-blue-900">We protect more than just the ceremony time.</div>
              <p class="mt-1">Choose the ceremony start you prefer. The system also reserves the configured preparation time before and clearance time after it, checks the Main Church calendar, and verifies that one of the <?= (int) $active_priest_count ?> available priest<?= (int) $active_priest_count === 1 ? '' : 's' ?> can serve the schedule.</p>
            </div>
          </div>
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
                <div class="text-[11px] uppercase tracking-wider font-semibold text-gold-700">Available special schedules</div>
                <p class="text-xs text-gray-500 mt-1">Each choice already includes both the date and time. Conflicting Main Church reservations and unavailable priests are removed automatically.</p>
              </div>
              <div class="text-sm font-bold text-gold-800 whitespace-nowrap"><?= peso($service['special_fee']) ?> special fee</div>
            </div>
          </div>

          <div class="flex items-center justify-between gap-3 mb-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Next available date &amp; time</h3>
              <p class="text-xs text-gray-400 mt-0.5">Tap one schedule to reserve it while your application is reviewed.</p>
            </div>
            <button type="button" onclick="loadSpecialSchedules()" class="text-xs font-semibold text-parish-700 hover:text-parish-900"><i class="ph ph-arrows-clockwise"></i> Refresh</button>
          </div>
          <div id="special-slots" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3"></div>
          <div id="special-message" class="hidden rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-500"></div>
        </div>

        <!-- Selection summary -->
        <div id="schedule-advisory" class="hidden mt-6"></div>
        <div id="schedule-summary" class="hidden mt-4 rounded-2xl bg-parish-900 text-white p-5">
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

        <div class="mt-6 pt-5 border-t border-gray-100 flex justify-end">
          <button id="step1-next" type="button" onclick="nextStep()" disabled class="w-full sm:w-auto sm:min-w-[180px] inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed transition">
            Next: Details <i class="ph ph-arrow-right"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- Step 2: booking details -->
    <section class="wizard-step hidden bg-white rounded-2xl border border-gray-100 overflow-hidden" data-step="2">
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

        <div class="pt-5 mt-2 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
          <button type="button" onclick="previousStep()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition">
            <i class="ph ph-arrow-left"></i> Back
          </button>
          <button type="button" onclick="nextStep()" class="w-full sm:w-auto sm:min-w-[210px] inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold transition">
            Next: Requirements <i class="ph ph-arrow-right"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- Step 3: requirements and submit -->
    <section class="wizard-step hidden bg-white rounded-2xl border border-gray-100 overflow-hidden" data-step="3">
      <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center gap-4">
        <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center font-bold text-sm">3</div>
        <div>
          <h2 class="font-semibold text-gray-900">Requirements &amp; submission</h2>
          <p class="text-xs text-gray-400 mt-0.5">Upload available documents now; the parish office will review the application.</p>
        </div>
      </div>

      <div class="p-6 sm:p-8">
        <div class="mb-6 rounded-2xl bg-parish-50 border border-parish-100 p-5">
          <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl bg-white text-parish-700 border border-parish-100 flex items-center justify-center text-xl flex-shrink-0">
              <i class="ph ph-calendar-check"></i>
            </div>
            <div class="min-w-0 flex-1">
              <div class="text-[10px] uppercase tracking-wider text-parish-600 font-bold">Booking Summary</div>
              <div class="font-semibold text-gray-900 mt-1"><?= html_escape($service['name']) ?></div>
              <div id="review-schedule" class="text-sm text-gray-600 mt-1">Schedule not selected</div>
              <div class="flex flex-wrap gap-2 mt-3">
                <span id="review-type" class="px-2.5 py-1 rounded-full bg-white border border-parish-100 text-[11px] font-semibold text-parish-700">—</span>
                <span id="review-fee" class="px-2.5 py-1 rounded-full bg-white border border-parish-100 text-[11px] font-semibold text-parish-700">—</span>
              </div>
            </div>
            <button type="button" onclick="showWizardStep(1)" class="text-xs font-semibold text-parish-700 hover:text-parish-900 flex-shrink-0">Change</button>
          </div>
        </div>

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

        <div class="mt-6 border-t border-gray-100 pt-6 flex flex-col-reverse sm:flex-row sm:items-end sm:justify-between gap-4">
          <button type="button" onclick="previousStep()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition">
            <i class="ph ph-arrow-left"></i> Back
          </button>
          <div class="w-full sm:w-auto sm:text-right">
            <button id="submit-booking" type="submit" disabled class="w-full sm:w-auto sm:min-w-[240px] py-3.5 px-6 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed transition">
              <i class="ph ph-paper-plane-tilt mr-1"></i> Submit Application
            </button>
            <p id="submit-help" class="text-xs text-gray-400 mt-2">Choose an available schedule first.</p>
          </div>
        </div>
      </div>
    </section>
  </form>
</div>

<script>
var selectedSlot = null;
var currentBookingType = null;
var currentWizardStep = 1;
var maxReachedStep = 1;

function scrollToWizard(){
  var el = document.getElementById('booking-wizard-progress');
  if(!el) return;
  var y = el.getBoundingClientRect().top + window.pageYOffset - 76;
  window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
}

function updateWizardProgress(){
  $('.wizard-progress-item').each(function(){
    var step = parseInt($(this).data('progress-step'), 10);
    var $number = $(this).find('.wizard-progress-number');
    var $label = $(this).find('.wizard-progress-label');

    $(this).removeClass('opacity-45 cursor-default');
    $number.removeClass('bg-parish-700 text-white bg-emerald-100 text-emerald-700 bg-gray-100 text-gray-400');
    $label.removeClass('text-parish-800 text-emerald-700 text-gray-500');

    if(step === currentWizardStep){
      $number.addClass('bg-parish-700 text-white');
      $label.addClass('text-parish-800');
    } else if(step <= maxReachedStep){
      $number.addClass('bg-emerald-100 text-emerald-700');
      $label.addClass('text-emerald-700');
    } else {
      $(this).addClass('opacity-45 cursor-default');
      $number.addClass('bg-gray-100 text-gray-400');
      $label.addClass('text-gray-500');
    }
  });

  $('#wizard-progress-bar').css('width', (currentWizardStep * 33.333) + '%');
}

function showWizardStep(step, shouldScroll){
  currentWizardStep = Math.max(1, Math.min(3, parseInt(step, 10) || 1));
  $('.wizard-step').addClass('hidden');
  $('.wizard-step[data-step="' + currentWizardStep + '"]').removeClass('hidden');
  updateWizardProgress();
  if(shouldScroll !== false) scrollToWizard();
}

function validateDetailsStep(){
  var valid = true;
  var firstInvalid = null;

  $('.wizard-step[data-step="2"] [required]').each(function(){
    var empty = String($(this).val() == null ? '' : $(this).val()).trim() === '';
    var browserInvalid = this.validity ? !this.validity.valid : false;

    $(this).removeClass('border-red-400 ring-2 ring-red-100');

    if(empty || browserInvalid){
      valid = false;
      $(this).addClass('border-red-400 ring-2 ring-red-100');
      if(!firstInvalid) firstInvalid = this;
    }
  });

  if(!valid){
    Swal.fire({
      icon:'warning',
      title:'Complete the required details',
      text:'Please fill in the highlighted fields before continuing.',
      confirmButtonColor:'#235a38'
    }).then(function(){
      if(firstInvalid) firstInvalid.focus();
    });
  }

  return valid;
}

function nextStep(){
  if(currentWizardStep === 1){
    if(!selectedSlot){
      Swal.fire({
        icon:'warning',
        title:'Choose an available schedule',
        text:'Select an available date and time before continuing.',
        confirmButtonColor:'#235a38'
      });
      return;
    }
  }

  if(currentWizardStep === 2 && !validateDetailsStep()){
    return;
  }

  if(currentWizardStep < 3){
    maxReachedStep = Math.max(maxReachedStep, currentWizardStep + 1);
    showWizardStep(currentWizardStep + 1);
  }
}

function previousStep(){
  if(currentWizardStep > 1){
    showWizardStep(currentWizardStep - 1);
  }
}

function goToCompletedStep(step){
  step = parseInt(step, 10) || 1;
  if(step <= maxReachedStep){
    showWizardStep(step);
  }
}

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
    loadSpecialSchedules();
  }
}

function renderSlotButton(slot, type){
  var feeText = Number(slot.fee || 0) === 0 ? 'FREE' : peso(slot.fee);
  var remaining = parseInt(slot.remaining || 1, 10);
  var nearby = Array.isArray(slot.nearby_bookings) ? slot.nearby_bookings : [];
  var capacityText = parseInt(slot.capacity || 1,10) > 1
    ? '<div class="text-[10px] text-gray-400 mt-1">' + remaining + ' place' + (remaining === 1 ? '' : 's') + ' left</div>'
    : '';
  var protectedText = slot.reserved_from && slot.reserved_until
    ? '<div class="text-[10px] text-gray-500 mt-2 flex items-center gap-1"><i class="ph ph-shield-check text-parish-600"></i> Church protected ' + escapeHtml(slot.reserved_from) + '–' + escapeHtml(slot.reserved_until) + '</div>'
    : '';
  var priestText = parseInt(slot.priest_total || 0,10) > 0
    ? '<div class="text-[10px] text-gray-500 mt-1 flex items-center gap-1"><i class="ph ph-users text-parish-600"></i> Priest checked · ' + escapeHtml(slot.priests_available_for_slot) + ' of ' + escapeHtml(slot.priest_total) + ' available</div>'
    : '';
  var nearbyText = nearby.length
    ? '<div class="mt-2 rounded-lg bg-amber-50 border border-amber-100 px-2.5 py-2 text-[10px] font-medium text-amber-800"><i class="ph ph-info mr-1"></i> Another church service is scheduled nearby</div>'
    : '';

  return '<button type="button" class="available-slot text-left rounded-2xl border border-gray-200 bg-white p-4 hover:border-parish-300 hover:shadow-sm transition" ' +
    'data-type="' + escapeHtml(type) + '" ' +
    'data-datetime="' + escapeHtml(slot.datetime) + '" ' +
    'data-rule="' + escapeHtml(slot.schedule_rule_id || '') + '" ' +
    'data-fee="' + escapeHtml(slot.fee) + '" ' +
    'data-date-label="' + escapeHtml(slot.date_label) + '" ' +
    'data-time="' + escapeHtml(slot.time) + '" ' +
    'data-reserved-from="' + escapeHtml(slot.reserved_from || '') + '" ' +
    'data-reserved-until="' + escapeHtml(slot.reserved_until || '') + '" ' +
    'data-priest-total="' + escapeHtml(slot.priest_total || 0) + '" ' +
    'data-priests-available="' + escapeHtml(slot.priests_available_for_slot || 0) + '" ' +
    'data-nearby="' + escapeHtml(encodeURIComponent(JSON.stringify(nearby))) + '">' +
      '<div class="flex items-start justify-between gap-2">' +
        '<div>' +
          '<div class="text-sm font-semibold text-gray-800">' + escapeHtml(slot.date_label) + '</div>' +
          '<div class="text-xl font-bold text-parish-800 mt-1">' + escapeHtml(slot.time) + '</div>' +
        '</div>' +
        '<span class="text-[10px] font-bold px-2 py-1 rounded-full ' + (Number(slot.fee || 0) === 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gold-100 text-gold-700') + '">' + feeText + '</span>' +
      '</div>' +
      '<div class="text-[11px] text-gray-400 mt-2">' + escapeHtml(slot.rule_name || (type === 'regular' ? 'Regular Schedule' : 'Special Booking')) + '</div>' +
      protectedText + priestText + capacityText + nearbyText +
    '</button>';
}

function renderScheduleAdvisory(slot){
  var nearby = Array.isArray(slot.nearby_bookings) ? slot.nearby_bookings : [];
  var $box = $('#schedule-advisory').empty();

  if(!nearby.length){
    $box.addClass('hidden');
    return;
  }

  var html = '<div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:p-5">' +
    '<div class="flex gap-3">' +
      '<div class="w-10 h-10 rounded-xl bg-white text-amber-700 flex items-center justify-center flex-shrink-0"><i class="ph ph-info text-xl"></i></div>' +
      '<div class="min-w-0 flex-1">' +
        '<div class="font-semibold text-amber-900 text-sm">Another church service is scheduled nearby</div>' +
        '<p class="text-xs text-amber-800/80 mt-1">Your selected time is still available. This notice helps you plan arrival, pictorials, and departure without being surprised by the next parish activity.</p>' +
        '<div class="mt-3 space-y-2">';

  nearby.forEach(function(item){
    if(item.direction === 'after'){
      html += '<div class="rounded-xl bg-white/75 border border-amber-100 px-3 py-2.5 text-xs text-gray-700">' +
        '<strong>' + escapeHtml(item.service_name) + '</strong> is scheduled after you at <strong>' + escapeHtml(item.ceremony_time) + '</strong>. ' +
        'Your protected church time ends at <strong>' + escapeHtml(slot.reserved_until) + '</strong>' +
        (item.reserved_from ? ', while the next protected church time begins at <strong>' + escapeHtml(item.reserved_from) + '</strong>.' : '.') +
      '</div>';
    } else {
      html += '<div class="rounded-xl bg-white/75 border border-amber-100 px-3 py-2.5 text-xs text-gray-700">' +
        '<strong>' + escapeHtml(item.service_name) + '</strong> is scheduled before you at <strong>' + escapeHtml(item.ceremony_time) + '</strong>. ' +
        'Its protected church time ends at <strong>' + escapeHtml(item.reserved_until) + '</strong>, before your protected time begins at <strong>' + escapeHtml(slot.reserved_from) + '</strong>.' +
      '</div>';
    }
  });

  html += '</div></div></div></div>';
  $box.html(html).removeClass('hidden');
}

function bindSlotButtons(){
  $('.available-slot').off('click').on('click', function(){
    var $btn = $(this);
    var nearby = [];
    try {
      nearby = JSON.parse(decodeURIComponent(String($btn.attr('data-nearby') || '%5B%5D')));
    } catch(e) {
      nearby = [];
    }

    selectedSlot = {
      booking_type: $btn.data('type'),
      datetime: $btn.data('datetime'),
      schedule_rule_id: $btn.data('rule') || '',
      fee: Number($btn.data('fee') || 0),
      date_label: $btn.data('date-label'),
      time: $btn.data('time'),
      reserved_from: $btn.data('reserved-from') || '',
      reserved_until: $btn.data('reserved-until') || '',
      priest_total: Number($btn.data('priest-total') || 0),
      priests_available_for_slot: Number($btn.data('priests-available') || 0),
      nearby_bookings: nearby
    };

    $('.available-slot').removeClass('border-parish-600 ring-2 ring-parish-100 bg-parish-50');
    $btn.addClass('border-parish-600 ring-2 ring-parish-100 bg-parish-50');

    $('#booking-type').val(selectedSlot.booking_type);
    $('#schedule-start').val(selectedSlot.datetime);
    $('#schedule-rule-id').val(selectedSlot.schedule_rule_id);

    var typeLabel = selectedSlot.booking_type === 'regular' ? 'Regular / Parish Schedule' : 'Special Booking';
    var protectedLabel = selectedSlot.reserved_from && selectedSlot.reserved_until
      ? ' · Protected ' + selectedSlot.reserved_from + '–' + selectedSlot.reserved_until
      : '';

    $('#selected-schedule-label').text(selectedSlot.date_label + ' · ' + selectedSlot.time);
    $('#selected-schedule-meta').text(typeLabel + ' · ' + (selectedSlot.fee === 0 ? 'FREE' : peso(selectedSlot.fee)) + protectedLabel);
    $('#review-schedule').text(selectedSlot.date_label + ' · ' + selectedSlot.time + protectedLabel);
    $('#review-type').text(typeLabel);
    $('#review-fee').text(selectedSlot.fee === 0 ? 'FREE' : peso(selectedSlot.fee));
    renderScheduleAdvisory(selectedSlot);
    $('#schedule-summary').removeClass('hidden');
    $('#step1-next').prop('disabled', false);
    $('#submit-booking').prop('disabled', false);
    $('#submit-help').text(selectedSlot.fee === 0 ? 'No booking fee is assigned to this selected schedule.' : 'Applicable booking fee: ' + peso(selectedSlot.fee));
  });
}

function clearSlotSelection(){
  selectedSlot = null;
  $('#schedule-start, #schedule-rule-id').val('');
  $('.available-slot').removeClass('border-parish-600 ring-2 ring-parish-100 bg-parish-50');
  $('#schedule-summary, #schedule-advisory').addClass('hidden');
  $('#schedule-advisory').empty();
  $('#review-schedule').text('Schedule not selected');
  $('#review-type, #review-fee').text('—');
  $('#step1-next').prop('disabled', true);
  $('#submit-booking').prop('disabled', true);
  if(currentWizardStep > 1){
    maxReachedStep = 1;
    showWizardStep(1, false);
  }
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

function loadSpecialSchedules(){
  clearSlotSelection();
  $('#booking-type').val('special');
  $('#special-slots').html('<div class="sm:col-span-2 lg:col-span-3 py-8 text-center text-sm text-gray-400"><i class="ph ph-spinner-gap animate-spin mr-1"></i> Finding available date-and-time schedules…</div>');
  $('#special-message').addClass('hidden');

  $.post('<?= site_url('my/bookings/availability') ?>', {
    service_type_id: <?= (int) $service['id'] ?>,
    booking_type: 'special'
  }, function(res){
    $('#special-slots').empty();
    if(res.success && res.slots && res.slots.length){
      res.slots.forEach(function(slot){
        $('#special-slots').append(renderSlotButton(slot, 'special'));
      });
      bindSlotButtons();
    } else {
      $('#special-message').removeClass('hidden').text(res.message || 'No special-booking schedules are currently available.');
    }
  }).fail(function(){
    $('#special-slots').empty();
    $('#special-message').removeClass('hidden').text('Unable to check availability. Please try again.');
  });
}

$('#booking-form').on('submit', function(e){
  e.preventDefault();

  if(currentWizardStep !== 3){
    return;
  }

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
  var confirmHtml = '<div class="text-sm text-gray-600">' +
    'Ceremony schedule: <strong>' + escapeHtml(scheduleText) + '</strong><br>' +
    'Booking fee: <strong>' + escapeHtml(feeText) + '</strong>';

  if(selectedSlot.reserved_from && selectedSlot.reserved_until){
    confirmHtml += '<br>Protected church time: <strong>' + escapeHtml(selectedSlot.reserved_from) + '–' + escapeHtml(selectedSlot.reserved_until) + '</strong>';
  }

  if(selectedSlot.nearby_bookings && selectedSlot.nearby_bookings.length){
    confirmHtml += '<div class="mt-3 rounded-lg bg-amber-50 border border-amber-100 p-3 text-left text-xs text-amber-800">' +
      '<strong>Planning note:</strong> another church service is scheduled nearby. Review the notice shown above before submitting.' +
    '</div>';
  }

  confirmHtml += '</div>';

  Swal.fire({
    icon:'question',
    title:'Submit this booking?',
    html:confirmHtml,
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
          else loadSpecialSchedules();
        }
      }
    }).fail(function(){
      $('#submit-booking').prop('disabled', false);
      Swal.fire({icon:'error', title:'Unable to submit', text:'Please try again.', confirmButtonColor:'#235a38'});
    });
  });
});

$(function(){
  showWizardStep(1, false);

  $('.wizard-step[data-step="2"] [required]').on('input change', function(){
    $(this).removeClass('border-red-400 ring-2 ring-red-100');
  });

  <?php if ($has_regular): ?>
    setBookingType('regular');
  <?php elseif ($allow_special): ?>
    setBookingType('special');
  <?php endif; ?>
});
</script>
