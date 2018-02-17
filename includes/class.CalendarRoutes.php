<?php
/**
 * Resolves URLs for symlinked calendars
 *
 * @author Tim Robert-Fitzgerald
 */
class CalendarRoutes {

  /**
   * @param $dir of current script, must be $_SERVER['SCRIPT_FILENAME'] and not __DIR__ or something so symlinks are resolved
   */
  public function __construct($script_filename) {
    $fn = basename($script_filename, '.php');
    if ($fn === 'add-event' || $fn === 'edit-event') {
      $fn = 'event-form';
    }
    $dir = dirname($script_filename);
    $dirs = explode('/', $dir);
    $website = $dirs[count($dirs)-2];
    $this->snippet_base = "{$dir}/includes/snippets/{$fn}/{$website}";
    $this->header_path = $this->snippet_base . '_top.php';
    $this->footer_path = $this->snippet_base . '_bottom.php';
    switch ($website) {
      case 'oberlin.org':
        $this->base_url = 'https://environmentaldashboard.org/symlinks/oberlin.org';
        $this->detail_page_sep = '?id='; // could make it a '/' but then appropriate rules needed in virtual host file
        break;
      default:
        $this->base_url = 'https://environmentaldashboard.org';
        $this->detail_page_sep = '/';
        break;
    }
  }

}
?>