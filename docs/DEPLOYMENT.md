# Deployment

Server directory `~/www/html/marcelkraus`, on the account `krswrk`, host `nix`. Mechanism, deploy keys and the mailer are in `../../../docs/DEPLOYMENT.md`, including the rule that **`marcelkraus.de` must never be registered as a mail domain on this account**—its MX points at `deimos` while the site runs on `nix`.

| # | | |
| --- | --- | --- |
| 1 | Canonical host | `https://www.marcelkraus.de` |
| 2 | The other addresses | The bare apex, and `marcel-kraus.de` with and without `www`, answer 301 to it and carry the path along. The hyphenated spelling is what somebody types who has only heard the name; it is not a second site and never serves content of its own. The rules sit in `public/.htaccess`, host-scoped rather than as a catch-all, so ddev and any host not named there stay untouched |
| 3 | Elsewhere | Nothing. The measurement this site is counted in answers at `analytics.krausgebaut.de`—another domain on another account, named by the tracking code and by the privacy policy and run by neither this repository nor this host |

**Each of the four hostnames needs a web domain of its own on the server.** A rule in `public/.htaccess` is never read for a host Apache does not serve—without `uberspace web domain add`, the hyphenated spelling keeps showing whatever the registrar parks and no redirect happens.

