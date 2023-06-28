
// Add listeners.
$('HTML').on('change', '[data-xls-cache-uri-recalc]', getCacheUri);

/**
 * Calculates cache uri for given fileUri, sheetName and dataRef.
 *
 * Those three fields have to have attribute `data-xls-cache-uri-recalc` defined with id's of four fields:
 * 1. destination field
 * 2. uri field
 * 3. sheet field
 * 4. dataRef field
 *
 * @param {Event} event Changing fields 2, 3 or 4.
 */
function getCacheUri(event) {
  // Lvd.
  var thisObj      = $(this);
  var fields       = thisObj.attr('data-xls-cache-uri-recalc').split(',');
  var destField    = $('#' + fields[0]);
  var uriField     = $('#' + fields[1]);
  var sheetField   = $('#' + fields[2]);
  var dataRefField = $('#' + fields[3]);

  // Don't continue if all fields are not filled.
  if (!uriField.val() || !sheetField.val() || !dataRefField.val()) {
    return;
  }

  // Calc cache Uri.
  var uri       = uriField.val().trim();
  var extPos    = uri.lastIndexOf('.xls');
  var sheetName = sheetField.val();
  var dataRef   = dataRefField.val().replace(':', '_');

  // Calc rand number.
  var r    = 0;
  var rand = '';
  for (r = 0; r < 5; r++) {
    rand += Math.floor(Math.random() * 10).toString();
  }

  // Calc cache uri.
  var cacheUri = uri.substring(0, extPos) + ' [xls] [r.' + rand + '] [s.' + sheetName + '] [r.' + dataRef + '] [data].json';

  // Set it.
  destField.val(cacheUri);
}
