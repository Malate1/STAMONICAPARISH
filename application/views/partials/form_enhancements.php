<?php
/**
 * Project-wide enhanced form controls.
 *
 * - All normal <select> controls use Select2 with search.
 * - All input[type="date"] controls use jQuery UI Datepicker.
 * - Dynamically inserted modal/AJAX controls are initialized automatically.
 *
 * Escape hatches for exceptional controls:
 *   class="no-select2" / data-native-select
 *   class="no-datepicker" / data-native-date
 */
?>
<style>
  /* ---------------- Select2: Parish Connect theme ---------------- */
  .select2-container{max-width:100%;font-family:inherit}
  .select2-container .select2-selection--single{
    height:46px !important;
    min-height:46px;
    border:1px solid #e5e7eb !important;
    border-radius:.75rem !important;
    background:#fff !important;
    display:flex !important;
    align-items:center;
    transition:border-color .15s ease,box-shadow .15s ease;
  }
  .select2-container .select2-selection--single .select2-selection__rendered{
    line-height:44px !important;
    padding-left:1rem !important;
    padding-right:2.5rem !important;
    color:#374151 !important;
    font-size:.875rem;
    width:100%;
  }
  .select2-container .select2-selection--single .select2-selection__placeholder{color:#9ca3af !important}
  .select2-container .select2-selection--single .select2-selection__arrow{
    height:44px !important;
    right:.65rem !important;
  }
  .select2-container--default.select2-container--focus .select2-selection--single,
  .select2-container--default.select2-container--open .select2-selection--single{
    border-color:#8bc99f !important;
    box-shadow:0 0 0 3px rgba(35,90,56,.10) !important;
  }
  .select2-container--default .select2-selection--multiple{
    min-height:46px !important;
    border:1px solid #e5e7eb !important;
    border-radius:.75rem !important;
    padding:.25rem .5rem !important;
    background:#fff !important;
  }
  .select2-container--default.select2-container--focus .select2-selection--multiple{
    border-color:#8bc99f !important;
    box-shadow:0 0 0 3px rgba(35,90,56,.10) !important;
  }
  .select2-dropdown{
    border:1px solid #e5e7eb !important;
    border-radius:.75rem !important;
    overflow:hidden;
    box-shadow:0 16px 40px rgba(17,24,39,.12);
    z-index:10050 !important;
  }
  .select2-search--dropdown{padding:.6rem !important;background:#fff}
  .select2-search--dropdown .select2-search__field{
    border:1px solid #d1d5db !important;
    border-radius:.6rem !important;
    padding:.55rem .7rem !important;
    outline:none !important;
    font-size:.875rem;
  }
  .select2-search--dropdown .select2-search__field:focus{
    border-color:#8bc99f !important;
    box-shadow:0 0 0 3px rgba(35,90,56,.08);
  }
  .select2-results__option{
    padding:.65rem .85rem !important;
    font-size:.875rem;
    color:#374151;
  }
  .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{
    background:#235a38 !important;
    color:#fff !important;
  }
  .select2-container--default .select2-results__option--selected{
    background:#f1f8f3 !important;
    color:#1e482f !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__clear{
    margin-right:1.6rem !important;
    color:#9ca3af;
  }
  select.select2-hidden-accessible + .select2-container{vertical-align:middle}

  /* Compact selects used as filters keep the same visual rhythm. */
  .form-select-compact + .select2-container .select2-selection--single,
  select.px-3.py-2 + .select2-container .select2-selection--single{
    height:40px !important;
    min-height:40px;
  }
  .form-select-compact + .select2-container .select2-selection--single .select2-selection__rendered,
  select.px-3.py-2 + .select2-container .select2-selection--single .select2-selection__rendered{
    line-height:38px !important;
  }
  .form-select-compact + .select2-container .select2-selection--single .select2-selection__arrow,
  select.px-3.py-2 + .select2-container .select2-selection--single .select2-selection__arrow{
    height:38px !important;
  }

  /* ---------------- jQuery UI Datepicker: Parish Connect theme ---------------- */
  input.parish-datepicker{
    background-color:#fff;
    cursor:pointer;
    padding-right:2.65rem !important;
    background-image:
      linear-gradient(transparent,transparent),
      url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 256 256'%3E%3Crect x='40' y='40' width='176' height='176' rx='16' fill='none' stroke='%23235a38' stroke-width='16'/%3E%3Cline x1='80' y1='24' x2='80' y2='56' stroke='%23235a38' stroke-width='16' stroke-linecap='round'/%3E%3Cline x1='176' y1='24' x2='176' y2='56' stroke='%23235a38' stroke-width='16' stroke-linecap='round'/%3E%3Cline x1='40' y1='88' x2='216' y2='88' stroke='%23235a38' stroke-width='16'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right .8rem center;
    background-size:18px 18px;
  }
  input.parish-datepicker:focus{
    border-color:#8bc99f !important;
    box-shadow:0 0 0 3px rgba(35,90,56,.10) !important;
    outline:none !important;
  }
  .ui-datepicker{
    width:19rem !important;
    padding:.7rem !important;
    border:1px solid #e5e7eb !important;
    border-radius:1rem !important;
    box-shadow:0 18px 48px rgba(17,24,39,.16) !important;
    font-family:'Google Sans','Product Sans',ui-sans-serif,system-ui !important;
    z-index:10060 !important;
  }
  .ui-datepicker .ui-datepicker-header{
    background:#f1f8f3 !important;
    border:0 !important;
    border-radius:.75rem !important;
    padding:.45rem 0 !important;
    color:#1e482f !important;
  }
  .ui-datepicker .ui-datepicker-title{font-size:.875rem;font-weight:700}
  .ui-datepicker .ui-datepicker-prev,
  .ui-datepicker .ui-datepicker-next{
    top:.35rem !important;
    border:0 !important;
    border-radius:.55rem !important;
    cursor:pointer;
  }
  .ui-datepicker .ui-datepicker-prev-hover,
  .ui-datepicker .ui-datepicker-next-hover{
    background:#dcefe1 !important;
    border:0 !important;
  }
  .ui-datepicker th{
    color:#6b7280 !important;
    font-size:.68rem !important;
    font-weight:700 !important;
    padding:.55rem .2rem !important;
  }
  .ui-datepicker td{padding:2px !important}
  .ui-datepicker td span,
  .ui-datepicker td a{
    border:0 !important;
    border-radius:.55rem !important;
    background:transparent !important;
    text-align:center !important;
    padding:.48rem .25rem !important;
    font-size:.78rem !important;
    color:#374151 !important;
  }
  .ui-datepicker td a:hover{
    background:#f1f8f3 !important;
    color:#1e482f !important;
  }
  .ui-datepicker .ui-state-highlight{
    background:#fdf9ec !important;
    color:#875417 !important;
    font-weight:700 !important;
  }
  .ui-datepicker .ui-state-active{
    background:#235a38 !important;
    color:#fff !important;
    font-weight:700 !important;
  }
  .ui-datepicker .ui-state-disabled span{color:#d1d5db !important}
  .ui-datepicker .ui-datepicker-buttonpane{
    border-top:1px solid #f3f4f6 !important;
    margin:.6rem 0 0 !important;
    padding:.5rem 0 0 !important;
  }
  .ui-datepicker .ui-datepicker-buttonpane button{
    background:#f9fafb !important;
    border:1px solid #e5e7eb !important;
    border-radius:.6rem !important;
    padding:.4rem .7rem !important;
    color:#374151 !important;
    opacity:1 !important;
    font-size:.75rem !important;
  }

  @media (max-width:640px){
    .select2-dropdown{max-width:calc(100vw - 1.5rem)}
    .ui-datepicker{
      width:min(19rem,calc(100vw - 1.5rem)) !important;
      left:50% !important;
      transform:translateX(-50%);
    }
  }
</style>

<script>
(function($){
  'use strict';

  if(!$) return;

  var SELECT_SKIP = [
    '.no-select2',
    '[data-native-select]',
    '.dataTables_length select',
    '.swal2-container select',
    '.select2-container select'
  ].join(',');

  var DATE_SKIP = [
    '.no-datepicker',
    '[data-native-date]',
    '.hasDatepicker'
  ].join(',');

  function selectPlaceholder($select){
    var explicit = $select.attr('data-placeholder');
    if(explicit) return explicit;

    var $empty = $select.find('option[value=""]').first();
    if($empty.length){
      var text = $.trim($empty.text());
      if(text) return text.replace(/[.…]+$/,'');
    }

    return 'Search or select an option';
  }

  function dropdownParentFor($select){
    var $parent = $select.closest(
      '[role="dialog"], .fixed.inset-0, .modal, .modal-dialog, [x-data*="modal"], [x-show]'
    );

    return $parent.length ? $parent.first() : $(document.body);
  }

  window.initParishSelect2 = function(context){
    if(!$.fn.select2) return;

    var $context = context ? $(context) : $(document);
    var $selects = $context.is('select') ? $context : $context.find('select');

    $selects.each(function(){
      var $select = $(this);

      if($select.is(SELECT_SKIP) || $select.closest('.dataTables_length, .swal2-container').length) return;
      if($select.hasClass('select2-hidden-accessible')) return;

      var hasEmpty = $select.find('option[value=""]').length > 0;
      var placeholder = selectPlaceholder($select);

      $select.select2({
        width:$select.hasClass('w-full') ? '100%' : 'resolve',
        placeholder:placeholder,
        allowClear:hasEmpty && !$select.prop('required'),
        minimumResultsForSearch:0,
        dropdownAutoWidth:false,
        dropdownParent:dropdownParentFor($select)
      });
    });
  };

  function parseIsoDate(value){
    if(!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;

    var parts = value.split('-');
    return new Date(
      parseInt(parts[0],10),
      parseInt(parts[1],10)-1,
      parseInt(parts[2],10)
    );
  }

  window.initParishDatepickers = function(context){
    if(!$.fn.datepicker) return;

    var $context = context ? $(context) : $(document);
    var $inputs = $context.is('input') ? $context : $context.find('input');

    $inputs.filter('[type="date"], [data-parish-datepicker="1"]').each(function(){
      var $input = $(this);

      if($input.is(DATE_SKIP) || $input.hasClass('hasDatepicker')) return;

      var originalValue = $input.val();
      var minValue = $input.attr('min');
      var maxValue = $input.attr('max');

      // jQuery UI is used instead of the browser-native date picker so date
      // behavior and styling remain consistent across desktop and mobile.
      $input.attr('type','text')
            .attr('inputmode','none')
            .attr('autocomplete','off')
            .attr('data-parish-datepicker','1')
            .addClass('parish-datepicker');

      $input.datepicker({
        dateFormat:'yy-mm-dd',
        changeMonth:true,
        changeYear:true,
        yearRange:'1850:' + (new Date().getFullYear() + 50),
        showButtonPanel:true,
        showAnim:'fadeIn',
        minDate:parseIsoDate(minValue),
        maxDate:parseIsoDate(maxValue),
        beforeShow:function(input, inst){
          var $el = $(input);

          // Read min/max again because some forms update these attributes
          // dynamically after the control was first initialized.
          $el.datepicker('option','minDate',parseIsoDate($el.attr('min')));
          $el.datepicker('option','maxDate',parseIsoDate($el.attr('max')));

          setTimeout(function(){
            $('#ui-datepicker-div').css('z-index',10060);
          },0);
        }
      });

      if(originalValue){
        $input.val(originalValue);
      }
    });
  };

  function syncSelect2($select){
    if(!$select || !$select.length || !$select.hasClass('select2-hidden-accessible')) return;
    $select.trigger('change.select2');
  }

  function initEnhancements(context){
    window.initParishSelect2(context);
    window.initParishDatepickers(context);
  }

  $(function(){
    initEnhancements(document);

    // Keep Select2 in sync after normal form resets.
    $(document).on('reset', 'form', function(){
      var form = this;
      setTimeout(function(){
        $(form).find('select.select2-hidden-accessible').each(function(){
          syncSelect2($(this));
        });
        initEnhancements(form);
      },0);
    });

    // Existing screens populate edit modals after AJAX calls. Refresh the
    // Select2 display after those callbacks have assigned .val(...).
    $(document).ajaxComplete(function(){
      setTimeout(function(){
        $('select.select2-hidden-accessible').each(function(){
          syncSelect2($(this));
        });
        initEnhancements(document);
      },0);
    });

    // Handle controls inserted by dynamic modals, AJAX fragments and wizard
    // steps (including date inputs generated as HTML strings).
    var observer = new MutationObserver(function(mutations){
      var roots = [];

      mutations.forEach(function(mutation){
        if(mutation.type === 'childList'){
          mutation.addedNodes.forEach(function(node){
            if(node.nodeType === 1) roots.push(node);
          });

          if(mutation.target && mutation.target.nodeType === 1 && mutation.target.tagName === 'SELECT'){
            syncSelect2($(mutation.target));
          }
        }

        if(mutation.type === 'attributes' && mutation.target){
          if(mutation.target.tagName === 'SELECT'){
            syncSelect2($(mutation.target));
          }

          if(
            mutation.attributeName === 'class' &&
            $(mutation.target).is('[role="dialog"], .fixed.inset-0, .modal, .modal-dialog')
          ){
            $(mutation.target).find('select.select2-hidden-accessible').each(function(){
              syncSelect2($(this));
            });
          }
        }
      });

      if(!roots.length) return;

      window.requestAnimationFrame(function(){
        roots.forEach(function(root){
          // Never enhance Select2's own generated DOM or SweetAlert controls.
          if($(root).closest('.select2-container, .swal2-container').length) return;
          initEnhancements(root);
        });
      });
    });

    observer.observe(document.body,{
      childList:true,
      subtree:true,
      attributes:true,
      attributeFilter:['disabled','class']
    });

    // When a modal becomes visible, rebuild its dropdown parent so the menu
    // stays above the overlay and is not clipped.
    $(document).on('click','[onclick*="openModal"], [data-modal-target]',function(){
      setTimeout(function(){ initEnhancements(document); },30);
    });
  });
})(window.jQuery);
</script>
