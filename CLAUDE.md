# marcelkraus

This project lives in the krauswerk, in the group Brand Family, and this
document is not complete on its own. The rules it follows stand a level up –
`../../docs/WEB_STACK.md`, `../../docs/DEPLOYMENT.md` and the group's
`../docs/BRAND_FAMILY.md` – and a session inside the workspace has the
`CLAUDE.md` of the workspace and of the group loaded. Read alone, in a
repository cloned elsewhere, it lacks that context. This document carries only
what is true of this project alone.

## Overview

Personal website of Marcel Kraus (https://www.marcelkraus.de). It does two jobs,
in this order:

1. **A curriculum vitae for job applications.** Career, competences, contact. No
   projects, no references, no app listings – the portfolio lives on krausgebaut
   and, in part, krausgedruckt.
2. **The hub of the brand family.** It carries the logo holding all three brands
   and points at the two commercial ones.

German only, one color scheme (light). One page plus the two legal pages.
Guiding rule for every case of doubt: **keep the site simple.**

Directory, ddev project, composer package and the GitHub repository are all
called `marcelkraus`.

## Stack notes

* **No print stylesheet.** The document is generated, not printed from the
  browser: a stylesheet cannot hold a letterhead grid to the millimetre, and
  every browser decides its own margins and running heads. `/lebenslauf` is the
  printed version; the browser's own print output is not supported.
* **The typography plugin is loaded but unused** (no `prose` in the templates).
  It stays because both siblings carry it and the stack is meant to be
  identical – if it goes, it goes in all three at once.

## Development

```bash
ddev start                                    # https://marcelkraus.ddev.site
ddev launch -m                                # Mailpit, captured mail
ddev exec npm run build                       # Tailwind, minified
ddev exec npm run dev                         # Tailwind, watch mode
```

The career timeline and the brand band hold literal maps of class names in
`homepage.html.twig`, because a utility class can never be assembled from a
variable.

## Layout

```
config/content/     milestones.json, skills.json, hobbies.json, brands.json
src/Controller/     DefaultController – all routes, form handling, JSON loading
src/Dto/            ContactRequest
src/EventListener/  SecurityHeadersListener
src/Content/        ContentRepository – the JSON files, one reader for both outputs
src/Pdf/            CurriculumVitaeRenderer – page geometry, fonts, the header
src/Service/        AvailabilityCalculator – the availability month, computed
                    GermanDateFormatter – twelve month names, in one place
assets/fonts/       static JetBrains Mono, derived; never served, only embedded
templates/pdf/      curriculum-vitae.html.twig
templates/          base.html.twig, default/, partials/
public/             css/, fonts/, images/, favicon.*, apple-touch-icon.png
```

Partials: `_logo`, `_eyebrow` (mono label with square marker and optional
section number), `_icons`, `_button_class` (two sizes, two tones),
`_contact_form`, `_tags` (technology tags of a career station, shared by a
station and by the roles nested inside one).

`_icons` keeps **no stock**: every name has a caller, and the names are
alphabetical. Most come from Heroicons v2 outline; `remote` and `wheel` are
drawn for this site, so a name missing from the Heroicons catalog is not
necessarily missing here.

## Routing

| # | Route | Name | Description |
|---|-------|------|-------------|
| 1 | `GET /` | `app_homepage` | The curriculum vitae, single page |
| 2 | `POST /kontakt` | `app_contact` | Form handling (PRG) |
| 3 | `GET /kontakt-per-email` | `app_contact_email` | Redirect to `mailto:` |
| 4 | `GET /kontakt-per-whats-app` | `app_contact_whats_app` | Redirect to WhatsApp |
| 5 | `GET /lebenslauf` | `app_curriculum_vitae_pdf` | The printed curriculum vitae, generated |
| 6 | `GET /impressum` | `app_imprint` | Imprint (`noindex,follow`) |
| 7 | `GET /datenschutz` | `app_data_privacy` | Privacy policy (`noindex,follow`) |
| 8 | `GET /robots.txt` | `app_robots` | robots, absolute sitemap URL |
| 9 | `GET /sitemap.xml` | `app_sitemap` | Sitemap (the homepage) |

Route names carry no locale segment: the site is single-language.


## Design

The light "spec-sheet" look of the family with the curriculum-vitae additions
below. No dark mode. Tokens, contrast rules and the family bracket are in
`../docs/BRAND_FAMILY.md`.

**What this site does differently:**

* **Section numbers** (`01` … `05`) sit in the eyebrow, in mono – the family's
  technical voice applied to a curriculum-vitae convention, structuring a long
  single page without a table of contents. **The number carries the accent, the
  label does not**, so the structure reads before the label. Both siblings set
  the whole eyebrow in the accent. Contrast is not the reason;
  `accent-on-light` measures 6.48:1 on `neutral-100`.
* **Four contact rows in the footer**, because LinkedIn belongs on a curriculum
  vitae and not on a sales page.
* **The hero carries two buttons**, the document filled and Kontakt outlined
  beside it; `_button_class` has a second tone for that rather than a second
  copy of the skin. The document opens in a new tab – the one case besides an
  external link – because it is served inline and would otherwise replace the
  page. The accessible name announces it.
* **Four navigation items:** Werdegang, Kompetenzen, Leidenschaft, Kontakt. The
  closing band is not in the navigation, as on both siblings. **Label and
  eyebrow must match word for word**; a test pins it.
* **The mobile menu carries no contact button.** The navigation already has that
  item, and with the icon button in the bar the word would stand three times in
  one open panel.
* **Three mixed-case mono sizes are three roles** – the milestone tag, the data
  line, the year in the timeline – not one role written three ways. Sizes:
  `text-sm` in the mobile menu, `text-xs` in the normal case,
  `text-[0.6875rem]` for a micro label.

## The body

The body follows the logic of a curriculum vitae rather than the sibling's
section rhythm. Section rhythm: light → `neutral-100` → light → `neutral-100` →
dark, the one dark block being the closing brand band.

* **The career is one single vertical list**, at every width, with a hairline
  rail and one square marker per station – the same marker shape as in the
  eyebrow, the footer lists and the brand band. The rail stops below the last
  station; the marker does not change color there. Every station carries either
  the accent or the color of its brand, so the timeline says at a glance which
  line of the career a station belongs to. **No carousel:** it shows less on a
  desktop than on a phone and keeps hidden cards in the tab order.
* **Several roles at one employer are nested, not listed side by side.** Ten
  years at Chefkoch is the strongest single statement in the document, and two
  stations next to each other read as two jobs rather than one development. The
  employer line carries the whole span, the roles sit indented below it on their
  own hairline. Chefkoch is the only case.
* **The availability is computed, never written out.** `AvailabilityCalculator`
  derives the month from the notice period, so the statement in the hero cannot
  fall out of date between two edits. The word *freigestellt* deliberately
  appears nowhere: it answers a question the reader did not ask and raises one
  they did not have. A date answers the only question there is – when?
* **Competences carry a year where a year says something, and never a rating.**
  A year is checkable; self-awarded scales are an anti-pattern.
* **Voice:** first person singular („ich“), the visitor addressed formally
  („Sie“) – the audience is a hiring company, and it matches krausgebaut.
* **The station texts follow one build.** Responsibility and scope are nominal
  („Mitverantwortlich für…“); what Marcel personally achieved is a full sentence
  in the first person („Ich habe … begleitet“); the trades are purely
  descriptive, because a business describes itself and does not claim an
  achievement.
* **No selling on this site, not even in a trade's description.** A trade says
  what it does, in what process, and stops. Sales copy belongs on krausgebaut.
* **Jurassic Jeep sits in the career and nowhere else** – not in the brand band,
  not in the footer. A curriculum vitae listing two of three registered trades
  invites the question what else is missing, and an omission weighs more than
  the thing omitted. The brand band stays at two cards because the mark is three
  squares for three brands.
* **The passion section carries four cards**, three of which link out. Two of
  them are the deliberate exception to „no projects here“: they make a
  different statement than a portfolio tile – not „I built this for a client“
  but „for this problem I write the software myself“. The Maker card points at
  krausgedruckt, a business rather than a self-written tool, so the section
  carries both kinds of link. The destinations live in `hobbies.json`.

## Brand mark and favicons

`_logo.html.twig` is the three squares and the wordmark as one lockup, the
squares running left to right in the order the brands came into being. Colors
come from `fill-*` classes.

The favicons are the three brand squares on a white tile with a `neutral-300`
hairline – one step darker than the interface hairline, because at 16 px
`neutral-200` disappears. The squares carry the verbatim master colors, which
are exactly what the tokens resolve to; the values are in
`../docs/BRAND_FAMILY.md`.

`portrait.jpg` is a 4:5 crop of a square original.

## Content

`config/content/*.json` is read by `DefaultController::loadContent()` (missing
or malformed → empty list). Editing content needs no code change.

### milestones.json

The rendered order is the file order. **Employment runs newest first; each
business sits directly below the employment it started under.** krausgedruckt
(11/2023) and Jurassic Jeep (05/2021) follow the Chefkoch station, krausgebaut
(01/2002) follows the ASB. Sorting purely by date would push the current
employment below a business that started in 2002, and the first line of a
curriculum vitae has to be the current job.

**Six stations, and the list is not a chronicle.** Entries a reader skips are
left out; the vocational school already says where the career started. The gaps
that leaves – 07 to 10/2002 and 10/2003 to 07/2004 – are covered by krausgebaut,
which runs through the whole timeline from 01/2002.

**Every period is month-precise**, which exists so that gaps are visible – so
the dates have to be right rather than merely plausible. krausgebaut starts on
the date of the trade license, 01/2002. The ASB employment starts 10/2004, with
the freelance work from 04/2004 named in the text rather than folded into the
dates.

Fields: `years` (the printed label, e.g. `05/2017 – 11/2026`), `company`,
`location`, and optionally `position`, `description`, `division`, `tags[]`,
`marker`, `url`, `roles[]`.

**`location` reads `Ort · Zusatz`**, the second part optional: `In Köln`,
`In Kerpen · Abschluss mit Fachhochschulreife`, `In Erftstadt · selbstständig`.
The place is always spelled `In <Ort>`. The field is mono without `uppercase`,
so it prints exactly as it stands and the addition stays lowercase where German
asks for it.

**It carries no duration.** The period already stands in the date column to the
left; a second figure only invites the reader to check one against the other,
and the two rarely agree because counting whole months and counting elapsed time
give different answers.

* **`division`** is a second line under the company name, for a unit such as
  `Bundesverband`.
* **`marker`** takes `krausgebaut`, `krausgedruckt`, `jurassic-jeep` or
  `secondary` and selects the station's color from the literal map in the
  template; without it the station takes the accent. The three trades carry
  their own color, the employments share the accent, and `secondary` sets a
  station back into `neutral-500` – today only the vocational school, so the
  accent belongs to the working life alone. `secondary` also grays the position
  line, the only place a marker value reaches beyond its square.
* **`neutral-500` is the floor for a set-back station, not `neutral-400`.** The
  calmer step measures 2.58:1 on this ground and misses the 3:1 WCAG 1.4.11 asks
  of an indicator; `neutral-500` measures 4.74:1.
* **`url`** hangs the business homepage in the muted location line – label
  `Homepage` plus the `external` glyph, no underline, because the glyph is a
  shape rather than a color and satisfies WCAG 1.4.1 on its own. The label is
  identical on all three, so the `aria-label` names the business; otherwise a
  screen reader announces the same word three times without a subject.
* **`roles[]`** turns one entry into an employer with several roles, each with
  its own `years`, `position`, `description` and `tags[]`; the entry then omits
  its own `position`. Giving every entry a `roles[]` would spare the branch in
  the template and inflate six single-role entries instead.
* An entry **without `description`** renders as a condensed one-liner, which is
  what keeps the vocational school from taking as much room as twelve years at
  the ASB.
* **No employment entry carries an open end.** The only statement about
  availability lives in the hero and is computed there. The businesses are open
  by design (`seit 11/2023`, `seit 05/2021`, `seit 01/2002`).

### skills.json

Groups in order of intensity – the order *is* the statement – entries
alphabetical inside each group: `group`, `entries[]` with `name` and optional
`since`.

* **`Täglich`** carries what is in daily use, **`Regelmäßig`** everything else
  still worked with, **`Arbeitsweise`** the methods, **`Schwerpunkte`** what the
  other three cannot say.
* **Which group carries years is itself the statement.** `Täglich` carries them,
  because „in daily use since 2017“ is a claim worth checking. `Regelmäßig`
  carries none: „since 2014, now and then“ tells a reader nothing and only
  invites arithmetic. `Arbeitsweise` carries them for the dated methods.
  `Schwerpunkte` never – a focus is not something one has „since“, and its
  entries are qualities and activities rather than technologies.
* Inside `Täglich`, a missing year means no year is claimed. That covers the
  operating systems, listed as a user rather than as a developer (`iOS` next to
  `Swift seit 2017` would otherwise read as years of unrecorded Apple
  development), and `Git`, where a guess is worth less than silence.
* Around twenty-five entries; tools are left out, methods stay. The years are
  Marcel's own figures.
* **A station tag is not a competence.** Tags say what a station worked with,
  the groups here say what Marcel can do today. Do not add a rule pairing the
  two lists – and the reverse holds as well: a technology that is neither a
  competence nor a defining part of a station appears in neither list.
* **A year that cannot be defended in an interview is worse than none.** No year
  names when a framework shipped; it names when Marcel started with it.

### hobbies.json

`title`, `icon` (a name from `_icons`), `description`, `knowsAbout[]` (the
schema.org terms for the JSON-LD) and the optional trio `url`, `urlLabel`,
`urlNote`. Without the trio the card renders without its link block.

### brands.json

`name`, `title`, `since`, `description`, `url`, `colorToken`
(`krausgebaut` / `krausgedruckt`), which selects the marker classes from the
literal map in the template.

### Structured data

`knowsAbout` in the JSON-LD is **built from the content files**, not hardcoded:
the skills carry the professional terms, the hobbies the private ones. Sorted
and deduplicated, so reordering a content file does not change the output.

## Contact form

The mechanism is in `../../docs/WEB_STACK.md`. Specific here:

* Fields: name, e-mail, company, phone, message; required are name, e-mail and
  message. **`Firma` means the sender's employer.**
* Success ⇒ mail to `CONTACT_TO` (reply-to = sender), PRG redirect to
  `/#kontakt` with flash `contact_success`.
* **The e-mail address is validated twice**, so `Assert\Email` runs in `strict`
  mode: the loose default accepts addresses that `Mime\Address` then rejects
  with an `InvalidArgumentException`, which the transport catch does not cover
  and which would land as a bare error page.
* The legal mailbox is `mail+legal@marcelkraus.de`. A test asserts that no
  `mailto:` reaches the markup.
* **No downloadable references.** An Arbeitszeugnis carries the letterhead,
  function and signature of a named third party; publishing it publishes someone
  else's data, and once indexed it stays indexed. One sentence in the contact
  section says they are sent on request. No protected area, no signed links, no
  checkbox in the form.

## The printed curriculum vitae

`GET /lebenslauf` renders the document from the same content files the page
reads. **Not a second document** – a hand-kept PDF drifts against the site, and
then there are two truths about one career. `ContentRepository` makes that
structural: one reader, two outputs.

Delivered **inline**, so it opens in the browser; the file name travels along
for whoever saves it. `X-Robots-Tag: noindex`, because the file duplicates the
homepage and carries the postal address – the indexed truth stays the page. A
`Disallow` would be worse than useless: a path never fetched is a header never
read.

**Two pages is a requirement, not a preference**, and a test asserts it. The
layout sits close enough to the limit that one longer station description pushes
it over, and nothing else would notice.

### Page geometry, the measured stationery

| # | | |
| --- | --- | --- |
| 1 | Top, every page | 55 mm – 20 mm paper edge + 15 mm logo + 20 mm clearance |
| 2 | Left / right | 20 mm |
| 3 | Bottom | 20 mm – the stationery fixes only the top and the sides |
| 4 | First column | 30.5 mm, half the logo width (61.03 ÷ 2) |
| 5 | Text column | starts at 50.5 mm, in every section |
| 6 | Right column of the head | starts at 129 mm, under the left edge of the logo |

The lockup lives in the page header, which is what makes it repeat without the
body knowing about it.

### Two scales, and nothing outside them

**Type**, five steps: 24 pt title, 12 pt byline, 10 pt names, 9 pt running text
and contact values, 8 pt the technical layer. The fifth step earns its place –
with names and running text sharing 10 pt the document runs to three pages.

**Spacing**, four steps, all multiples of 1.5 mm: 0 inside an entry (the lines
sit flush, as on the page), 1.5 mm between contact rows, 3 mm between siblings,
9 mm between blocks. The one value outside both scales is 0.5 mm above the
period column – an optical nudge, not a distance between things.

### The head, and what it leaves out

The right column carries Datum, Anschrift, Telefon, Verfügbar, E-Mail and
`Portfolio & Profile`, then the QR code. That last label does real work: it says
the one address behind it is where the projects and the other profiles are,
which is why there is **no LinkedIn row and no second QR**. Two codes side by
side cannot be told apart without reading their labels, and a reader who scans
the wrong one blames the document. One code is a device; two are a pattern that
invites a third.

**No photograph.** The page carries the portrait one scan away, and a 4:5 image
at any usable size costs 40 to 60 mm of a first page that has 23 mm to spare.
Adding it means a third page or a shorter profile, and the profile is the only
part of the document that says in Marcel's own words who he is.

A quiet page number closes each page, centered in the bottom margin – the band
runs 277 to 297 mm, so the figure sits at 287 mm. Two pages do not need it to
find their way back together; a sheet that falls out of a folder does.

### What the document does differently from the page

* **No section numbers.** On the page they structure one long scroll; here there
  are four headings on two numbered pages, and the ordinal would collide – `02`
  is Kompetenzen on the page and Selbstständigkeit in the document.
* **No technology tags.** They are a device of the page.
* **No years in the competences.** The document is read in one sitting; the
  figures only crowd a line that is already dense.
* **Three blocks instead of one timeline** – Berufserfahrung, Selbstständigkeit,
  Ausbildung – grouped on the `marker` field, which needs no extra data.
* **A short profile** that exists nowhere on the page; the hero carries a
  tagline, which is a different thing. It lives in `config/services.yaml`
  because a content file would only be ignored by the page.

### mPDF has two rules that shape the whole template

1. **Every distance is cell padding or a spacer element.** Block margins inside
   a table cell are dropped, and an empty spacer renders only sometimes.
2. **Every area is an image.** Neither a background nor a border on a block
   inside a cell is drawn. The square before an eyebrow is `marker-accent.svg`;
   the rail beside the nested roles is a cell border.

`shrink_tables_to_fit` is **off**. It silently rescales individual tables mPDF
thinks too wide, which produces two different body sizes on one page. Every
column here has an explicit width, so nothing needs rescuing.

### Assets

`logo.svg`, `qr-code.svg` and `marker-accent.svg` under `public/images/` carry
literal hex, as `favicon.svg` does: mPDF parses its own stylesheet and never
sees the theme, so there is no token to bind to. The values are the ones the
tokens resolve to and have to move with them.

**The QR code has no quiet zone of its own** – the view box is cropped to the 25
data modules, because the white paper around it is the quiet zone and the
built-in border only pushes the ink off the column edge. It carries
`width`/`height`, for the same reason `favicon.svg` must.

**JetBrains Mono is embedded from `assets/fonts/`**, two static instances
derived from the variable web font – mPDF can read neither a variable font nor
Brotli. They are never served; `assets/README.md` carries the command that
rebuilds them.

## SEO / meta

Centralised in `base.html.twig`: canonical, Open Graph, Twitter card, sharing
image `public/images/sharing.jpg`. `Person` JSON-LD in the homepage
`structured_data` block, `sameAs` pointing at both sibling sites and LinkedIn.
Subpage title scheme: `{Page} · Marcel Kraus`. The homepage is the only
indexable page.

The sharing image was rendered from the real logo partial and the real fonts:
white, the eyebrow and the logo lockup on the left, the job title in the accent
below it, domain and location as a mono line at the bottom, and the three brand
squares oversized and cropped off the right edge – the same device krausgebaut
uses with its gear.

**Hero title and structured data are deliberately decoupled.** The visible hero
line is the personality tagline; the `jobTitle` field of the JSON-LD and the
page `<title>` keep the precise job description („Senior Software-Entwickler“).

## Deployment

Server directory `~/www/html/marcelkraus`, on the account `krswrk`, host
`nix`. Mechanism, deploy keys and the mailer are in `../../docs/DEPLOYMENT.md`,
including the rule that **`marcelkraus.de` must never be registered as a mail
domain on this account** – its MX points at `deimos` while the site runs on
`nix`.

| # | | |
| --- | --- | --- |
| 1 | Canonical host | `https://www.marcelkraus.de` |
| 2 | The other addresses | The bare apex, and `marcel-kraus.de` with and without `www`, answer 301 to it and carry the path along. The hyphenated spelling is what somebody types who has only heard the name; it is not a second site and never serves content of its own. The rules sit in `public/.htaccess`, host-scoped rather than as a catch-all, so ddev and any host not named there stay untouched |
| 3 | Elsewhere | Nothing. The measurement this site is counted in answers at `analytics.krausgebaut.de` – another domain on another account, named by the tracking code and by the privacy policy and run by neither this repository nor this host |

**Each of the four hostnames needs a web domain of its own on the server.**
A rule in `public/.htaccess` is never read for a host Apache does not serve –
without `uberspace web domain add`, the hyphenated spelling keeps showing
whatever the registrar parks and no redirect happens.

## Environment variables

| # | Variable | Description |
|---|----------|-------------|
| 1 | `APP_ENV` | Environment (`dev` / `prod`) |
| 2 | `APP_SECRET` | Symfony secret, also keys CSRF and the anti-spam timestamp |
| 3 | `MAILER_DSN` | Mail delivery |
| 4 | `CONTACT_TO` | Recipient of form submissions |
| 5 | `CONTACT_FROM` | Sender address of form mail |
| 6 | `DEFAULT_URI` | Base URL for absolute URLs generated in CLI (sitemap) |
| 7 | `APP_SHARE_DIR` | Symfony shared-state directory |

## Open points

None.
