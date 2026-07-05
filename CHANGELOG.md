# Changelog

## 1.0.3 - 2026-07-05

- Fixed Mautic 7 form submission validation so SmartCaptcha fields are not
  treated as view-only before the custom validator runs.

## 1.0.2 - 2026-07-05

- Fixed the Mautic integration service id so Mautic 7 registers the plugin as a
  configurable integration instead of showing only the generic bundle info view.
- Added the standard `Assets/img/icon.png` fallback so the plugin card uses the
  official Yandex icon even before integration metadata is loaded.

## 1.0.1 - 2026-07-05

- Added the Yandex icon asset and wired it as the Mautic integration icon.
- Added an in-plugin setup note explaining where to create SmartCaptcha,
  which domains to add, and where to paste the Client key and Server key.
- Added Russian translations for the plugin settings, form field and setup note.

## 1.0.0 - 2026-07-05

- Initial standalone Yandex SmartCaptcha field and server-side validation plugin
  for Mautic 5, 6 and 7.
