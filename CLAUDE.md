# personal-homepage
> Übergreifender Kontext > personal-homepage

Kurzname: homepage

Dieses Projekt ist Teil meiner IT-Landschaft – einer Sammlung
privater und gewerblicher Projekte. Übergeordneter Kontext und
Struktur sind im „Übergreifenden Kontext" dokumentiert.

## Beschreibung

Meine persönliche Portfolio-Website (https://www.marcelkraus.de). Die
Seite stellt meine Tätigkeiten, Karrieremeilensteine und Projekte
vor – als minimalistische Visitenkarte. Die Website ist
zweisprachig (Deutsch/Englisch) verfügbar.

## Technologie-Stack

- **Backend:** Symfony 8.0, PHP 8.4
- **Templates:** Twig
- **Styling:** Tailwind CSS 4 mit Typography-Plugin
- **Internationalisierung:** Symfony Translation (YAML-Dateien)
- **Karte:** Leaflet (lokal aus `public/vendor/leaflet/` ausgeliefert) mit OpenStreetMap-Kacheln
- **Analytics:** Matomo (cookie-free, Site ID 10)
- **Entwicklung:** DDEV (Nginx-FPM, MariaDB 10.4)

## Entwicklungsumgebung

### DDEV starten

```bash
ddev start
```

Zugriff über: https://personal-homepage.ddev.site

### Tailwind CSS kompilieren

```bash
# Entwicklung mit Watch-Modus:
ddev exec npm run dev

# Produktions-Build:
ddev exec npm run build
```

### Symfony-Befehle

```bash
ddev exec php bin/console cache:clear
ddev exec php bin/console debug:routes
```

## Projektstruktur

```
personal-homepage/
├── config/
│   ├── content/
│   │   ├── hobbies.json             ← Hobbys/Leidenschaft (DE + EN, via language-Feld)
│   │   ├── milestones.json          ← Meilensteine (DE + EN, via language-Feld)
│   │   └── projects.json            ← Projekte (DE + EN, via language-Feld)
│   └── packages/
│       └── translation.yaml         ← Übersetzungskonfiguration
├── src/
│   ├── Controller/
│   │   └── DefaultController.php    ← Alle Routes (nur GET)
│   ├── Entity/
│   │   ├── Hobby.php                ← Hobby-Entity
│   │   ├── Milestone.php            ← Meilenstein-Entity
│   │   └── Project.php              ← Projekt-Entity
│   └── EventListener/
│       └── LocaleRedirectListener.php ← Locale-Erkennung (Accept-Language)
├── templates/
│   ├── base.html.twig               ← Basis-Template mit Header, Footer, Matomo
│   ├── partials/
│   │   └── social_links.html.twig   ← Wiederverwendbare Social-/Kontakt-Icons
│   └── default/
│       ├── homepage.html.twig       ← Startseite
│       ├── imprint.de.html.twig     ← Impressum (Deutsch)
│       ├── imprint.en.html.twig     ← Imprint (Englisch)
│       ├── data-privacy.de.html.twig ← Datenschutz (Deutsch)
│       └── data-privacy.en.html.twig ← Privacy Policy (Englisch)
├── translations/
│   ├── messages.de.yaml             ← Deutsche UI-Strings
│   └── messages.en.yaml             ← Englische UI-Strings
├── public/
│   ├── css/
│   │   ├── input.css                ← Tailwind-Quell-CSS
│   │   └── output.css               ← Kompiliertes Tailwind CSS
│   ├── fonts/                       ← Aller-Schriftfamilie
│   ├── images/                      ← Bilder (Avatar, Logos, Projekte)
│   ├── vendor/leaflet/              ← Lokal ausgelieferte Leaflet-Bibliothek
│   ├── robots.txt                   ← Crawling (permissiv, Sitemap-Verweis)
│   └── sitemap.xml                  ← Sitemap (Homepage DE/EN, indexierbar)
├── .ddev/config.yaml
├── composer.json
└── package.json
```

## Meta-Daten / SEO

`base.html.twig` stellt überschreibbare Blöcke bereit: `title`,
`meta_description` und `meta_robots`. OG-/Twitter-Titel und
-Description leiten sich daraus ab (`block('title')` /
`block('meta_description')`). Unterseiten überschreiben die Blöcke;
Impressum und Datenschutz setzen `meta_robots` auf
`noindex,follow`. Titel-Schema der Unterseiten: `{Seite} · Marcel
Kraus`. `robots.txt` ist permissiv, `sitemap.xml` listet nur die
indexierbaren Seiten (Homepage DE/EN). E-Mail-Adressen werden über
`data-email-*`-Attribute und ein kleines Skript in `base.html.twig`
spamgeschützt zusammengesetzt. Das Social-Sharing-Bild liegt unter
`public/images/sharing.jpg` (1200×630) und ist als
`og:image`/`twitter:image` mit `twitter:card = summary_large_image`
eingebunden.

Hero-Titel und strukturierte Daten sind bewusst entkoppelt: Die
sichtbare Hero-Zeile nutzt `hero.roles` (Persönlichkeits-Tagline,
z. B. „Entwickler · Schrauber · Maker"), während das `jobTitle`-Feld
des JSON-LD und der Seiten-`<title>` über `hero.job_title` /
`meta.title` die präzise Berufsbezeichnung („Senior Software-
Entwickler") behalten.

## Routing

Jede Seite hat zwei Routen: eine deutsche (ohne Präfix) und eine
englische (mit `/en`-Präfix). Namensschema: `app_{locale}_{name}`.

| Route | Name | Beschreibung |
|-------|------|-------------|
| `GET /` | `app_de_homepage` | Startseite (Deutsch) |
| `GET /en` | `app_en_homepage` | Homepage (Englisch) |
| `GET /impressum` | `app_de_imprint` | Impressum |
| `GET /en/imprint` | `app_en_imprint` | Imprint |
| `GET /datenschutz` | `app_de_data_privacy` | Datenschutz |
| `GET /en/privacy-policy` | `app_en_data_privacy` | Privacy Policy |

Zusätzlich gibt es eine sprachneutrale Utility-Route
`GET /kontakt-per-whats-app` (`app_contact_whats_app`), die per 302 auf
den WhatsApp-Kurzlink weiterleitet (hält die Nummer aus dem Markup; vom
WhatsApp-Icon in `social_links.html.twig` verlinkt).

## Internationalisierung

- **Standardsprache:** Deutsch (kein URL-Präfix)
- **Locale-Erkennung:** `LocaleRedirectListener` prüft auf `/`
  den `Accept-Language`-Header; bei englischem Browser → 302 auf
  `/en`. Ein `locale`-Cookie überschreibt die Erkennung.
- **UI-Strings:** `translations/messages.{de,en}.yaml`, in
  Templates über `{{ 'key'|trans }}`
- **JSON-Inhalte:** Jeder Eintrag hat ein `language`-Feld
  (`de`/`en`), Controller filtert nach Locale
- **Rechtstexte:** Separate Templates pro Sprache; englische
  Versionen enthalten einen Hinweis auf die maßgebliche deutsche
  Fassung

## Inhalte

Die Inhalte der Startseite werden aus JSON-Dateien geladen und über
den Symfony Serializer in Entities deserialisiert:

- `config/content/milestones.json` – Berufliche Meilensteine
- `config/content/projects.json` – Projekte und Arbeiten
- `config/content/hobbies.json` – Hobbys/Leidenschaft-Sektion (Feld
  `icon`: `wrench`, `wheel` oder `remote`; das Template mappt den
  Wert auf ein Inline-SVG)

Jeder Eintrag enthält ein `language`-Feld als erstes Feld.
Änderungen an Inhalten erfordern keine Code-Änderungen.

## Tailwind-Konfiguration

Die Tailwind-Konfiguration erfolgt über `public/css/input.css`
(Tailwind CSS 4). Eigene Farben und Design-Tokens sind dort als
`@theme`-Block definiert:

- `accent`: #607E4A (Markenfarbe)
- Markenfarben: `brand-marcelkraus`, `brand-krausgebaut`,
  `brand-krausgeborgt`, `brand-krausgedruckt`
- Eigene Schriftfamilie: Aller (Regular, Bold)

Plugin: `@tailwindcss/typography`

### Layout-Container

Inhalte sind auf einen zentrierten Container `max-w-6xl mx-auto`
(mit `px-8`) begrenzt: Startseiten-Sektionen sowie der Inhalt von
Header und Footer richten sich an derselben Kante aus. Vollflächig
(full-bleed) bleiben dagegen das Hero-Raster (`100svh`) und die
Hintergrund-/Rahmen-Leisten von Header und Footer. Rechtstexte nutzen
weiterhin das schmalere `max-w-3xl` (Lesbarkeit der Prosa).

### Navigation (Header)

Sektions-Navigation, Theme- und Sprachumschalter bündelt der Header in
`#header-controls` (eine einzige Instanz). Auf Desktop (ab `sm`) steht
der Block inline rechts; auf Mobile ist er ausgeblendet und klappt über
den rechtsbündigen Hamburger (`#btn-menu`) als Dropdown auf
(Klasse `.mobile-open`, Styles in `input.css`). Eine graue Trennlinie
trennt im Mobile-Menü die Sektions-Links von den Optionen. Das Menü
schließt bei Außenklick, Escape und beim Wechsel auf Desktop; der Fokus
wird beim Öffnen ins Menü und beim Schließen zurück auf den Hamburger
geführt. Die Sektions-Links liefert die Startseite über den
`header_nav`-Block. Die Startseite gliedert sich in die Sektions-Anker
`#milestones`, `#work`, `#hobbies` und `#location`; das Scroll-Spy-Skript
hebt den jeweils aktiven Link hervor (Liste der Sektionen im Skript
synchron halten).

## Code-Konventionen

- **Kommentare sind grundsätzlich Englisch** – das gilt auch für
  Twig-Kommentare (`{# … #}`), inklusive der Sektions-Trenner. Damit
  bleiben Twig, JavaScript und PHP einheitlich. Sichtbarer Inhalt
  (Übersetzungen, Anzeige-Labels) und die deutschen Projekt-Dokumente
  sind davon ausgenommen.
- **Bezeichner sind sprachneutral/Englisch und stabil.** Sie folgen
  der Domäne, nicht der Anzeige: Entity `Hobby`, `hobbies.json`,
  Anker `#hobbies` – auch wenn die Kachel „Leidenschaft" heißt.
  Übersetzungs-Keys nutzen denselben Stamm (`hobbies.*`,
  `nav.hobbies`).

## Umgebungsvariablen

| Variable | Beschreibung |
|----------|-------------|
| `APP_ENV` | Umgebung (`dev` / `prod`) |
| `APP_SECRET` | Symfony Secret |
