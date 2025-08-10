# Bonza Quote Form

A simple WordPress plugin that provides a quote request form via shortcode and an admin interface to review and approve/reject submissions.

## Project Info

- **Contributors**: uniezuka
- **Donate**: `https://github.com/uniezuka/`
- **Tags**: forms, quotes, crm, admin
- **Requires WordPress**: 6.0+
- **Tested up to**: 6.6
- **Requires PHP**: 7.4+
- **Stable tag**: 1.0.0
- **License**: GPLv2 or later (`http://www.gnu.org/licenses/gpl-2.0.html`)

## Description

Bonza Quote Form lets you collect quote requests on any page using a shortcode and manage them from the WordPress admin.

### Features

- **Frontend shortcode**: `[bonza_quote_form]` with fields: Name, Email, Service Type, Notes
- **Persists submissions** to a custom database table with status `pending`
- **Admin page** “Bonza Quotes” to list, search, paginate, and approve/reject submissions
- **Security best practices**: nonces, capability checks, prepared statements, output escaping
- **Translation-ready** (`bonza_quote` text domain)
- **Developer hooks and filters** for extensibility

### How it works

- On activation, the plugin creates a table `{$wpdb->prefix}bonza_quotes` to store submissions
- Visitors submit the form; valid entries are saved as `pending` and a success message is shown
- Administrators can approve or reject entries from the “Bonza Quotes” menu
- On new submission, the site admin receives an email notification (subject and body are filterable)

## Installation

1. Upload the `bonza_quote` folder to `/wp-content/plugins/` or install via the WordPress Plugins screen
2. Activate the plugin through the “Plugins” menu
3. Create or edit a page and add the shortcode: `[bonza_quote_form]`
4. View and manage submissions in “Bonza Quotes” in the admin sidebar

## Usage

- **Shortcode**: `[bonza_quote_form]`

### Behavior

- After submission, the user is redirected back with a success or error message
- Submissions are saved as `pending`; admins can change status to `approved` or `rejected`

### Customize service types

Developers can filter the Service Type options shown in the form using the `bonza_quote/service_types` filter.

Example (add to a small custom plugin or your theme’s `functions.php`):

```php
add_filter( 'bonza_quote/service_types', function( $types ) {
    return array( 'General Inquiry', 'Web Design', 'SEO', 'Maintenance', 'Consulting' );
} );
```

## Developer Reference

### Actions

- `bonza_quote/submitted` fires after a quote is saved — params: `(int $quote_id, array $data)`
- `bonza_quote/status_changed` fires when status changes — params: `(int $quote_id, string $old, string $new)`

### Filters (Email)

- `bonza_quote/admin_email_subject` — `(string $subject, int $quote_id, array $data)`
- `bonza_quote/admin_email_body` — `(string $body, int $quote_id, array $data)`

## FAQ

### Where do I see the submissions?

Go to “Bonza Quotes” in the WordPress admin.

### Can I change the service type list?

Yes. Use the `bonza_quote/service_types` filter shown above.

### Does uninstall remove data?

Yes. Uninstall drops the custom table `{$wpdb->prefix}bonza_quotes`.

## Screenshots

1. Frontend quote form
2. Admin list table with approve/reject actions

## Changelog

### 1.0.0

- Initial release: shortcode form, custom table, admin list with approve/reject, basic search and pagination

## License

This project is licensed under the GPLv2 (or later). See `LICENSE.txt` or `http://www.gnu.org/licenses/gpl-2.0.html` for details.
