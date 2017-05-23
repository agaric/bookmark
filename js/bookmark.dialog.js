(function($) {
  $.fn.updateBookmarkLink = function(status, entity_id) {

    if (status === 'success' && entity_id > 0) {
      $link = $("a[data-bookmark-entity-id=" + entity_id + "]");
      $link.text("Saved!");
    }
  };
})(jQuery);
