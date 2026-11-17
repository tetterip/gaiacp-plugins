# Changelog

Every version of this plugin, newest first. The version is in `plugin.json`; it is installed from the plugin catalogue.

## [1.1.1] - 2026-11-17

- Moved out of GaiaCP into the plugin catalogue: install it under Settings → System → Plugin catalogue. An install that
  had it keeps it, with its settings and data, and updates it from the catalogue from now on. No change to how it
  works.
- Reads the demo switch from the config instead of a class GaiaCP keeps for its own plugins.

## [1.1.0] - 2026-11-13

- The QR code window has an **I've paid** button. It records nothing: the admins' bell rings, the admins with Receive system emails get an email (Settings → Automation → Payment claim email), and the notice shows the claim until you record the payment (IRIS is already picked) or reject it. Pressed once per notice; a rejected claim can be made again.

## [1.0.0] - 2026-11-12

- First version: clients pay their payment notices by IRIS (Greece's instant bank payments). Settings → IRIS holds the payee, the IRIS number or alias and the fixed QR code your bank gave you; unpaid notices in the client area get a **Pay with IRIS** button that shows them with the amount and the notice number as the reference. Payments are recorded by hand with the IRIS method.
