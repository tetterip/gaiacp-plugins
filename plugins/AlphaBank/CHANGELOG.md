# Changelog

Every version of this plugin, newest first. The version is in `plugin.json`; it is installed from the plugin catalogue.

## [1.0.1] - 2026-11-17

- Moved out of GaiaCP into the plugin catalogue: install it under Settings → System → Plugin catalogue. An install that
  had it keeps it, with its settings and payments, and updates it from the catalogue from now on. No change to
  how it works.

## [1.0.0] - 2026-11-12

- Card payments through Alpha Bank e-Commerce (Cardlink): a "Pay by card (Alpha Bank)" button on unpaid payment
  notices in the client area opens the bank's own page. The payment is recorded only when the bank's answer carries a
  valid digest (signed with the shared secret) and names the amount GaiaCP asked for, exactly once. Answers that never
  come are closed after two hours. Settings → Alpha Bank for the merchant ID, shared secret and test or live mode.
  Ported from the WooCommerce plugin by Papaki (Enartia S.A.), GPL-3.0-or-later
