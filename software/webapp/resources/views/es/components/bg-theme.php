
<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$pathWithoutLang = preg_replace('#^/(en|es)(?=/|$)#', '', $currentPath);
$pathWithoutLang = '/' . ltrim((string)$pathWithoutLang, '/');
if ($pathWithoutLang === '//') { $pathWithoutLang = '/'; }
$query = $_SERVER['QUERY_STRING'] ?? '';
$querySuffix = $query !== '' ? ('?' . $query) : '';
$esUrl = $pathWithoutLang . $querySuffix;
$enUrl = ($pathWithoutLang === '/' ? '/en' : ('/en' . $pathWithoutLang)) . $querySuffix;
?>

  <div class="position-fixed bottom-0 end-0 mb-3 me-3 d-flex flex-column gap-2 bd-mode-toggle">
    <div class="dropdown">
      <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
              id="bd-lang"
              type="button"
              aria-expanded="false"
              data-bs-toggle="dropdown"
              aria-label="Idioma">
        <span class="small fw-semibold">ES</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-lang">
        <li><a class="dropdown-item" href="<?= htmlspecialchars($esUrl) ?>">Español</a></li>
        <li><a class="dropdown-item" href="<?= htmlspecialchars($enUrl) ?>">English</a></li>
      </ul>
    </div>

    <div class="dropdown">
    <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
            id="bd-theme"
            type="button"
            aria-expanded="false"
            data-bs-toggle="dropdown"
            aria-label="Toggle theme (auto)">
      <svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#circle-half"></use></svg>
      <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
          <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#sun-fill"></use></svg>
          Light
          <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
        </button>
      </li>
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
          <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
          Dark
          <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
        </button>
      </li>
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
          <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#circle-half"></use></svg>
          Auto
          <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
        </button>
      </li>
    </ul>
  </div>
  </div>