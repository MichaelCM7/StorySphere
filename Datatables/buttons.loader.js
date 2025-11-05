// Lightweight local loader: waits for jQuery + DataTables, then injects Buttons and dependencies in correct order.
(function(){
  var CDN = {
    jszip: 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js',
    pdfmake: 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js',
    vfs_fonts: 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js',
    buttons: 'https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js',
    buttonsHtml5: 'https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js',
    buttonsPrint: 'https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js'
  };

  function loadScript(src, onload){
    var s = document.createElement('script');
    s.src = src;
    s.async = false;
    s.onload = function(){ console.debug('[buttons.loader] loaded', src); onload && onload(); };
    s.onerror = function(){ console.error('[buttons.loader] failed to load', src); onload && onload(new Error('failed to load '+src)); };
    document.head.appendChild(s);
  }

  function ensureWindow(conditionFn, cb){
    var tries = 0;
    (function poll(){
      try{
        if(conditionFn()) return cb();
      }catch(e){}
      tries++;
      if(tries>200){ console.error('[buttons.loader] timeout waiting for dependency'); return; }
      setTimeout(poll, 50);
    })();
  }

  // Wait for jQuery and DataTables core to be available
  ensureWindow(function(){
    return window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable;
  }, function(){
    console.debug('[buttons.loader] jQuery + DataTable detected; loading buttons dependencies');
    // Load in sequence: JSZip -> pdfmake+vfs -> dataTables.buttons -> buttons.html5 -> buttons.print
    loadScript(CDN.jszip, function(){
      loadScript(CDN.pdfmake, function(){
        loadScript(CDN.vfs_fonts, function(){
          loadScript(CDN.buttons, function(){
            loadScript(CDN.buttonsHtml5, function(){
              loadScript(CDN.buttonsPrint, function(){
                console.info('[buttons.loader] DataTables Buttons and export dependencies loaded');
                // signal that Buttons and dependencies are ready
                try{
                  window.__DataTablesButtonsReady = true;
                  window.dispatchEvent(new Event('datatables-buttons-ready'));
                }catch(e){
                  console.warn('[buttons.loader] could not dispatch datatables-buttons-ready event', e);
                }
              });
            });
          });
        });
      });
    });
  });
})();
