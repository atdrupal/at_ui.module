<?php
namespace Drupal\at_ui\Controller\Reports;

class SourceCode {
  private $base_path = 'admin/reports/documentation/at_base/source';
  private $module;
  private $path;

  public function __construct() {
    if (!empty($_GET['module']) && module_exists($_GET['module'])) {
      $this->module = $_GET['module'];
    }

    if (!empty($_GET['path'])) {
      $this->path = $_GET['path'];
    }
  }

  public function render() {
    if (is_null($this->module)) {
      return $this->renderIndex();
    }

    $path = DRUPAL_ROOT . '/' . trim(drupal_get_path('module', $this->module) . '/' . $this->path, '/');
    if (is_dir($path)) {
      return $this->renderModuleDir($path);
    }

    return $this->renderModuleFile($path);
  }

  private function renderIndex() {
    foreach (system_list('module_enabled') as $module => $module_info) {
      $name = l($module, $this->base_path, array('query' => array('module' => $module, 'path' => '/')));
      $path = './' . drupal_get_path('module', $module);
      $rows[$module] = array($name, $path);
    }
    
    uksort($rows, function($a, $b) {
      return strnatcmp($a, $b);
    });
    
    return array(
      '#theme' => 'table',
      '#header' => array('Module', 'Directory'),
      '#rows' => $rows,
    );
  }

  private function formatFileSize($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    //return round($bytes, $precision) . ' ' . $units[$pow];
    return round($bytes, 0) . ' ' . $units[$pow];
  }

  private function renderModuleDir($dir) {
    $bc = drupal_get_breadcrumb();
    $bc[] = l('Source', $this->base_path);
    $bc[] = l($this->module . '.module', $this->base_path, array('query' => array('module' => $this->module)));
    if ($path = trim(dirname($this->path), '/')) {
      $bc[] = l($path, $this->base_path, array('query' => array('module' => $this->module, 'path' => dirname($this->path))));
    }
    drupal_set_breadcrumb($bc);

    $items = scandir($dir);
    foreach ($items as $name) {
      if ($name === '.') { continue; }
      if ($name === '.DS_Store') { continue; }
      if ($name === '._.DS_Store') { continue; }
      if ($name === 'nbproject') { continue; }

      $file   = "{$dir}/{$name}";
      if ($name === '..') {
        $_name  = l($name, "{$this->base_path}", array('query' => array('module' => $this->module, 'path' => dirname($this->path))));
      }
      else {
        $_name  = l($name, "{$this->base_path}", array('query' => array('module' => $this->module, 'path' => trim($this->path . '/' . $name, '/'))));
      }

      $_stats = stat($file);

      $rows[$file] = array(
        is_dir($file) ? at_icon('folder-o').'  '."<strong>{$_name}/</strong>" : at_icon('file-text-o').'  '.$_name,
        $_stats[4],
        $_stats[5],
        $this->formatFileSize($_stats[7]),
        format_date($_stats[9], 'short'),
      );
    }
    
    uksort($rows, function($a, $b) {
      // Parent directory first.
      if (is_dir($a) && '..' === substr($a, -2)) return -1;
      if (is_dir($b) && '..' === substr($b, -2)) return  1;

      if (is_dir($a) xor is_dir($b)) {
        // And then directory if the type is difference.
        if (is_dir($a)) return -1;
        if (is_dir($b)) return  1;
      }

      // Compare by basename, if the type is the same.
      return strcmp(basename($a), basename($b)) < 0 ? -1 : 1;
    });

    $output = theme('table', array(
        'header' => array('Name', 'UID', 'GID', 'Size', 'Modified'), 
        'rows' => $rows
    ));
    if ($this->renderModuleDirReadMe($dir)) {
      $output .= theme('table', array(
        'header' => array('README'),
        'rows' => array(0 => array($this->renderModuleDirReadMe($dir))),
      )); 
    }
    
    return $output;
  }
  
  private function renderModuleDirReadMe($dir) {
    if (is_file("{$dir}/README.txt")) {
      return '<pre><code>'. file_get_contents("{$dir}/README.txt") .'</code></pre>';
    }

    if (is_file("{$dir}/README.md")) {
      require_once at_library('parsedown') . '/Parsedown.php';
      $output = file_get_contents("{$dir}/README.md");
      return '<div class="readme parsedown">' . at_id(new \Parsedown())->text($output) . '</div>';
    }
  }

  private function renderModuleFile($file) {
    $bc = drupal_get_breadcrumb();
    $bc[] = l('Source', $this->base_path);
    $bc[] = l($this->module . '.module', $this->base_path, array('query' => array('module' => $this->module)));
    if ($path = trim(dirname($this->path), '/')) {
      $bc[] = l($path, $this->base_path, array('query' => array(
        'module' => $this->module, 'path' => dirname($this->path)
      )));
    }
    drupal_set_breadcrumb($bc);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file);

    switch ($mime_type) {
      case 'text/x-php':
        $type = 'php'; break;

      case 'image/png':
      case 'image/jpg':
      case 'image/jpeg':
      case 'image/gif':
        return '<img src="'. $GLOBALS['base_path'] . drupal_get_path('module', $this->module) .'/'. trim($this->path, '/') .'" />';

      default:
        $type = 'unknown';
        break;
    }

    if ('unknown' === $type) {
      switch (pathinfo($file, PATHINFO_EXTENSION)) {
        case 'module':
        case 'install':
        case 'inc':
        case 'php':
          $type = 'php';
          break;

        case 'css':
        case 'scss':
        case 'less':
          $type = 'css';
          break;

        case 'js':
          $type = 'javascript';
          break;

        case 'twig':
          $type = 'twig';
          break;

        case 'yml':
        case 'yaml':
          $type = 'yaml';
          break;
      }
    }
    
    return drupal_get_form('at_ui_display_file', $file, $type);
  }
}
