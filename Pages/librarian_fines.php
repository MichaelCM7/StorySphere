<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/LibraryComponents.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Fines');
$template->hero('Fines');

$section = new FineManagementSection();
echo '<div class="card card-modern"><div class="card-body">';
echo $section->renderContent();
echo '</div></div>';

$template->footer($config);
?>
<!-- DataTables assets (local) -->
<link rel="stylesheet" href="../Datatables/3.1.1.css">
<!-- jQuery (required) -->
<script src="../Datatables/3.7.1.js"></script>
<!-- DataTables core -->
<script src="../Datatables/2.1.4.js"></script>
<!-- Optional extensions (JSZip/pdfmake) - used for export buttons if needed -->
<script src="../Datatables/dependancy1.js"></script>
<script src="../Datatables/dependancy2.js"></script>
<script>
// Robust DataTables initializer: waits for jQuery + DataTable, then initializes any table inside .card
(function(){
	function init() {
		// debug: confirm jQuery and DataTable availability
		if (window.jQuery) console.log('jQuery version', window.jQuery.fn && window.jQuery.fn.jquery);
		if (window.jQuery) console.log('DataTable plugin present:', !!(window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable));
		if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
			window.jQuery('.card table').each(function(i, el){
				if (!el.id) el.id = 'datatable-' + i;
				if (!window.jQuery.fn.DataTable.isDataTable('#' + el.id)) {
					window.jQuery('#' + el.id).DataTable({ pageLength: 25, order: [[4, 'desc']] });
				}
			});
			return true;
		}
		return false;
	}
	function waitForInit() {
		if (!init()) setTimeout(waitForInit, 200);
	}
	document.addEventListener('DOMContentLoaded', waitForInit);
})();
</script>
