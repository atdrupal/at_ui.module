<?php
namespace Drupal\at_ui\Controller\Reports;

class SourceCode {
  private $base_path = 'admin/reports/documentation/at_base/source';
  private $module;
  private $path;

  public function render($module) {
    $this->module = $module;
    $this->path = substr($_GET['q'], strlen($this->base_path . "/{$this->module}/"));

    $path = DRUPAL_ROOT . '/' . trim(drupal_get_path('module', $this->module) . '/' . $this->path, '/');
    if (is_dir($path)) {
      return $this->renderModuleDir($path);
    }

    return $this->renderModuleFile($path);
  }

  private function formatFileSize($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    return round($bytes, $precision) . ' ' . $units[$pow];
  }

  private function renderModuleDir($dir) {
    $bc = drupal_get_breadcrumb();
    $bc[] = l('Source', $this->base_path);
    $bc[] = l("{$this->module}.module", "{$this->base_path}/{$this->module}");
    if ($path = trim(dirname($this->path), '/.')) {
      $bc[] = l($path, "{$this->base_path}/{$this->module}/" . dirname($this->path));
    }
    drupal_set_breadcrumb($bc);

    $items = scandir($dir);
    foreach ($items as $name) {
      switch ($name) {
        case '':
        case '.':
        case '.DS_Store':
        case '._.DS_Store':
        case 'nbproject':
          continue;
        default:
          $file = "{$dir}/{$name}";
          $_name  = l(
              $name,
              $name !== '..'
                ? "{$this->base_path}/{$this->module}/" . trim($this->path . '/' . $name, '/')
                : "{$this->base_path}/{$this->module}/" . dirname($this->path)
          );

          $_stats = stat($file);

          $rows[$file] = array(
            is_dir($file) ? "<strong>{$_name}/</strong>" : $_name,
            $_stats[4],
            $_stats[5],
            $this->formatFileSize($_stats[7]),
            format_date($_stats[9], 'short'),
          );

          break;
      }
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

    return array('#theme' => 'table',
      '#header' => array('Name', 'UID', 'GID', 'Size', 'Modified'),
      '#rows' => $rows,
      '#suffix' => $this->renderModuleDirSuffix($dir)
    );
  }

  private function renderModuleDirSuffix($dir) {
    return $this->renderModuleDirReadMe($dir) . $this->renderModuleAPI($dir);
  }

  private function renderModuleAPI($dir) {
    if (is_file("{$dir}/{$this->module}.api.php")) {
      $file = "{$dir}/{$this->module}.api.php";
      include_once $file;
      foreach (file($file) as $line) {
        if (strpos($line, "function hook_") === 0) {
          $hook = trim(preg_replace('/function hook_([a-z0-9_]+).+$/i', '$1', $line));

          $rows[] = array(
            "{$hook}()",
            $this->parseFunctionDocBlock("hook_{$hook}"),
            theme('item_list', array('items' => array_map(function($module) use ($hook) { return "{$module}_{$hook}"; }, module_implements($hook))))
          );
        }
      }

      if (!empty($rows)) {
        return theme(
          'table',
          array(
            'header' => array('Hook', 'Comment', 'Implementations'),
            'rows' => $rows,
            'caption' => '<h2>Module hooks</h2>',
          )
        ) . '<style>body table td { vertical-align: top; }</style>';
      }
    }

    return '';
  }

  private function parseFunctionDocBlock($fn) {
    require_once at_library('parsedown') . '/Parsedown.php';

    $comment = at_id(new \ReflectionFunction($fn))->getDocComment();
    $lines = explode("\n", $comment);
    foreach ($lines as $i => &$line) {
      $line = trim($line);
      if ($line === '/**') { unset($lines[$i]); continue; }
      if ($line === '*/') { unset($lines[$i]); continue; }
      $line = ltrim($line, '* ');
    }

    return at_id(new \Parsedown())->text(implode("\n", $lines));
  }

  private function renderModuleDirReadMe($dir) {
    if (is_file("{$dir}/README.txt")) {
      return '<pre><code>'. file_get_contents("{$dir}/README.txt") .'</code></pre>';
    }

    if (is_file("{$dir}/README.md")) {
      require_once at_library('parsedown') . '/Parsedown.php';
      $output = file_get_contents("{$dir}/README.md");
      $parser = new \Parsedown();
      return '<div class="readme parsedown">' . $parser->text($output) . '</div>';
    }
  }

  private function renderModuleFile($file) {
    $bc = drupal_get_breadcrumb();
    $bc[] = l('Source', $this->base_path);
    $bc[] = l($this->module . '.module', "{$this->base_path}/{$this->module}");
    if ($path = trim(dirname($this->path), '/.')) {
      $bc[] = l($path, "{$this->base_path}/{$this->module}" . dirname($this->path));
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

        case 'json':
          if ($json = json_decode(file_get_contents($file), TRUE)) {
            return atdr($json);
          }
          break;
      }
    }

    return drupal_get_form('at_ui_display_file', $file, $type);
  }
}
