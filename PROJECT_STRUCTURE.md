# Bonza Quote Plugin – Project Structure

This document provides a developer-focused overview of the repository layout, responsibilities of each directory/file, and common extension points.

## Top-Level
- `bonza_quote.php`: Plugin bootstrap. Defines metadata, hooks activation/deactivation, loads core class, and runs the plugin.
- `README.txt`: Plugin readme (WordPress standard format).
- `LICENSE.txt`: License information.
- `uninstall.php`: Placeholder for uninstall cleanup logic.
- `index.php`: Silence is golden (prevents directory listing).
- `languages/`: Translation templates and MO/PO files.

## Directories

### `includes/` (Core)
Holds core classes loaded by the bootstrap and used across admin/public layers.
- `class-bonza_quote.php`: Core plugin class. Initializes dependencies, sets up i18n, and registers admin/public hooks via the loader.
- `class-bonza_quote-loader.php`: Hook orchestrator. Collects actions/filters and registers them with WordPress.
- `class-bonza_quote-i18n.php`: Internationalization loader (textdomain setup under `plugins_loaded`).
- `class-bonza_quote-activator.php`: Activation routine entry point (e.g., create tables, roles, options). Called by `register_activation_hook` in `bonza_quote.php`.
- `class-bonza_quote-deactivator.php`: Deactivation routine entry point (e.g., clear scheduled events). Called by `register_deactivation_hook`.
- `index.php`: Directory index guard.

Extension points:
- Add new service/model classes here (e.g., data access, domain logic).
- Register additional hooks by extending `Bonza_quote` and adding through `Bonza_quote_Loader`.

### `admin/` (WP Admin UI)
Admin-only assets and logic for the dashboard.
- `class-bonza_quote-admin.php`: Admin controller. Enqueue admin styles/scripts and define admin-specific hooks. Good place to add admin menus/pages.
- `css/bonza_quote-admin.css`: Admin styles.
- `js/bonza_quote-admin.js`: Admin scripts.
- `partials/bonza_quote-admin-display.php`: Example admin view partial (rendered from the admin controller).
- `index.php`: Directory index guard.

Extension points:
- Add menu pages via `admin_menu` hook inside `class-bonza_quote-admin.php`.
- Add settings pages and forms; render with partials.

### `public/` (Frontend UI)
Public-facing assets and logic.
- `class-bonza_quote-public.php`: Public controller. Enqueue public styles/scripts and define shortcodes/widgets.
- `css/bonza_quote-public.css`: Public styles.
- `js/bonza_quote-public.js`: Public scripts.
- `partials/bonza_quote-public-display.php`: Example public view partial.
- `index.php`: Directory index guard.

Extension points:
- Register shortcodes/widgets via methods in `class-bonza_quote-public.php` hooked from `includes/class-bonza_quote.php`.
- Render complex markup via partials.

### `languages/`
- `bonza_quote.pot`: Base POT file for translations. Generate/update with WP-CLI or tools like Poedit.

## Common Additions (for this project’s goals)
- Frontend: Implement `[bonza_quote_form]` shortcode in `public/class-bonza_quote-public.php` and render via a new partial.
- Data layer: Add a Quote model in `includes/` and create required tables in `class-bonza_quote-activator.php`.
- Admin: Add a “Bonza Quotes” menu/page in `admin/class-bonza_quote-admin.php` to list and manage submissions.
- Optional: Email notifications using `wp_mail` and custom actions/filters inside your controller methods to improve extensibility.

## Testing (suggested layout)
If you add PHP unit tests, a common structure is:
- `tests/` – PHPUnit tests and bootstrap.
- `phpunit.xml.dist` – PHPUnit config.
- `bin/install-wp-tests.sh` – Test bootstrap (if using WP core integration tests).
