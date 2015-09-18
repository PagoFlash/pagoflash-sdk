jQuery(document).ready(function () {
  jQuery(document.getElementById('callback-url-select-button')).on('click', function (p_event) {
    jQuery(document.getElementById('callback-url')).select();
  });
});