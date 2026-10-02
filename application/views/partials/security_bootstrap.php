<?php
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
?>
<meta name="csrf-name" content="<?= html_escape($csrf_name) ?>">
<meta name="csrf-token" content="<?= html_escape($csrf_hash) ?>">
<script>
(function(window, document){
  'use strict';

  window.StaMonicaSecurity = {
    csrfName: <?= json_encode($csrf_name) ?>,
    csrfHash: <?= json_encode($csrf_hash) ?>
  };

  function addTokenToForms(){
    var name = window.StaMonicaSecurity.csrfName;
    var hash = window.StaMonicaSecurity.csrfHash;

    document.querySelectorAll('form').forEach(function(form){
      var method = String(form.getAttribute('method') || 'GET').toUpperCase();
      if(method === 'GET') return;

      var existing = form.querySelector('input[name="' + name + '"]');
      if(existing){
        existing.value = hash;
        return;
      }

      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = hash;
      input.setAttribute('data-csrf-field', '1');
      form.appendChild(input);
    });
  }

  function installJqueryProtection(){
    if(!window.jQuery || window.StaMonicaSecurity.jqueryInstalled) return;

    var $ = window.jQuery;
    var name = window.StaMonicaSecurity.csrfName;
    var hash = window.StaMonicaSecurity.csrfHash;

    $.ajaxPrefilter(function(options, originalOptions){
      var method = String(options.type || options.method || 'GET').toUpperCase();
      if(method === 'GET' || method === 'HEAD' || method === 'OPTIONS') return;

      var originalData = originalOptions ? originalOptions.data : null;

      if(window.FormData && originalData instanceof FormData){
        if(!originalData.has(name)) originalData.append(name, hash);
        options.data = originalData;
        return;
      }

      if(options.data == null || options.data === ''){
        options.data = encodeURIComponent(name) + '=' + encodeURIComponent(hash);
        return;
      }

      if(typeof options.data === 'string'){
        var encodedName = encodeURIComponent(name) + '=';
        if(options.data.indexOf(encodedName) === -1){
          options.data += (options.data ? '&' : '') + encodedName + encodeURIComponent(hash);
        }
        return;
      }

      if(typeof options.data === 'object'){
        if(typeof options.data[name] === 'undefined'){
          options.data[name] = hash;
        }
      }
    });

    window.StaMonicaSecurity.jqueryInstalled = true;
  }

  function initialize(){
    addTokenToForms();
    installJqueryProtection();

    var observer = new MutationObserver(function(mutations){
      var hasNewForm = mutations.some(function(m){
        return Array.prototype.some.call(m.addedNodes || [], function(node){
          return node.nodeType === 1 && (node.matches && node.matches('form') || node.querySelector && node.querySelector('form'));
        });
      });
      if(hasNewForm) addTokenToForms();
    });

    if(document.body){
      observer.observe(document.body, {childList:true, subtree:true});
    }
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initialize);
  }else{
    initialize();
  }

  // Some layouts load jQuery after this partial; retry once the page has loaded.
  window.addEventListener('load', installJqueryProtection);
})(window, document);
</script>
