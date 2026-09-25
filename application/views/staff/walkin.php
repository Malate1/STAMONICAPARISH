<?php
$catalog_json = json_encode(
    $service_catalog,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
);
?>

<div class="max-w-6xl mx-auto">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div>
      <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Parish Office Intake</div>
      <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Record Walk-in Booking</h1>
      <p class="text-gray-500 text-sm mt-1 max-w-3xl">Use the same service availability, church-conflict protection, priest-capacity checks, requirements, and review workflow used by online parishioner bookings.</p>
    </div>
    <a href="<?= site_url('staff/booking') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 text-sm font-semibold hover:bg-gray-50">
      <i class="ph ph-arrow-left"></i> Booking List
    </a>
  </div>

  <div class="rounded-[1.75rem] bg-gradient-to-r from-parish-900 via-parish-800 to-parish-700 text-white p-6 sm:p-8 shadow-sm mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center text-2xl flex-shrink-0">
          <i class="ph ph-user-plus"></i>
        </div>
        <div>
          <div class="text-xs uppercase tracking-[.14em] text-gold-200 font-semibold">Walk-in ≠ Manual Bypass</div>
          <h2 class="text-xl sm:text-2xl font-bold mt-1">The parish office now follows the online booking rules.</h2>
          <p class="text-sm text-white/65 mt-2 max-w-3xl">A walk-in parishioner gets an actual available date and time—not an arbitrary preferred date. The selected slot is rechecked before saving so it cannot silently conflict with another protected church booking.</p>
        </div>
      </div>

      <div class="flex flex-wrap gap-2 md:justify-end flex-shrink-0">
        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-white/10 border border-white/10 text-white/80 text-xs font-semibold">
          <i class="ph ph-shield-check"></i> Same availability engine
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-gold-400/15 border border-gold-300/20 text-gold-100 text-xs font-semibold">
          <i class="ph ph-users"></i> <?= (int)$active_priest_count ?> active priest<?= (int)$active_priest_count === 1 ? '' : 's' ?>
        </span>
      </div>
    </div>
  </div>

  <form id="walkin-form" enctype="multipart/form-data" novalidate>
    <div id="walkin-wizard-progress" class="relative z-10 mb-5 rounded-2xl border border-gray-100 bg-white shadow-sm px-4 sm:px-6 py-4">
      <div class="grid grid-cols-4 gap-2 sm:gap-4">
        <?php
        $steps = [
            1 => ['Parishioner', 'Identify / link account'],
            2 => ['Schedule', 'Service & availability'],
            3 => ['Details', 'Service information'],
            4 => ['Review', 'Requirements & submit'],
        ];
        foreach ($steps as $num => $meta):
        ?>
          <button type="button" class="wizard-progress-item flex items-center gap-2 sm:gap-3 text-left <?= $num === 1 ? '' : 'opacity-45 cursor-default' ?>" data-progress-step="<?= $num ?>" onclick="goToCompletedStep(<?= $num ?>)">
            <span class="wizard-progress-number w-8 h-8 sm:w-9 sm:h-9 rounded-xl <?= $num === 1 ? 'bg-parish-700 text-white' : 'bg-gray-100 text-gray-400' ?> flex items-center justify-center font-bold text-xs sm:text-sm flex-shrink-0"><?= $num ?></span>
            <span class="min-w-0">
              <span class="wizard-progress-label block text-[10px] sm:text-sm font-semibold <?= $num === 1 ? 'text-parish-800' : 'text-gray-500' ?> truncate"><?= $meta[0] ?></span>
              <span class="hidden lg:block text-[10px] text-gray-400 mt-0.5 truncate"><?= $meta[1] ?></span>
            </span>
          </button>
        <?php endforeach; ?>
      </div>
      <div class="mt-3 h-1.5 bg-gray-100 rounded-full overflow-hidden">
        <div id="wizard-progress-bar" class="h-full bg-parish-700 rounded-full transition-all duration-300" style="width:25%"></div>
      </div>
    </div>

    <input type="hidden" name="booking_type" id="booking-type">
    <input type="hidden" name="schedule_start" id="schedule-start">
    <input type="hidden" name="schedule_rule_id" id="schedule-rule-id">

    <!-- Step 1 -->
    <section class="wizard-step bg-white rounded-2xl border border-gray-100 overflow-hidden" data-step="1">
      <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center gap-4">
        <div class="w-9 h-9 rounded-xl bg-parish-700 text-white flex items-center justify-center font-bold text-sm">1</div>
        <div>
          <h2 class="font-semibold text-gray-900">Identify the parishioner</h2>
          <p class="text-xs text-gray-400 mt-0.5">Existing Parishioner accounts are linked. Otherwise, the system creates a Parishioner profile for the walk-in transaction.</p>
        </div>
      </div>

      <div class="p-6 sm:p-8">
        <div class="grid md:grid-cols-2 gap-5">
          <div>
            <label class="text-xs font-semibold text-gray-600">Full Name</label>
            <input required name="full_name" id="walkin-full-name" autocomplete="name" placeholder="Parishioner's full name" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
          </div>

          <div>
            <label class="text-xs font-semibold text-gray-600">Email Address</label>
            <div class="mt-1.5 flex gap-2">
              <input required type="email" name="email" id="walkin-email" autocomplete="email" placeholder="name@example.com" class="min-w-0 flex-1 px-4 py-3 rounded-xl border border-gray-200 text-sm">
              <button type="button" onclick="lookupParishioner(false)" class="px-3.5 py-3 rounded-xl border border-parish-100 bg-parish-50 text-parish-700 text-xs font-semibold hover:bg-parish-100 whitespace-nowrap">
                <i class="ph ph-magnifying-glass mr-1"></i> Check
              </button>
            </div>
          </div>

          <div>
            <label class="text-xs font-semibold text-gray-600">Mobile Number <span class="text-gray-300">(optional)</span></label>
            <input name="mobile_number" id="walkin-mobile" autocomplete="tel" placeholder="09xx xxx xxxx" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
          </div>

          <div class="rounded-2xl border border-gray-100 bg-gray-50/60 p-4">
            <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Account Handling</div>
            <p class="text-xs text-gray-500 mt-2 leading-relaxed">If the email belongs to an existing Parishioner, this booking is attached to that profile. Staff/Admin/Priest emails cannot be reused for a walk-in parishioner booking.</p>
          </div>
        </div>

        <div id="account-lookup-result" class="hidden mt-5 rounded-2xl p-4"></div>

        <div class="mt-6 pt-5 border-t border-gray-100 flex justify-end">
          <button type="button" onclick="nextStep()" class="w-full sm:w-auto sm:min-w-[210px] inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold">
            Next: Service &amp; Schedule <i class="ph ph-arrow-right"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- Step 2 -->
    <section class="wizard-step hidden bg-white rounded-2xl border border-gray-100 overflow-hidden" data-step="2">
      <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center gap-4">
        <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center font-bold text-sm">2</div>
        <div>
          <h2 class="font-semibold text-gray-900">Choose the service and an available schedule</h2>
          <p class="text-xs text-gray-400 mt-0.5">This uses the same live slot rules shown to parishioners booking online.</p>
        </div>
      </div>

      <div class="p-6 sm:p-8">
        <div class="grid lg:grid-cols-[.7fr_1.3fr] gap-5 items-start">
          <div>
            <label class="text-xs font-semibold text-gray-600">Parish Service</label>
            <select required name="service_type_id" id="service-type" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm">
              <option value="">Select a service…</option>
              <?php foreach ($service_types as $service): ?>
                <option value="<?= (int)$service['id'] ?>"><?= html_escape($service['name']) ?></option>
              <?php endforeach; ?>
            </select>

            <div id="service-overview" class="hidden mt-4 rounded-2xl bg-parish-50/60 border border-parish-100 p-5"></div>
          </div>

          <div id="schedule-workspace" class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/40 p-6 text-center">
            <i class="ph ph-calendar-dots text-3xl text-gray-300"></i>
            <div class="font-semibold text-gray-700 mt-3">Select a service first</div>
            <p class="text-xs text-gray-400 mt-1">Regular and special booking choices will appear here.</p>
          </div>
        </div>

        <div id="booking-type-area" class="hidden mt-6">
          <div id="booking-type-cards" class="grid md:grid-cols-2 gap-4"></div>

          <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/60 p-4 sm:p-5">
            <div class="flex gap-3">
              <div class="w-10 h-10 rounded-xl bg-white text-blue-700 flex items-center justify-center flex-shrink-0"><i class="ph ph-shield-check text-xl"></i></div>
              <div class="text-xs text-blue-900/80 leading-relaxed">
                <div class="font-semibold text-blue-900">Availability protection is active.</div>
                <p class="mt-1">Only valid future slots are shown. The engine checks the configured preparation/clearance buffer, Main Church conflicts, capacity, advance-booking rules, and priest availability when the service requires a priest.</p>
              </div>
            </div>
          </div>

          <div id="published-rules" class="hidden mt-5 rounded-2xl bg-parish-50/60 border border-parish-100 p-4"></div>

          <div id="regular-panel" class="hidden mt-6">
            <div class="flex items-center justify-between gap-3 mb-3">
              <div>
                <h3 class="text-sm font-semibold text-gray-800">Next available regular slots</h3>
                <p class="text-xs text-gray-400 mt-0.5">Full or conflicting schedules are automatically omitted.</p>
              </div>
              <button type="button" onclick="loadRegularSlots()" class="text-xs font-semibold text-parish-700 hover:text-parish-900"><i class="ph ph-arrows-clockwise"></i> Refresh</button>
            </div>
            <div id="regular-slots" class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3"></div>
            <div id="regular-message" class="hidden rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-500"></div>
          </div>

          <div id="special-panel" class="hidden mt-6">
            <div class="flex items-center justify-between gap-3 mb-3">
              <div>
                <h3 class="text-sm font-semibold text-gray-800">Next available special-booking slots</h3>
                <p class="text-xs text-gray-400 mt-0.5">Each option contains the actual date and ceremony start time.</p>
              </div>
              <button type="button" onclick="loadSpecialSlots()" class="text-xs font-semibold text-parish-700 hover:text-parish-900"><i class="ph ph-arrows-clockwise"></i> Refresh</button>
            </div>
            <div id="special-slots" class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3"></div>
            <div id="special-message" class="hidden rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-500"></div>
          </div>

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
        </div>

        <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
          <button type="button" onclick="previousStep()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50">
            <i class="ph ph-arrow-left"></i> Back
          </button>
          <button id="step2-next" type="button" onclick="nextStep()" disabled class="w-full sm:w-auto sm:min-w-[190px] inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed">
            Next: Details <i class="ph ph-arrow-right"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- Step 3 -->
    <section class="wizard-step hidden bg-white rounded-2xl border border-gray-100 overflow-hidden" data-step="3">
      <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center gap-4">
        <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center font-bold text-sm">3</div>
        <div>
          <h2 id="details-step-title" class="font-semibold text-gray-900">Service details</h2>
          <p class="text-xs text-gray-400 mt-0.5">Record the same service-specific information requested in the online application.</p>
        </div>
      </div>

      <div class="p-6 sm:p-8">
        <div id="service-details-fields" class="space-y-5"></div>

        <div class="mt-5">
          <label class="text-xs font-semibold text-gray-600">Secretary Intake Note <span class="text-gray-300">(optional)</span></label>
          <textarea name="intake_notes" rows="3" placeholder="Notes shared in person, follow-up reminders, or office intake context" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea>
        </div>

        <div class="pt-5 mt-6 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
          <button type="button" onclick="previousStep()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50">
            <i class="ph ph-arrow-left"></i> Back
          </button>
          <button type="button" onclick="nextStep()" class="w-full sm:w-auto sm:min-w-[220px] inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold">
            Next: Requirements <i class="ph ph-arrow-right"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- Step 4 -->
    <section class="wizard-step hidden bg-white rounded-2xl border border-gray-100 overflow-hidden" data-step="4">
      <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center gap-4">
        <div class="w-9 h-9 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center font-bold text-sm">4</div>
        <div>
          <h2 class="font-semibold text-gray-900">Requirements &amp; final review</h2>
          <p class="text-xs text-gray-400 mt-0.5">Attach available scans/photos, confirm the walk-in details, then create the booking.</p>
        </div>
      </div>

      <div class="p-6 sm:p-8">
        <div class="grid lg:grid-cols-[.9fr_1.1fr] gap-5 mb-6">
          <div class="rounded-2xl bg-parish-50 border border-parish-100 p-5">
            <div class="text-[10px] uppercase tracking-wider text-parish-600 font-bold">Parishioner</div>
            <div id="review-parishioner-name" class="font-semibold text-gray-900 mt-1">—</div>
            <div id="review-parishioner-contact" class="text-xs text-gray-500 mt-1">—</div>
            <button type="button" onclick="showWizardStep(1)" class="mt-3 text-xs font-semibold text-parish-700 hover:underline">Change parishioner</button>
          </div>

          <div class="rounded-2xl bg-parish-900 text-white p-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-[10px] uppercase tracking-wider text-gold-300 font-bold">Booking Summary</div>
                <div id="review-service" class="font-semibold mt-1">—</div>
                <div id="review-schedule" class="text-sm text-white/65 mt-1">Schedule not selected</div>
                <div class="flex flex-wrap gap-2 mt-3">
                  <span id="review-type" class="px-2.5 py-1 rounded-full bg-white/10 border border-white/10 text-[11px] font-semibold">—</span>
                  <span id="review-fee" class="px-2.5 py-1 rounded-full bg-gold-400/15 border border-gold-300/20 text-[11px] font-semibold text-gold-100">—</span>
                </div>
              </div>
              <button type="button" onclick="showWizardStep(2)" class="text-xs text-white/60 hover:text-white flex-shrink-0">Change</button>
            </div>
          </div>
        </div>

        <div>
          <div class="flex items-end justify-between gap-3 mb-3">
            <div>
              <h3 class="font-semibold text-gray-900">Service Requirements</h3>
              <p class="text-xs text-gray-400 mt-1">Upload copies when available. Physical originals can still be reviewed at the parish office.</p>
            </div>
          </div>

          <div id="requirements-list"></div>
        </div>

        <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50/60 p-4">
          <div class="flex gap-3">
            <i class="ph ph-info text-blue-700 text-xl mt-0.5"></i>
            <div class="text-xs text-blue-900/80 leading-relaxed">
              <strong class="text-blue-900">Walk-in bookings enter the normal booking workflow.</strong>
              The record is not automatically marked Approved or Completed. Services requiring approval still begin Under Review, and staff can continue processing requirements, priest review, payment, scheduling and completion from the Booking record.
            </div>
          </div>
        </div>

        <div class="mt-6 border-t border-gray-100 pt-6 flex flex-col-reverse sm:flex-row sm:items-end sm:justify-between gap-4">
          <button type="button" onclick="previousStep()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50">
            <i class="ph ph-arrow-left"></i> Back
          </button>

          <div class="w-full sm:w-auto sm:text-right">
            <button id="submit-walkin" type="submit" disabled class="w-full sm:w-auto sm:min-w-[250px] py-3.5 px-6 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed">
              <i class="ph ph-floppy-disk mr-1"></i> Record Walk-in Booking
            </button>
            <p id="submit-help" class="text-xs text-gray-400 mt-2">Complete the earlier wizard steps first.</p>
          </div>
        </div>
      </div>
    </section>
  </form>
</div>

<script>
var serviceCatalog = <?= $catalog_json ?: '{}' ?>;
var currentWizardStep = 1;
var maxReachedStep = 1;
var currentService = null;
var currentBookingType = null;
var selectedSlot = null;
var lookupState = null;

function escapeHtml(value){
  return $('<div>').text(value == null ? '' : String(value)).html();
}

function peso(value){
  return '₱' + Number(value || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

function scrollToWizard(){
  var el = document.getElementById('walkin-wizard-progress');
  if(!el) return;
  var y = el.getBoundingClientRect().top + window.pageYOffset - 76;
  window.scrollTo({top: Math.max(0, y), behavior:'smooth'});
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
    }else if(step <= maxReachedStep){
      $number.addClass('bg-emerald-100 text-emerald-700');
      $label.addClass('text-emerald-700');
    }else{
      $(this).addClass('opacity-45 cursor-default');
      $number.addClass('bg-gray-100 text-gray-400');
      $label.addClass('text-gray-500');
    }
  });

  $('#wizard-progress-bar').css('width', (currentWizardStep * 25) + '%');
}

function showWizardStep(step, shouldScroll){
  currentWizardStep = Math.max(1, Math.min(4, parseInt(step, 10) || 1));
  $('.wizard-step').addClass('hidden');
  $('.wizard-step[data-step="' + currentWizardStep + '"]').removeClass('hidden');

  if(currentWizardStep === 4){
    updateReview();
  }

  updateWizardProgress();
  if(shouldScroll !== false) scrollToWizard();
}

function goToCompletedStep(step){
  step = parseInt(step, 10) || 1;
  if(step <= maxReachedStep) showWizardStep(step);
}

function previousStep(){
  if(currentWizardStep > 1) showWizardStep(currentWizardStep - 1);
}

function validateFields($scope){
  var valid = true;
  var firstInvalid = null;

  $scope.find('[required]').each(function(){
    var empty = String($(this).val() == null ? '' : $(this).val()).trim() === '';
    var browserInvalid = this.validity ? !this.validity.valid : false;

    $(this).removeClass('border-red-400 ring-2 ring-red-100');
    if(empty || browserInvalid){
      valid = false;
      $(this).addClass('border-red-400 ring-2 ring-red-100');
      if(!firstInvalid) firstInvalid = this;
    }
  });

  if(!valid && firstInvalid){
    firstInvalid.focus();
  }

  return valid;
}

function nextStep(){
  if(currentWizardStep === 1){
    if(!validateFields($('.wizard-step[data-step="1"]'))){
      Swal.fire({
        icon:'warning',
        title:'Complete parishioner information',
        text:'Full name and a valid email address are required.',
        confirmButtonColor:'#235a38'
      });
      return;
    }

    lookupParishioner(true);
    return;
  }

  if(currentWizardStep === 2){
    if(!currentService){
      Swal.fire({icon:'warning', title:'Choose a parish service', text:'Select the service being requested.', confirmButtonColor:'#235a38'});
      return;
    }
    if(!selectedSlot){
      Swal.fire({icon:'warning', title:'Choose an available schedule', text:'Select an actual available date and time before continuing.', confirmButtonColor:'#235a38'});
      return;
    }
  }

  if(currentWizardStep === 3){
    if(!validateFields($('.wizard-step[data-step="3"]'))){
      Swal.fire({
        icon:'warning',
        title:'Complete the required service details',
        text:'Please fill in the highlighted fields before continuing.',
        confirmButtonColor:'#235a38'
      });
      return;
    }
  }

  if(currentWizardStep < 4){
    maxReachedStep = Math.max(maxReachedStep, currentWizardStep + 1);
    showWizardStep(currentWizardStep + 1);
  }
}

function lookupParishioner(continueAfter){
  var email = String($('#walkin-email').val() || '').trim();
  if(!email || !document.getElementById('walkin-email').checkValidity()){
    $('#walkin-email').addClass('border-red-400 ring-2 ring-red-100').focus();
    return;
  }

  var $box = $('#account-lookup-result');
  $box.removeClass('hidden bg-emerald-50 border-emerald-100 text-emerald-800 bg-blue-50 border-blue-100 text-blue-800 bg-red-50 border-red-100 text-red-700')
      .addClass('border bg-gray-50 border-gray-100 text-gray-500')
      .html('<i class="ph ph-spinner-gap animate-spin mr-1"></i> Checking parishioner account…');

  $.post('<?= site_url('staff/walkin/lookup') ?>', {email: email})
    .done(function(res){
      lookupState = res;

      if(res.success && res.exists){
        var d = res.data || {};
        $('#walkin-full-name').val(d.full_name || $('#walkin-full-name').val());
        if(d.mobile_number) $('#walkin-mobile').val(d.mobile_number);

        $box.removeClass('bg-gray-50 border-gray-100 text-gray-500')
            .addClass('bg-emerald-50 border-emerald-100 text-emerald-800')
            .html('<div class="flex gap-3"><i class="ph ph-check-circle text-xl"></i><div><div class="font-semibold text-sm">Existing Parishioner account found</div><div class="text-xs mt-1">' + escapeHtml(res.message) + '</div></div></div>');
      }else if(res.success){
        $box.removeClass('bg-gray-50 border-gray-100 text-gray-500')
            .addClass('bg-blue-50 border-blue-100 text-blue-800')
            .html('<div class="flex gap-3"><i class="ph ph-user-plus text-xl"></i><div><div class="font-semibold text-sm">New Parishioner profile</div><div class="text-xs mt-1">' + escapeHtml(res.message) + '</div></div></div>');
      }else{
        $box.removeClass('bg-gray-50 border-gray-100 text-gray-500')
            .addClass('bg-red-50 border-red-100 text-red-700')
            .html('<div class="flex gap-3"><i class="ph ph-warning-circle text-xl"></i><div><div class="font-semibold text-sm">Cannot use this account</div><div class="text-xs mt-1">' + escapeHtml(res.message || 'Please review the email address.') + '</div></div></div>');
        return;
      }

      if(continueAfter){
        maxReachedStep = Math.max(maxReachedStep, 2);
        showWizardStep(2);
      }
    })
    .fail(function(){
      $box.removeClass('bg-gray-50 border-gray-100 text-gray-500')
          .addClass('bg-red-50 border-red-100 text-red-700')
          .text('Could not check the account. Please try again.');
    });
}

function getService(id){
  return serviceCatalog[String(id)] || serviceCatalog[parseInt(id,10)] || null;
}

function weeksLabel(value){
  var labels = {1:'1st',2:'2nd',3:'3rd',4:'4th',5:'5th'};
  return String(value || '').split(',').map(function(v){ return labels[parseInt(v,10)] || ''; }).filter(Boolean).join(' & ');
}

function dayName(day){
  return ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][parseInt(day,10)] || '';
}

function timeLabel(value){
  if(!value) return 'time pending';
  var p = String(value).split(':');
  var h = parseInt(p[0],10), m = p[1] || '00';
  var suffix = h >= 12 ? 'PM' : 'AM';
  var hour = h % 12 || 12;
  return hour + ':' + m + ' ' + suffix;
}

function setCurrentService(){
  currentService = getService($('#service-type').val());
  currentBookingType = null;
  clearSlotSelection(true);
  $('#booking-type-area, #service-overview').addClass('hidden');
  $('#schedule-workspace').removeClass('hidden');
  $('#service-details-fields').empty();
  $('#requirements-list').empty();

  if(!currentService) return;

  var regular = Array.isArray(currentService.schedule_rules) ? currentService.schedule_rules : [];
  var requirements = Array.isArray(currentService.requirements) ? currentService.requirements : [];
  currentService.has_regular = regular.length > 0;

  var overview = '<div class="text-[10px] uppercase tracking-wider text-parish-600 font-bold">' + escapeHtml(currentService.category || 'service') + '</div>' +
    '<div class="font-semibold text-parish-900 mt-1">' + escapeHtml(currentService.name) + '</div>' +
    '<p class="text-xs text-gray-500 mt-2 leading-relaxed">' + escapeHtml(currentService.description || 'Parish service') + '</p>' +
    '<div class="flex flex-wrap gap-2 mt-3">' +
      (currentService.has_regular ? '<span class="px-2.5 py-1 rounded-full bg-white border border-parish-100 text-[10px] font-semibold text-parish-700">Regular schedule</span>' : '') +
      (parseInt(currentService.allow_special_booking || 0,10) === 1 ? '<span class="px-2.5 py-1 rounded-full bg-white border border-gold-100 text-[10px] font-semibold text-gold-700">Special ' + peso(currentService.special_fee) + '</span>' : '') +
      '<span class="px-2.5 py-1 rounded-full bg-white border border-gray-100 text-[10px] font-semibold text-gray-500">' + requirements.length + ' requirement' + (requirements.length === 1 ? '' : 's') + '</span>' +
    '</div>';

  $('#service-overview').html(overview).removeClass('hidden');
  $('#schedule-workspace').addClass('hidden');
  $('#booking-type-area').removeClass('hidden');

  renderBookingTypeCards();
  renderPublishedRules();
  renderDetailsFields();
  renderRequirements();

  if(currentService.has_regular){
    setBookingType('regular');
  }else if(parseInt(currentService.allow_special_booking || 0,10) === 1){
    setBookingType('special');
  }else{
    $('#booking-type-cards').html('<div class="md:col-span-2 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800"><i class="ph ph-warning-circle mr-1"></i>No bookable schedule is configured for this service yet.</div>');
  }
}

function renderBookingTypeCards(){
  var html = '';

  if(currentService.has_regular){
    html += '<button type="button" id="type-regular" onclick="setBookingType(\'regular\')" class="booking-type-card text-left rounded-2xl border-2 border-gray-100 p-5 hover:border-parish-200 transition">' +
      '<div class="flex items-start gap-4">' +
        '<div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl flex-shrink-0"><i class="ph ph-calendar-heart"></i></div>' +
        '<div class="flex-1"><div class="font-semibold text-gray-900">Parish Regular Schedule</div><p class="text-xs text-gray-500 mt-2">Choose one of the parish’s published recurring schedules. The configured regular fee, including FREE schedules, applies.</p></div>' +
      '</div></button>';
  }

  if(parseInt(currentService.allow_special_booking || 0,10) === 1){
    html += '<button type="button" id="type-special" onclick="setBookingType(\'special\')" class="booking-type-card text-left rounded-2xl border-2 border-gray-100 p-5 hover:border-gold-200 transition">' +
      '<div class="flex items-start gap-4">' +
        '<div class="w-11 h-11 rounded-xl bg-gold-50 text-gold-700 flex items-center justify-center text-xl flex-shrink-0"><i class="ph ph-sparkle"></i></div>' +
        '<div class="flex-1"><div class="flex items-center justify-between gap-2"><div class="font-semibold text-gray-900">Other Available Date & Time</div><span class="px-2 py-1 rounded-full bg-gold-100 text-gold-700 text-[10px] font-bold">' + peso(currentService.special_fee) + '</span></div><p class="text-xs text-gray-500 mt-2">Choose another open date and time using the same special-booking availability rules as the online portal.</p></div>' +
      '</div></button>';
  }

  $('#booking-type-cards').html(html);
}

function renderPublishedRules(){
  var rules = Array.isArray(currentService.schedule_rules) ? currentService.schedule_rules : [];
  var $box = $('#published-rules');

  if(!rules.length){
    $box.addClass('hidden').empty();
    return;
  }

  var html = '<div class="text-[11px] uppercase tracking-wider font-semibold text-parish-700 mb-2">Published Regular Schedule</div><div class="flex flex-wrap gap-2">';
  rules.forEach(function(rule){
    var fee = Number(rule.fee_amount || 0);
    html += '<span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-parish-100 text-xs text-gray-600">' +
      '<i class="ph ph-calendar-dots text-parish-700"></i>' +
      escapeHtml(weeksLabel(rule.week_numbers)) + ' ' + escapeHtml(dayName(rule.day_of_week)) + ' · ' + escapeHtml(timeLabel(rule.start_time)) + ' · ' +
      '<strong class="' + (fee === 0 ? 'text-emerald-700' : 'text-gold-700') + '">' + (fee === 0 ? 'FREE' : peso(fee)) + '</strong>' +
      (parseInt(rule.capacity || 1,10) > 1 ? ' · <span class="font-semibold text-parish-700">' + escapeHtml(rule.capacity) + ' / session</span>' : '') +
    '</span>';
  });
  html += '</div>';

  $box.html(html).removeClass('hidden');
}

function setBookingType(type){
  if(!currentService) return;

  currentBookingType = type;
  clearSlotSelection(true);
  $('#booking-type').val(type);

  $('.booking-type-card').removeClass('border-parish-500 bg-parish-50 border-gold-400 bg-gold-50');
  $('#regular-panel, #special-panel').addClass('hidden');

  if(type === 'regular'){
    $('#type-regular').addClass('border-parish-500 bg-parish-50');
    $('#regular-panel').removeClass('hidden');
    loadRegularSlots();
  }else{
    $('#type-special').addClass('border-gold-400 bg-gold-50');
    $('#special-panel').removeClass('hidden');
    loadSpecialSlots();
  }
}

function renderSlotButton(slot, type){
  var fee = Number(slot.fee || 0);
  var remaining = parseInt(slot.remaining || 1,10);
  var capacity = parseInt(slot.capacity || 1,10);
  var nearby = Array.isArray(slot.nearby_bookings) ? slot.nearby_bookings : [];

  var meta = '';
  if(slot.reserved_from && slot.reserved_until){
    meta += '<div class="text-[10px] text-gray-500 mt-2 flex items-center gap-1"><i class="ph ph-shield-check text-parish-600"></i>Protected ' + escapeHtml(slot.reserved_from) + '–' + escapeHtml(slot.reserved_until) + '</div>';
  }
  if(parseInt(slot.priest_total || 0,10) > 0){
    meta += '<div class="text-[10px] text-gray-500 mt-1 flex items-center gap-1"><i class="ph ph-users text-parish-600"></i>Priest checked · ' + escapeHtml(slot.priests_available_for_slot || 0) + ' of ' + escapeHtml(slot.priest_total || 0) + ' available</div>';
  }
  if(capacity > 1){
    meta += '<div class="text-[10px] text-gray-500 mt-1 flex items-center gap-1"><i class="ph ph-users-three text-parish-600"></i>' + remaining + ' place' + (remaining === 1 ? '' : 's') + ' left</div>';
  }
  if(nearby.length){
    meta += '<div class="mt-2 rounded-lg bg-amber-50 border border-amber-100 px-2.5 py-2 text-[10px] font-medium text-amber-800"><i class="ph ph-info mr-1"></i>Another church service is scheduled nearby</div>';
  }

  return '<button type="button" class="available-slot text-left rounded-2xl border border-gray-200 bg-white p-4 hover:border-parish-300 hover:shadow-sm transition" ' +
    'data-type="' + escapeHtml(type) + '" ' +
    'data-datetime="' + escapeHtml(slot.datetime) + '" ' +
    'data-rule="' + escapeHtml(slot.schedule_rule_id || '') + '" ' +
    'data-fee="' + escapeHtml(fee) + '" ' +
    'data-date-label="' + escapeHtml(slot.date_label || '') + '" ' +
    'data-time="' + escapeHtml(slot.time || '') + '" ' +
    'data-reserved-from="' + escapeHtml(slot.reserved_from || '') + '" ' +
    'data-reserved-until="' + escapeHtml(slot.reserved_until || '') + '" ' +
    'data-nearby="' + escapeHtml(encodeURIComponent(JSON.stringify(nearby))) + '">' +
      '<div class="flex items-start justify-between gap-2">' +
        '<div><div class="text-sm font-semibold text-gray-800">' + escapeHtml(slot.date_label || '') + '</div><div class="text-xl font-bold text-parish-800 mt-1">' + escapeHtml(slot.time || '') + '</div></div>' +
        '<span class="text-[10px] font-bold px-2 py-1 rounded-full ' + (fee === 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gold-100 text-gold-700') + '">' + (fee === 0 ? 'FREE' : peso(fee)) + '</span>' +
      '</div>' +
      '<div class="text-[11px] text-gray-400 mt-2">' + escapeHtml(slot.rule_name || (type === 'regular' ? 'Regular Schedule' : 'Special Booking')) + '</div>' +
      meta +
    '</button>';
}

function bindSlotButtons(){
  $('.available-slot').off('click').on('click', function(){
    var $button = $(this);
    var nearby = [];
    try{
      nearby = JSON.parse(decodeURIComponent(String($button.attr('data-nearby') || '%5B%5D')));
    }catch(e){
      nearby = [];
    }

    selectedSlot = {
      booking_type: String($button.data('type') || ''),
      datetime: String($button.data('datetime') || ''),
      schedule_rule_id: $button.data('rule') || '',
      fee: Number($button.data('fee') || 0),
      date_label: String($button.data('date-label') || ''),
      time: String($button.data('time') || ''),
      reserved_from: String($button.data('reserved-from') || ''),
      reserved_until: String($button.data('reserved-until') || ''),
      nearby_bookings: nearby
    };

    $('.available-slot').removeClass('border-parish-600 ring-2 ring-parish-100 bg-parish-50');
    $button.addClass('border-parish-600 ring-2 ring-parish-100 bg-parish-50');

    $('#booking-type').val(selectedSlot.booking_type);
    $('#schedule-start').val(selectedSlot.datetime);
    $('#schedule-rule-id').val(selectedSlot.schedule_rule_id);

    var typeLabel = selectedSlot.booking_type === 'regular' ? 'Regular / Parish Schedule' : 'Special Booking';
    var protectedLabel = selectedSlot.reserved_from && selectedSlot.reserved_until
      ? ' · Protected ' + selectedSlot.reserved_from + '–' + selectedSlot.reserved_until
      : '';

    $('#selected-schedule-label').text(selectedSlot.date_label + ' · ' + selectedSlot.time);
    $('#selected-schedule-meta').text(typeLabel + ' · ' + (selectedSlot.fee === 0 ? 'FREE' : peso(selectedSlot.fee)) + protectedLabel);
    $('#schedule-summary').removeClass('hidden');
    $('#step2-next').prop('disabled', false);
    $('#submit-walkin').prop('disabled', false);
    $('#submit-help').text('The selected slot will be checked once more when you submit.');

    renderScheduleAdvisory(selectedSlot);
    updateReview();
  });
}

function renderScheduleAdvisory(slot){
  var nearby = Array.isArray(slot.nearby_bookings) ? slot.nearby_bookings : [];
  var $box = $('#schedule-advisory').empty();

  if(!nearby.length){
    $box.addClass('hidden');
    return;
  }

  var html = '<div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:p-5">' +
    '<div class="flex gap-3"><i class="ph ph-info text-amber-700 text-xl mt-0.5"></i><div>' +
    '<div class="font-semibold text-amber-900 text-sm">Another church service is scheduled nearby</div>' +
    '<p class="text-xs text-amber-800/80 mt-1">The chosen time is still valid. This notice is for arrival, preparation, pictorial and departure planning.</p>' +
    '<div class="mt-3 space-y-2">';

  nearby.forEach(function(item){
    html += '<div class="rounded-xl bg-white/75 border border-amber-100 px-3 py-2.5 text-xs text-gray-700"><strong>' +
      escapeHtml(item.service_name || 'Church service') + '</strong> · ' + escapeHtml(item.ceremony_time || '') + '</div>';
  });

  html += '</div></div></div></div>';
  $box.html(html).removeClass('hidden');
}

function clearSlotSelection(keepStep){
  selectedSlot = null;
  $('#schedule-start, #schedule-rule-id').val('');
  $('.available-slot').removeClass('border-parish-600 ring-2 ring-parish-100 bg-parish-50');
  $('#schedule-summary, #schedule-advisory').addClass('hidden');
  $('#schedule-advisory').empty();
  $('#step2-next').prop('disabled', true);
  $('#submit-walkin').prop('disabled', true);
  $('#submit-help').text('Complete the earlier wizard steps first.');

  if(!keepStep && currentWizardStep > 2){
    maxReachedStep = Math.min(maxReachedStep, 2);
    showWizardStep(2, false);
  }

  updateReview();
}

function loadRegularSlots(){
  if(!currentService) return;

  clearSlotSelection(true);
  $('#booking-type').val('regular');
  $('#regular-slots').html('<div class="sm:col-span-2 xl:col-span-3 py-8 text-center text-sm text-gray-400"><i class="ph ph-spinner-gap animate-spin mr-1"></i>Checking parish availability…</div>');
  $('#regular-message').addClass('hidden');

  $.post('<?= site_url('staff/walkin/availability') ?>', {
    service_type_id: currentService.id,
    booking_type: 'regular'
  }).done(function(res){
    $('#regular-slots').empty();

    if(res.success && Array.isArray(res.slots) && res.slots.length){
      res.slots.forEach(function(slot){
        $('#regular-slots').append(renderSlotButton(slot, 'regular'));
      });
      bindSlotButtons();
    }else{
      $('#regular-message').removeClass('hidden').text(res.message || 'No regular slots are currently available.');
    }
  }).fail(function(){
    $('#regular-slots').empty();
    $('#regular-message').removeClass('hidden').text('Unable to check availability. Please try again.');
  });
}

function loadSpecialSlots(){
  if(!currentService) return;

  clearSlotSelection(true);
  $('#booking-type').val('special');
  $('#special-slots').html('<div class="sm:col-span-2 xl:col-span-3 py-8 text-center text-sm text-gray-400"><i class="ph ph-spinner-gap animate-spin mr-1"></i>Finding available date-and-time schedules…</div>');
  $('#special-message').addClass('hidden');

  $.post('<?= site_url('staff/walkin/availability') ?>', {
    service_type_id: currentService.id,
    booking_type: 'special'
  }).done(function(res){
    $('#special-slots').empty();

    if(res.success && Array.isArray(res.slots) && res.slots.length){
      res.slots.forEach(function(slot){
        $('#special-slots').append(renderSlotButton(slot, 'special'));
      });
      bindSlotButtons();
    }else{
      $('#special-message').removeClass('hidden').text(res.message || 'No special-booking slots are currently available.');
    }
  }).fail(function(){
    $('#special-slots').empty();
    $('#special-message').removeClass('hidden').text('Unable to check availability. Please try again.');
  });
}

function renderDetailsFields(){
  if(!currentService){
    $('#service-details-fields').empty();
    return;
  }

  var key = String(currentService.service_key || '');
  var html = '';

  if(key === 'baptism'){
    html =
      '<div><h3 class="text-sm font-semibold text-gray-700 mb-3">Child Information</h3><div class="grid sm:grid-cols-2 gap-4">' +
        '<input required name="child_name" placeholder="Child\'s Full Name" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">' +
        '<input required type="date" name="child_birth_date" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">' +
      '</div></div>' +
      '<div><h3 class="text-sm font-semibold text-gray-700 mb-3">Parents\' Information</h3><div class="grid sm:grid-cols-2 gap-4">' +
        '<input required name="father_name" placeholder="Father\'s Full Name" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">' +
        '<input required name="mother_name" placeholder="Mother\'s Full Name" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">' +
      '</div></div>' +
      '<div><h3 class="text-sm font-semibold text-gray-700 mb-3">Godparents / Sponsors</h3><textarea name="sponsors" rows="3" placeholder="List of godparents (one per line)" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea></div>';
  }else if(key === 'wedding'){
    html =
      '<div><h3 class="text-sm font-semibold text-gray-700 mb-3">Bride & Groom Information</h3><div class="grid sm:grid-cols-2 gap-4">' +
        '<input required name="bride_name" placeholder="Bride\'s Full Name" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">' +
        '<input required name="groom_name" placeholder="Groom\'s Full Name" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">' +
      '</div></div>' +
      '<div><h3 class="text-sm font-semibold text-gray-700 mb-3">Additional Details</h3><textarea name="sponsor_information" rows="3" placeholder="Principal sponsors, notes, or other information" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea></div>' +
      '<div class="bg-amber-50 border border-amber-100 text-amber-800 text-xs rounded-xl p-4 flex gap-2"><i class="ph ph-info text-lg flex-shrink-0"></i><div>Wedding bookings remain subject to documentary requirements, canonical interview, priest/parish review and payment verification when applicable.</div></div>';
  }else if(key === 'funeral'){
    html =
      '<div><h3 class="text-sm font-semibold text-gray-700 mb-3">Deceased Information</h3><div class="grid sm:grid-cols-2 gap-4">' +
        '<input required name="deceased_name" placeholder="Name of Deceased" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">' +
        '<input required type="date" name="date_of_death" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">' +
        '<input name="funeral_home" placeholder="Funeral Home" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">' +
        '<input name="cemetery" placeholder="Cemetery" class="px-4 py-3 rounded-xl border border-gray-200 text-sm">' +
      '</div></div>';
  }else if(key === 'confirmation'){
    html = '<div><label class="text-xs font-semibold text-gray-600">Confirmand</label><input required name="confirmand_name" placeholder="Confirmand\'s Full Name" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></div>';
  }else if(key === 'house_blessing' || key === 'vehicle_blessing'){
    html = '<div><label class="text-xs font-semibold text-gray-600">Blessing Details</label><textarea required name="blessing_details" rows="4" placeholder="Address, vehicle details, or other information needed by the parish" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea></div>';
  }else{
    html = '<div><label class="text-xs font-semibold text-gray-600">Service Notes</label><textarea name="notes" rows="4" placeholder="Information relevant to this parish service" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm"></textarea></div>';
  }

  $('#details-step-title').text(currentService.name + ' details');
  $('#service-details-fields').html(html);

  $('#service-details-fields [required]').on('input change', function(){
    $(this).removeClass('border-red-400 ring-2 ring-red-100');
  });
}

function renderRequirements(){
  var $list = $('#requirements-list').empty();

  if(!currentService){
    $list.html('<div class="rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-500">Select a service first.</div>');
    return;
  }

  var requirements = Array.isArray(currentService.requirements) ? currentService.requirements : [];
  if(!requirements.length){
    $list.html('<div class="rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-500">No document requirements are configured for this service.</div>');
    return;
  }

  var html = '<div class="space-y-3">';
  requirements.forEach(function(req){
    var required = parseInt(req.is_required || 0,10) === 1;
    var accepts = parseInt(req.accepts_file_upload || 0,10) === 1;

    html += '<div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 rounded-xl border border-gray-100 bg-gray-50/40">' +
      '<div class="w-9 h-9 rounded-lg bg-white border border-gray-100 text-gray-400 flex items-center justify-center flex-shrink-0"><i class="ph ph-paperclip"></i></div>' +
      '<div class="flex-1"><div class="text-sm text-gray-700">' + escapeHtml(req.label) +
        (required ? ' <span class="text-red-500">*</span>' : ' <span class="text-gray-400 text-xs">(optional)</span>') +
      '</div>' +
      (!accepts ? '<div class="text-[10px] text-gray-400 mt-1">Physical / office verification — no file upload configured</div>' : '') +
      '</div>';

    if(accepts){
      html += '<input type="file" name="documents[]" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" class="text-xs max-w-full">' +
              '<input type="hidden" name="requirement_id[]" value="' + escapeHtml(req.id) + '">';
    }

    html += '</div>';
  });
  html += '</div><p class="text-xs text-gray-400 mt-3">Accepted uploads: JPG, PNG or PDF up to 5 MB each. A required document may still be marked missing later if the physical/original copy has not been verified.</p>';

  $list.html(html);
}

function updateReview(){
  var name = String($('#walkin-full-name').val() || '').trim();
  var email = String($('#walkin-email').val() || '').trim();
  var mobile = String($('#walkin-mobile').val() || '').trim();

  $('#review-parishioner-name').text(name || '—');
  $('#review-parishioner-contact').text([email, mobile].filter(Boolean).join(' · ') || '—');
  $('#review-service').text(currentService ? currentService.name : '—');

  if(selectedSlot){
    var typeLabel = selectedSlot.booking_type === 'regular' ? 'Regular / Parish Schedule' : 'Special Booking';
    var protectedLabel = selectedSlot.reserved_from && selectedSlot.reserved_until
      ? ' · Protected ' + selectedSlot.reserved_from + '–' + selectedSlot.reserved_until
      : '';

    $('#review-schedule').text(selectedSlot.date_label + ' · ' + selectedSlot.time + protectedLabel);
    $('#review-type').text(typeLabel);
    $('#review-fee').text(selectedSlot.fee === 0 ? 'FREE' : peso(selectedSlot.fee));
  }else{
    $('#review-schedule').text('Schedule not selected');
    $('#review-type, #review-fee').text('—');
  }
}

$('#service-type').on('change', function(){
  setCurrentService();

  if(currentWizardStep > 2){
    maxReachedStep = 2;
    showWizardStep(2, false);
  }
});

$('#walkin-full-name, #walkin-email, #walkin-mobile').on('input change', function(){
  $(this).removeClass('border-red-400 ring-2 ring-red-100');
  updateReview();

  if(this.id === 'walkin-email'){
    lookupState = null;
    $('#account-lookup-result').addClass('hidden').empty();
  }
});

$('#walkin-form').on('submit', function(e){
  e.preventDefault();

  if(currentWizardStep !== 4 || !currentService || !selectedSlot){
    return;
  }

  var scheduleText = selectedSlot.date_label + ' at ' + selectedSlot.time;
  var feeText = selectedSlot.fee === 0 ? 'FREE' : peso(selectedSlot.fee);
  var accountText = lookupState && lookupState.exists
    ? 'Existing Parishioner account will be linked.'
    : 'A Parishioner profile will be created if the email is new.';

  Swal.fire({
    icon:'question',
    title:'Record this walk-in booking?',
    html:'<div class="text-sm text-gray-600 text-left space-y-2">' +
      '<div><strong>Parishioner:</strong> ' + escapeHtml($('#walkin-full-name').val()) + '</div>' +
      '<div><strong>Service:</strong> ' + escapeHtml(currentService.name) + '</div>' +
      '<div><strong>Schedule:</strong> ' + escapeHtml(scheduleText) + '</div>' +
      '<div><strong>Fee:</strong> ' + escapeHtml(feeText) + '</div>' +
      '<div class="mt-3 rounded-lg bg-parish-50 border border-parish-100 p-3 text-xs text-parish-800">' + escapeHtml(accountText) + '</div>' +
    '</div>',
    showCancelButton:true,
    confirmButtonText:'Record Booking',
    cancelButtonText:'Review',
    confirmButtonColor:'#235a38'
  }).then(function(result){
    if(!result.isConfirmed) return;

    var formData = new FormData(document.getElementById('walkin-form'));
    var $button = $('#submit-walkin');
    var original = $button.html();

    $button.prop('disabled', true).addClass('opacity-60').html('<i class="ph ph-spinner-gap animate-spin mr-1"></i> Recording…');

    $.ajax({
      url:'<?= site_url('staff/walkin/store') ?>',
      type:'POST',
      data:formData,
      processData:false,
      contentType:false
    }).done(function(res){
      if(res.success){
        var text = res.message || 'Walk-in booking recorded.';
        if(Array.isArray(res.upload_warnings) && res.upload_warnings.length){
          text += '\n\nDocument note: ' + res.upload_warnings.join(' ');
        }

        Swal.fire({
          icon:'success',
          title:'Walk-in Booking Recorded',
          text:text,
          confirmButtonColor:'#235a38'
        }).then(function(){
          window.location.href = res.redirect;
        });
      }else{
        $button.prop('disabled', false).removeClass('opacity-60').html(original);

        Swal.fire({
          icon:'error',
          title:'Could not record booking',
          text:res.message || 'Please review the booking information.',
          confirmButtonColor:'#235a38'
        });

        var lower = String(res.message || '').toLowerCase();
        if(lower.indexOf('schedule') !== -1 || lower.indexOf('slot') !== -1 || lower.indexOf('available') !== -1){
          clearSlotSelection(true);
          maxReachedStep = Math.min(maxReachedStep, 2);
          showWizardStep(2, false);
          if(currentBookingType === 'regular') loadRegularSlots();
          if(currentBookingType === 'special') loadSpecialSlots();
        }
      }
    }).fail(function(xhr){
      $button.prop('disabled', false).removeClass('opacity-60').html(original);
      Swal.fire({
        icon:'error',
        title:'Unable to record booking',
        text:'The server did not accept the walk-in booking. Please try again.',
        confirmButtonColor:'#235a38'
      });
      console.error('Walk-in booking error:', xhr.status, xhr.responseText);
    });
  });
});

$(function(){
  showWizardStep(1, false);

  $('.wizard-step[data-step="1"] [required]').on('input change', function(){
    $(this).removeClass('border-red-400 ring-2 ring-red-100');
  });
});
</script>
