# marcelkraus

## Overview

Personal website of Marcel Kraus (https://www.marcelkraus.de). It does two
jobs, in this order:

1. **A curriculum vitae for job applications.** Career, competences, contact.
   No projects, no references, no app listings — the portfolio lives on
   krausgebaut and, in part, krausgedruckt.
2. **The hub of the brand family.** The site carries the logo that holds all
   three brands and points at the two commercial ones.

German only, no internationalisation, one color scheme (light). One page plus
the two legal pages. Guiding rule for every case of doubt: **keep the site
simple.**

Directory, ddev project, composer package and the GitHub repository are all
called `marcelkraus` (`git@github.com:marcelkraus/marcelkraus`).

## Technology stack

Identical to krausgebaut — that identity is a requirement, not a coincidence.

- **Backend:** Symfony 8.1, PHP 8.4 (skeleton), Twig
- **Styling:** Tailwind CSS 4 (standalone CLI) with the typography plugin
- **Fonts:** self-hosted — Aller (display + body, static TTF), JetBrains Mono
  (mono / technical labels, variable woff2)
- **Form:** hand-rolled (no symfony/form) + symfony/validator +
  symfony/rate-limiter + CSRF
- **Content:** JSON files in `config/content/`, read with plain `json_decode`
- **Mail:** symfony/mailer; ddev Mailpit in development
- **Logging:** symfony/monolog-bundle (rotating file in prod)
- **Tests:** PHPUnit (`phpunit/phpunit`) with `symfony/browser-kit` and
  `symfony/css-selector`
- **Document:** mpdf/mpdf for the printed curriculum vitae
- **Development:** ddev (apache-fpm, Node 22)

**There is no database at all** — `omit_containers: [db]` in the ddev config,
so no container starts either. Four content files read with `json_decode` need
neither Doctrine nor a serializer, and doing without both is what makes the
technical base match the sibling exactly.

Deliberately **not** included: Doctrine, symfony/form, symfony/serializer,
symfony/translation, AssetMapper/Encore/Vite, Leaflet, a print stylesheet and a
custom error page.

- **mPDF is the one deliberate break with the sibling's stack.** The identity
  with krausgebaut is otherwise a requirement, and this dependency ends it —
  justified, because krausgebaut has no curriculum vitae to print. It is a
  production dependency: the document is generated per request, so it cannot
  fall out of step with the page.
- **No print stylesheet, still.** The document is generated, not printed from
  the browser: a stylesheet cannot hold a letterhead grid to the millimetre,
  and every browser decides its own margins and running heads. `/lebenslauf`
  is the printed version; the browser's own print output is not supported.
- **No custom error page**, so a 404 or a 405 renders the plain Symfony page.
  All three sites are in that state.
- **The typography plugin is loaded but unused** (no `prose` in the templates).
  It stays because both siblings carry it and the stack is meant to be
  identical — if it goes, it goes in all three at once.

## Development

```bash
ddev start                                    # https://marcelkraus.ddev.site
ddev launch -m                                # Mailpit, captured mail
ddev exec npm run build                       # Tailwind, minified
ddev exec npm run dev                         # Tailwind, watch mode
ddev exec php bin/console cache:clear
```

**Rebuild Tailwind after every change to a template or to `input.css`.**
`public/css/output.css` is committed and included via a static `<link>`, so an
un-rebuilt stylesheet ships silently broken. `input.css` sets
`@import "tailwindcss" source(none)` and declares the templates explicitly:
without that, Tailwind scans the whole tree and a stray word in a Markdown file
turns into a CSS rule.

**Never assemble a utility class from a variable.** Tailwind reads the
templates as text, so `bg-{{ token }}` never reaches the build. The career
timeline and the brand band therefore hold literal maps of class names
(`homepage.html.twig`).

### Quality gates (before merging to main)

```bash
ddev exec php bin/console lint:twig templates
ddev exec php bin/console lint:yaml config
ddev exec php bin/console lint:container
ddev exec bash -c 'find src tests -name "*.php" -exec php -l {} \;'
ddev exec php bin/phpunit
ddev exec npm run build
```

## Layout

```
config/content/     milestones.json, skills.json, hobbies.json, brands.json
src/Controller/     DefaultController – all routes, form handling, JSON loading
src/Dto/            ContactRequest – form DTO with validation constraints
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

Each partial is the single source for its pattern: `_logo` (brand lockup),
`_eyebrow` (mono label with square marker and optional section number),
`_icons` (line-icon macro), `_button_class` (the button skin as a bare class
string, in two sizes and two tones), `_contact_form`, `_tags` (the technology
tags of a career station, shared by a station and by the roles nested inside
one).

`_icons` keeps **no stock**: every name in it has a caller, and the names are
alphabetical. Most come from Heroicons v2 outline, `remote` and `wheel` are
drawn for this site — so a name missing from the Heroicons catalog is not
necessarily missing here.

## Routing

| Route | Name | Description |
|-------|------|-------------|
| `GET /` | `app_homepage` | The curriculum vitae, single page |
| `POST /kontakt` | `app_contact` | Form handling (PRG) |
| `GET /kontakt-per-email` | `app_contact_email` | Redirect to `mailto:` |
| `GET /kontakt-per-whats-app` | `app_contact_whats_app` | Redirect to WhatsApp |
| `GET /lebenslauf` | `app_curriculum_vitae_pdf` | The printed curriculum vitae, generated |
| `GET /impressum` | `app_imprint` | Imprint (`noindex,follow`) |
| `GET /datenschutz` | `app_data_privacy` | Privacy policy (`noindex,follow`) |
| `GET /robots.txt` | `app_robots` | robots (absolute sitemap URL) |
| `GET /sitemap.xml` | `app_sitemap` | Sitemap (the homepage) |

Route names carry no locale segment: the site is single-language.

## Design system

The light "spec-sheet" look of the family, with the curriculum-vitae additions
described below. No dark mode.

### Colors

The only chromatic color is the purple accent; everything else is Tailwind's
`neutral-*`, hairlines `neutral-200`. **No hex values in templates** — use the
tokens. All tokens bind to `var(--color-…)` rather than to copied hex values,
so the interface cannot drift from the palette.

**One vocabulary, all three brands.** The role is in the name, so the
difference between the sites falls into the values and not into the naming. The
same five tokens exist on krausgebaut and krausgedruckt under the same names.

| Token | Value | Role |
| --- | --- | --- |
| `accent` | `purple-700` | the brand: surfaces, borders, markers — **never type** |
| `accent-on-light` | `purple-700` | type on a light ground (7.07:1) |
| `accent-on-dark` | `purple-400` | type on a dark ground (6.42:1) |
| `accent-hover` | `purple-800` | hover of a filled surface |
| `accent-on-light-hover` | `purple-800` | hover of type on a light ground |

The purple is a **dark** color, like krausgebaut's petrol: it carries a light
ground as it is and misses AA as type on a dark one (2.54:1), where it needs
the brighter step. The hover of a filled surface always moves in whichever
direction keeps its label readable — here the label is white, so the fill
**darkens**.

The naming makes the rule checkable: **`text-accent` without a role suffix must
not appear anywhere.** The same split holds for the focus ring: on the footer
and on the brand band it takes `accent-on-dark`, because the accent measures
2.54:1 and 2.80:1 there and WCAG 1.4.11 asks 3:1 of an indicator.

**The gray ramp needs the same care as the accent and has no names for it.**
Measured against the grounds this site actually uses: `neutral-400` carries only
dark grounds (6.94:1 on `neutral-900`) and fails on white (2.58:1);
`neutral-500` passes on white (4.74:1) but not on `neutral-100` (4.35:1) and not
on the dark grounds (3.78:1 / 4.18:1); `neutral-600` carries every light ground
(7.81:1 / 7.17:1). So: **`neutral-600` for labels on light, `neutral-400` for
secondary text on dark, `neutral-500` only on white.**

The eyebrow separator is the one measured exception: it takes `neutral-500` on
both grounds (4.35:1 on `neutral-100`, 4.18:1 on the band). It is an
`aria-hidden` glyph and therefore answers to the 3:1 of WCAG 1.4.11 rather
than the 4.5:1 of body text — but it had the ramp the wrong way round before
(2.37:1 and 2.53:1), which is below even that.

The two sibling brands have their own tokens, so foreign colors stay out of
the accent scale: `brand-krausgebaut` (`cyan-800`),
`brand-krausgebaut-on-dark` (`cyan-600`) and `brand-krausgedruckt`
(`orange-600`). `brand-jurassic-jeep` (`red-700`) joins them for the career
marker of the vehicle rental — the trade has no square in the logo and no tile
in the band, only that marker. Its own red is `#C01D0C`; `red-700` is the
nearest palette step at a perceptual distance of 0.0155 in oklab, about three
times closer than `orange-700`, which is what keeps the token bound to the
palette instead of carrying a copied hex. A role step exists only where the base value fails as type, so
a missing step is information: the petrol measures 2.74:1 on the dark band and
needs the brighter step (5.47:1 on the band, 4.95:1 on the footer), the papaya
passes as it is (5.50:1 / 4.98:1).

### Type, shapes, container

- **Typography:** `font-display` = `font-sans` = Aller (wordmark, headlines and
  body, which ties the type to the logo); `font-mono` = JetBrains Mono for
  eyebrows, labels, years and technical data.
- **Body text is `neutral-600`; the legal pages are `neutral-700`.** The
  declaration on `<body>` said `neutral-700` while every page overrode it, so
  the stated base colour was the one colour the site did not use. It now names
  the normal case. The darker step stays where it belongs — the long reading
  texts of the imprint and the privacy policy, which set it explicitly.
- **The mono scale has three trackings and no fourth.** `0.2em` marks a
  heading role — the eyebrow and the footer's column headings. `0.15em` is
  every other run of capitals: navigation, buttons, tags, micro labels, the
  copyright line. Mixed-case mono carries **none**, because tracking is what
  makes capitals legible and it only loosens everything else. Sizes: `text-sm`
  in the mobile menu, `text-xs` in the normal case, `text-[0.6875rem]` for a
  micro label. The three mixed-case sizes are three roles — the milestone tag,
  the data line, the year in the timeline — and not one role written three
  ways.
- **Corners scale with the surface:** `rounded-[2px]` for the square markers,
  `rounded-md` for tags and badges, `rounded-lg` for buttons and fields,
  `rounded-xl` for cards and containers. A tag stands around 24 pixels tall,
  where an 8-pixel radius reads as a pill and fights the square marker; six
  pixels at that height is the shape a button has at twice the size.
- **Cards are free-standing:** `rounded-xl border border-neutral-200 bg-white`
  in a `gap-6` grid. Deliberately **no** hairline (`gap-px`) grids.
- **Container:** `max-w-6xl mx-auto px-6 lg:px-8`; legal pages `max-w-3xl`. The
  legal pages clear the fixed header themselves (`pt-36 lg:pt-40`) because there
  is no padding on `main` — the same numbers as on both sibling sites, so all
  three sets of legal pages sit at identical measurements.
- **Section rhythm:** light → `neutral-100` → light → `neutral-100` → dark. The
  one dark block is the closing brand band, where both siblings carry theirs.

### The family bracket

**Header and footer are binding, the body is not.** Position, measurements,
grid and behavior stay identical across all three sites; the content of the
lists does not. Verified at 1440 px against the live sibling: header grid
x=144 w=1152 h=80, logo x=176 w=160 h=36, navigation x=360, footer columns at
176 / 649 / 981.

What differs on purpose: this footer carries four contact rows, because
LinkedIn belongs on a curriculum vitae and not on a sales page. What
does not differ: behavior. The burger's icon swap and the outside-click
close were carried back to both siblings in the same pass rather than left
here, and the `=== false` spelling now holds in all three.

- **Header:** a three-column grid, not a flex row. The fixed first column
  (`lg:grid-cols-[10rem_1fr_auto]`) starts at `lg`, where the navigation is
  visible and the shared edge is actually perceived; below that the grid is
  `grid-cols-[auto_1fr_auto]`, because reserving 160 pixels for a logo that
  needs less pushed the actions off a 320 pixel screen, which WCAG 1.4.10
  forbids. In a flex row the navigation is squeezed between logo and actions and
  its starting edge follows the width of its own labels — which puts it in a
  different place than on the sibling sites. The contact button stays visible on
  a phone and shrinks to its icon there (`sr-only sm:not-sr-only`); its label is
  `Kontakt`, not the siblings' sales verb.
- **The mobile menu** closes on the burger, on Escape (focus returns to the
  burger), on a link, on a tap outside and when the viewport grows to desktop.
  The burger swaps to a cross while open — an `aria-label` alone leaves sighted
  users without a signal. The menu deliberately carries **no** contact button:
  the navigation already has that item, and with the icon button in the bar the
  word stood three times in one open panel.
- **The hero carries two buttons**, the document filled and Kontakt outlined
  beside it. `_button_class` grew a second tone for that rather than a second
  copy of the skin. The document opens in a new tab — the one case besides an
  external link where the site does that, because it is served inline and
  would otherwise replace the page; the accessible name announces it.
- **Four navigation items** — Werdegang, Kompetenzen, Leidenschaft, Kontakt.
  The closing band is not in the navigation, as on both siblings. **Label and
  eyebrow must match word for word**, otherwise a link sends the visitor to a
  section whose name they never see; a test pins it.

### The body

The body follows the logic of a curriculum vitae instead of the sibling's
section rhythm.

- **Section numbers** (`01` … `05`) sit in the eyebrow, in mono. That is the
  family's technical voice applied to a curriculum-vitae convention: it
  structures a long single page without a table of contents. **The number
  carries the accent, the label does not** — both siblings set the whole eyebrow
  in the accent. Here the number leads, so the structure reads before the label
  does. That is the one deliberate deviation of this partial from the family;
  contrast was not the reason (`accent-on-light` measures 6.48:1 on
  `neutral-100`).
- **The career is one single vertical list**, on every width, with a hairline
  rail and one square marker per station — the same marker shape as in the
  eyebrow, the footer lists and the brand band. The rail stops below the last
  station; the marker does not change color there. Every station is either the
  accent or the color of its brand, so the timeline says at a glance which line
  of the career a station belongs to. There is deliberately no carousel: the
  previous one showed *less* on the desktop than on the phone and kept hidden
  cards in the tab order.
- **Several roles at one employer are nested, not listed side by side.** Ten
  years at Chefkoch is the strongest single statement in the document, and two
  stations next to each other read as two jobs rather than as one development.
  The employer line carries the whole span, the roles sit indented below it on
  their own hairline. Only Chefkoch has this shape today.
- **The availability is computed, never written out.** `AvailabilityCalculator`
  derives the month from the notice period, so the statement in the hero cannot
  fall out of date between two edits. The word *freigestellt* deliberately
  appears nowhere: it answers a question the reader did not ask and raises one
  he did not have. A date answers the only question there is — when?
- **Competences carry a year where a year says something, and never a
  rating.** A year is checkable; self-awarded scales are an anti-pattern.
  Grouped by intensity, alphabetical inside each group. **The years do not have
  to match the career** and mostly do not — they say since when Marcel works
  with a thing, not since when an employer paid for it.
- **Voice:** first person singular ("ich"), and the visitor is addressed
  formally ("Sie") — the audience is a hiring company, and it matches
  krausgebaut.
- **The station texts follow one build, and it is checkable.** Responsibility
  and scope are nominal ("Mitverantwortlich für…", "Zunächst in einem
  Plattform-Team…"); what Marcel personally achieved is a full sentence in the
  first person ("Ich habe … begleitet", "Federführend habe ich …"); the trades
  are purely descriptive, because a business describes itself and does not
  claim an achievement. The perspective used to jump inside a single text —
  nominal, then "ich", then nominal again — which read as three drafts stitched
  together.
- **No selling on this site, not even in a trade's description.** "Ich mache
  die Ideen meiner Kunden greifbar" stood at krausgedruckt for a while and was
  the one sentence on the page that advertised. Sales copy belongs on
  krausgebaut; here a trade says what it does, in what process, and stops.
- **Positioning:** this is the curriculum vitae and the hub. Sales copy belongs
  on krausgebaut. **Jurassic Jeep sits in the career and nowhere else** — not in
  the brand band, not in the footer. A curriculum vitae that lists two of three
  registered trades invites the question what else is missing, and an omission
  weighs more than the thing omitted. The brand band stays at two cards because
  the mark is three squares for three brands; a fourth tile under it contradicts
  the logo, and the vehicle rental is a trade rather than a brand of the family.
  The passion section carries four cards, three of which link out. OwnYard and meetMyRC are
  the deliberate exception to "no projects here": they make a different
  statement than a portfolio tile — not "I built this for a client" but "for
  this problem I write the software myself". The Maker card points at
  krausgedruckt instead, which is a business and not a self-written tool, so
  the section carries both kinds of link and the brand band repeats that
  destination further down.

### Brand mark

`_logo.html.twig` is the three squares and the wordmark as **one lockup**,
generated from the master artwork with the wordmark converted to outlines, so
the logo carries no font dependency. A master that keeps the wordmark as
`<text>` cannot be used: it would fall back to a generic sans wherever Aller is
absent, and the mark is the one thing on the page that has to be exact.

The squares run left to right in the order the brands came into being: purple
(the employment career, this site), petrol (krausgebaut), papaya
(krausgedruckt). Colors come from `fill-*` classes; `mono: true` renders
everything in `fill-current`, which is what the footer uses.

Master artwork is **not** kept in the repository. Marcel supplies it on demand,
and every shipped asset is derived from it — the favicons and the sharing image
from the logo master, `portrait.jpg` as a 4:5 crop of a square original.

### Favicons

Three files at the web root, all derived from the master artwork — the three
brand squares on a white tile with a `neutral-300` hairline — one step darker
than the interface hairline, because at 16 px `neutral-200` disappears:

| File | Role |
|------|------|
| `favicon.svg` | primary — scales to any size a browser asks for |
| `favicon.ico` | 16 + 32 px; also answers the implicit `/favicon.ico` request browsers make without a `<link>` |
| `apple-touch-icon.png` | 180×180, iOS home screen |

The SVG **must** keep its `width`/`height` attributes. Without them it has no
intrinsic size, so the browser rasterises it into a default box and scales that
into the tab slot, which puts a pale rim around the tile. Every generated file
is checked for a fully opaque, single-color border before it ships.

The squares carry the verbatim master colors (`#8200DB`, `#005F78`,
`#F54900`), and those are exactly what the tokens resolve to. The hex values
of the family are defined in the workspace's `BRAND_COLOURS.md`, because all
three sit outside sRGB and no conversion is authoritative on its own. **Artwork and
palette agree to the digit, and they have to stay that way.** When the artwork
changes, the icon files are re-derived from the master rather than edited.

## Content

`config/content/*.json` is read by `DefaultController::loadContent()` (missing
or malformed → empty list). Editing content needs no code change.

### milestones.json

The rendered order is the file order. **Employment runs newest first; each of
the businesses sits directly below the employment it started under.**
krausgedruckt (11/2023) and Jurassic Jeep (05/2021) therefore follow the
Chefkoch station, and krausgebaut (01/2002) follows the ASB. Sorting purely by
date would push the current employment below a business that started in 2002,
and the first line of a curriculum vitae has to be the current job.

**Six stations, and the list is not a chronicle.** The community service and
the 2002 internship were both here and are gone from the page as well as from
the document: they are the two entries a reader skips, and the vocational
school already says where the career started. The gaps they leave — 07 to
10/2002 and 10/2003 to 07/2004 — are covered by krausgebaut, which runs
through the whole timeline from 01/2002.

**Every period is month-precise.** Month precision exists so that gaps are
visible, which means the dates have to be right rather than merely plausible:
krausgebaut starts with the date on the trade licence (01/2002, not the 05/2001
that older sources carry), and the ASB employment starts 10/2004, with the
freelance work that ran from 04/2004 named in the text instead of folded into
the dates.

Fields: `years` (the printed label, e.g. `05/2017 – 11/2026`), `company`,
`location`, and optionally `position`, `description`, `division`, `tags[]`,
`marker`, `url` and `roles[]`.

**`location` reads `Ort · Zusatz`**, the second part optional: `In Köln`,
`In Kerpen · Abschluss mit Fachhochschulreife`, `In Erftstadt · selbstständig`.
The place is always spelled `In <Ort>`. The field is
mono without `uppercase`, so it prints exactly as it stands in the file, and
the addition stays lowercase where German asks for it (`selbstständig`).

**It carries no duration.** Durations were tried here and removed: the period
already stands in the date column to the left, so a second figure only invites
the reader to check one against the other — and the two rarely agree, because
counting whole months and counting elapsed time give different answers. One
statement of the period, in one place.

- **`division`** is a second line under the company name, for an organizational
  unit such as `Bundesverband`.
- **`marker`** takes `krausgebaut`, `krausgedruckt`, `jurassic-jeep` or
  `secondary` and selects the station's color from the literal map in the
  template; without it the station takes the accent. The three trades carry
  their own color, the employments share the accent, and `secondary` sets a
  station back into `neutral-500` — today only the vocational school, so the
  accent belongs to the working life alone. `secondary` also greys the
  position line, which is the only place a marker value reaches beyond its
  square.
- **`neutral-500` is the floor for a set-back station, not `neutral-400`.**
  The calmer step measures 2.58:1 on this ground and misses the 3:1 that WCAG
  1.4.11 asks of an indicator; `neutral-500` measures 4.74:1 and therefore
  carries the marker and its line of type.
- **`url`** hangs the business homepage in the muted location line — label
  `Homepage` plus the `external` glyph, no underline, because the glyph is a
  shape rather than a color and satisfies WCAG 1.4.1 on its own. The label is
  identical on all three, so the `aria-label` names the business; otherwise a
  screen reader announces the same word three times without a subject.
- **`roles[]`** turns one entry into an employer with several roles, each with
  its own `years`, `position`, `description` and `tags[]`. The entry then omits
  its own `position`. Chefkoch is the only case; the alternative — giving every
  entry a `roles[]` — would spare the branch in the template and inflate six
  single-role entries instead.
- An entry **without `description`** renders as a condensed one-liner. That is
  how the vocational school is kept from taking as much room as twelve years
  at the ASB.
- **No employment entry carries an open end.** The only statement about
  availability lives in the hero and is computed there. The businesses are open
  by design (`seit 11/2023`, `seit 05/2021`, `seit 01/2002`).

### skills.json

Groups in order of intensity — the order *is* the statement — entries
alphabetical inside each group: `group`, `entries[]` with `name` and optional
`since`.

- **`Täglich`** carries what is in daily use, **`Regelmäßig`** everything else
  that is still worked with, **`Arbeitsweise`** the methods, **`Schwerpunkte`**
  what the other three cannot say.
- **Which group carries years is itself the statement.** `Täglich` carries
  them, because "in daily use since 2017" is a claim worth checking.
  `Regelmäßig` carries none: "since 2014, now and then" tells a reader nothing
  and only invites arithmetic. `Arbeitsweise` carries them for the dated
  methods. `Schwerpunkte` never — a focus is not something one has "since", and
  its entries are qualities and activities rather than technologies.
- Inside `Täglich`, a missing year means no year is claimed. That covers the
  operating systems, which are listed as a user rather than as a developer
  (`iOS` next to `Swift seit 2017` would otherwise read as years of unrecorded
  Apple development), and `Git`, where the year was a guess and a guess on a
  curriculum vitae is worth less than silence.
- Around twenty-five entries; tools are left out, methods stay. The years are
  Marcel's own figures.
- **A station tag is not a competence.** Tags say what a station worked with,
  the groups here say what Marcel can do today. `Drupal` is the proof: it sits
  on the PHP role and does not belong in this file, because the work ended in
  2017. Do not add a rule that pairs the two lists; it was tried on paper and
  it is wrong — and the reverse is just as true, a technology that is neither
  a competence nor a defining part of a station simply appears in neither
  list, which is why Kotlin Multiplatform is now absent from both.
- **A year that cannot be defended in an interview is worse than none.**
  `Objective-C seit 2017` claimed nine years of practice in a language Marcel
  never deliberately wrote, and `SwiftUI seit 2019` named the year the
  framework shipped rather than the year he started. The first entry is gone,
  the second says 2021.

### hobbies.json

`title`, `icon` (a name from `_icons`), `description`, `knowsAbout[]` (the
schema.org terms for the JSON-LD) and the optional trio `url`, `urlLabel`,
`urlNote`. Without the trio the card renders without its link block.

### brands.json

`name`, `title`, `since`, `description`, `url` and `colorToken`
(`krausgebaut` / `krausgedruckt`), which selects the marker classes from the
literal map in the template.

### Structured data

`knowsAbout` in the JSON-LD is **built from the content files**, not hardcoded:
the skills carry the professional terms, the hobbies the private ones. Sorted
and deduplicated, so reordering a content file does not change the output.

## Contact form

Hand-rolled, handled in `DefaultController::contact()` — the same shape as on
both sibling sites, so a submission takes one path through the family.

- Data → `App\Dto\ContactRequest`, validated with symfony/validator (German
  messages). Fields: name, e-mail, company, phone, message; required are name,
  e-mail and message. `Firma` here means the *sender's* employer. Errors keep
  the **first** violation per field, because the constraints are written in
  order of relevance.
- **One required-marker, not two:** the asterisk on the label is the single
  signal (`aria-required` alongside it, plus the `Pflichtfelder *` legend).
- **Spam protection without a captcha:** a hidden honeypot field (`website`)
  plus a signed, time-boxed timestamp (`ts`/`ts_sig`, HMAC over
  `%kernel.secret%`). Honeypot filled, a missing or tampered signature, or a
  submission under 3 s ⇒ silently dropped (fake success). A valid but expired
  (> 2 h) signature re-renders as a normal error asking the visitor to resend.
- **A transport failure never reaches the visitor as an error page.** `send()`
  is wrapped and answers a `TransportExceptionInterface` through the normal
  form-error path. The sendmail DSN has two documented ways of being wrong on
  the production host, and Apache replaces the Symfony error page with its own;
  without the catch the message is lost behind a bare 500, on the one form this
  site exists for.
- **Rate limiting:** `contact_form`, sliding window, 5/hour per IP. Injected as
  `RateLimiterFactoryInterface`, so a test can put a refusing factory in its
  place — which is how the refusal path is covered without spending counters.
  The test environment raises the limit to 1000, because the limiter state
  outlives a single run and would otherwise leak between cases.
- **The e-mail address is validated twice**, so `Assert\Email` runs in
  `strict` mode: the loose default accepts addresses that `Mime\Address`
  then rejects with an `InvalidArgumentException`, which the transport catch
  does not cover and which would land as the bare error page the point above
  rules out.
- **CSRF** enabled, token `contact`.
- Success ⇒ mail to `CONTACT_TO` (reply-to = sender), PRG redirect to
  `/#kontakt` with flash `contact_success`; the confirmation replaces the form
  rather than sitting above it. Errors ⇒ home re-renders with status 422,
  per-field errors, old input and the first invalid field name
  (`contact_focus`); inline JS focuses it.
- **The address never appears in the markup.** E-Mail and WhatsApp are offered
  as links, but both point at a route that answers with a redirect; the
  `mailto:` never reaches the page. The imprint and the privacy policy render
  `partials/_legal_mail_address.html.twig`: the plus-addressed mailbox
  `mail+legal@marcelkraus.de` in plain, unlinked text, followed by a decoy hidden
  with an inline style, so a harvester reading the markup carries off an
  address that does not exist. The plus tag appears on those two pages only –
  what arrives on it came from a legal page and can be filtered on its own.
  Do not reintroduce a `mailto:` written into the markup — a test asserts its absence.

**No downloadable references.** An Arbeitszeugnis carries the letterhead,
function and signature of a named third party; publishing it publishes someone
else's data, and once indexed it stays indexed. Instead one sentence in the
contact section says they are sent on request. No protected area, no signed
links, no checkbox in the form.

## The printed curriculum vitae

`GET /lebenslauf` renders the document from the same content files the page
reads. **Not a second document** — a hand-kept PDF drifts against the site, and
then there are two truths about one career. `ContentRepository` is what makes
that structural: one reader, two outputs.

Delivered **inline**, so it opens in the browser; the file name travels along
for whoever saves it. `X-Robots-Tag: noindex`, because the file duplicates the
homepage and carries the postal address — the indexed truth stays the page. A
`Disallow` would be worse than useless: a path never fetched is a header never
read.

**Two pages is a requirement, not a preference**, and a test asserts it. The
layout sits close enough to the limit that one longer station description
pushes it over, and nothing else would notice.

### The page geometry is the measured stationery

| | |
| --- | --- |
| Top, every page | 55 mm — 20 mm paper edge + 15 mm logo + 20 mm clearance |
| Left / right | 20 mm |
| Bottom | 20 mm — the stationery fixes only the top and the sides |
| First column | 30.5 mm, half the logo width (61.03 ÷ 2) |
| Text column | starts at 50.5 mm, in every section |
| Right column of the head | starts at 129 mm, under the left edge of the logo |

The lockup lives in the page header, which is what makes it repeat without the
body knowing about it.

### Two scales, and nothing outside them

**Type**, five steps: 24 pt title, 12 pt byline, 10 pt names, 9 pt running
text and the contact values, 8 pt the technical layer. The fifth step earns its place — names
and running text shared 10 pt for a while, and the document ran to three pages.

**Spacing**, four steps, all multiples of 1.5 mm: 0 inside an entry (the lines
sit flush, as on the page), 1.5 mm between contact rows, 3 mm between siblings,
9 mm between blocks. The one value outside both scales is 0.5 mm above the
period column — an optical nudge, not a distance between things.

### The head, and what it deliberately leaves out

The right column carries Datum, Anschrift, Telefon, Verfügbar, E-Mail and
`Portfolio & Profile`, then the QR code. That last label is doing real work:
it says the one address behind it is where the projects and the other profiles
are, which is why there is **no LinkedIn row and no second QR**. The homepage
links the profile anyway, and two codes side by side cannot be told apart
without reading their labels — a reader who scans the wrong one blames the
document. One code is a device; two are a pattern that invites a third.

**No photograph.** It was weighed and left out: the page carries the portrait
one scan away, and a 4:5 image at any usable size costs 40 to 60 mm of a first
page that has 23 mm to spare. Adding it means a third page or a shorter
profile, and the profile is the only part of the document that says in
Marcel's own words who he is.

A quiet page number closes each page, centred in the bottom margin — the band
runs 277 to 297 mm, so the figure sits at 287 mm. Two pages do not need it to
find their way back together; a sheet that falls out of a folder does.

### What the document does differently from the page, and why

- **No section numbers.** On the page they structure one long scroll with no
  table of contents. Here there are four headings on two numbered pages, and
  the ordinal would collide: `02` is Kompetenzen on the page and
  Selbstständigkeit in the document.
- **No technology tags.** They are a device of the page.
- **No years in the competences.** The document is read in one sitting; the
  figures only crowd a line that is already dense.
- **Three blocks instead of one timeline** — Berufserfahrung, Selbstständigkeit,
  Ausbildung — grouped on the `marker` field, which needs no extra data.
- **A short profile** that exists nowhere on the page: the hero carries a
  tagline, which is a different thing. It lives in `config/services.yaml`
  because a content file would only be ignored by the page.

### mPDF has two rules that shape the whole template

1. **Every distance is cell padding or a spacer element.** Block margins inside
   a table cell are dropped, and an empty spacer renders only sometimes.
2. **Every area is an image.** Neither a background nor a border on a block
   inside a cell is drawn. The square before an eyebrow is
   `marker-accent.svg`; the rail beside the nested roles is a cell border.

`shrink_tables_to_fit` is **off**. It silently rescaled individual tables that
mPDF thought too wide, which produced two different body sizes on one page —
the long stations came out smaller than the short ones. Every column here has
an explicit width, so nothing needs rescuing.

### Assets

`logo.svg`, `qr-code.svg` and `marker-accent.svg` under `public/images/` carry
literal hex, as `favicon.svg` does: mPDF parses its own stylesheet and never
sees the theme, so there is no token to bind to. The values are the ones the
tokens resolve to and have to move with them.

**The QR code has no quiet zone of its own** — the view box is cropped to the
25 data modules, because the white paper around it is the quiet zone and the
built-in border only pushed the ink off the column edge. It carries
`width`/`height`, for the same reason `favicon.svg` must: without an intrinsic
size the renderer invents a box.

**JetBrains Mono is embedded from `assets/fonts/`**, two static instances
derived from the variable web font — mPDF can read neither a variable font nor
Brotli. They are never served; `assets/README.md` carries the command that
rebuilds them.

## SEO / meta

Centralised in `base.html.twig`: canonical, Open Graph, Twitter card, sharing
image `public/images/sharing.jpg` (1200×630). `Person` JSON-LD in the homepage
`structured_data` block, with `sameAs` pointing at both sibling sites and
LinkedIn. Subpage title scheme: `{Page} · Marcel Kraus`. The homepage is the
only indexable page; the legal pages are `noindex`.

The sharing image is a finished asset, not a build product. It was rendered
from the real logo partial and the real fonts at 1200×630: white, the eyebrow
and the logo lockup on the left, the job title in the accent below it, domain
and location as a mono line at the bottom, and the three brand squares
oversized and cropped off the right edge — the same "texture off the right
edge" device krausgebaut uses with its gear.

**The card states nothing that expires.** It carried an availability badge
("VERFÜGBAR AB SOFORT") until the availability became a computed month; a
finished asset cannot follow a value that moves, so the card would have
contradicted the page it links to on every share. The badge is gone rather
than updated.

**The bottom line matches both siblings**: the domain without `www.`, set in
capitals like everything else in mono. It said `www.marcelkraus.de` in mixed
case for a while, which made this the only card of the three that spelled its
own address differently from the other two. The claim carries the ampersand
the title and the hero use; it had been spelled out here alone.

**Hero title and structured data are deliberately decoupled.** The visible hero
line is the personality tagline; the `jobTitle` field of the JSON-LD and the
page `<title>` keep the precise job description ("Senior Software-Entwickler").

## Analytics

Self-hosted Matomo (**SiteId 10**), inlined in `base.html.twig` behind
`{% if app.environment == 'prod' %}` — dev and test never track. Cookieless
(`disableCookies`) with the visitor IP anonymised server-side, so nothing is
stored on or read from the device and no consent banner is required. Covered by
section 5 of the privacy policy, and pinned by `AnalyticsTest`.

## Deployment

Production runs on **Uberspace 7** (`ssh kraus`) at https://www.marcelkraus.de,
alongside both sibling projects. The server directory is `~/html/marcelkraus`.

**A push to `main` rolls out by itself.** The workflow runs the gates first
and starts `bin/deploy` over SSH only if they pass, so nothing reaches the
server that has not been linted, tested and built. krausgebaut runs the same
job; krausgedruckt is the one still rolled out by hand, because it carries a
database and an unattended migration is the one step a rollback cannot undo.

The key GitHub authenticates with is restricted in the server's
`authorized_keys` to exactly one command — `cd ~/html/marcelkraus &&
bin/deploy` — with no terminal and no forwarding. That is what makes the
secret cheap to hold: it can redeploy the state already on `main`, and
nothing else. Withdraw it by deleting that line on the server; the entries
there are labelled so they can be told apart.

Three secrets carry it, all in the repository's Actions settings:
`DEPLOY_SSH_KEY`, `DEPLOY_HOST` and `DEPLOY_USER`.

By hand, unchanged and always available:

```bash
ssh kraus 'cd ~/html/marcelkraus && bin/deploy'
```

`bin/deploy` follows the sibling's script: fetch, hard reset to `origin/main`,
`composer install --no-dev`, move the compiled prod cache aside and rebuild
it. Ahead of all that it refuses to run without a usable `APP_SECRET` in
`.env.local` — the repository carries a placeholder, and a release that keeps
it ships a secret anybody can read, which makes both the CSRF token and the
anti-spam signature forgeable without breaking a single page.

The cache is moved rather than cleared, and that is not a
detail: `cache:clear` loads the existing compiled container before it replaces
it, so a release that drops a bundle dies on a class that no longer exists.
Moving beats `rm -rf`, which races PHP-FPM and fails with "Directory not empty"
at the worst possible moment.

`public/css/output.css` is committed, so the server needs no npm run. The
non-www redirect lives in `public/.htaccess`.

**Repository access** runs over a per-repository deploy key. Deploy keys are
bound to one repository and all of them live on `github.com`, so the host name
cannot tell them apart: `~/.ssh/config` gives the project an alias and the
remote URL names the alias. `IdentitiesOnly yes` matters — without it ssh
offers every key it finds, and a wrong one first burns the single
authentication attempt a deploy key allows.

**`.env.local`** (mode 600, never committed) holds `APP_ENV=prod`,
`APP_DEBUG=0`, a generated `APP_SECRET`, `CONTACT_TO`, `CONTACT_FROM`,
`DEFAULT_URI=https://www.marcelkraus.de` and the mailer DSN below.

**Mail** goes through the local MTA; the SPF record includes
`spf.uberspace.de`, so no SMTP credentials are needed. Two traps, both worked
around in the DSN below and both cost the sibling project a release:

- Plain `sendmail://default` calls `sendmail -bs`, which the qmail wrapper
  rejects with `421 unable to read controls`. The command has to be forced into
  pipe mode.
- The command **must be URL-encoded** inside the DSN. With literal spaces the
  query string is mangled and the mailer fails with `Unsupported sendmail
  command flags`. The contact form catches the transport exception, so the
  visitor gets a readable message — but the mail is still lost, so the DSN has
  to be right.

```
MAILER_DSN="sendmail://default?command=%2Fusr%2Fsbin%2Fsendmail%20-t%20-i"
```

**Never add marcelkraus.de as a mail domain on this host.** This is the one
site whose website and mailbox sit on different Uberspace accounts: the site
runs on `menkar`, while the MX still points at `deimos`. Sending works anyway,
because the SPF record authorises every Uberspace host — but the moment the
domain is registered for mail *here*, qmail treats it as local and drops
`mail@marcelkraus.de` into a mailbox on `menkar` that nobody reads, instead of
handing it to the MX. The contact form would then answer with its confirmation
page while every enquiry quietly piles up on the wrong machine.

All three sites use this transport, so the family delivers mail through one
path. An SMTP transport pointed at the other account is the wrong shape here:
it ties the form to credentials and to a machine this site does not own.

**Headers.** Uberspace adds `Strict-Transport-Security` and forces
`X-Frame-Options: SAMEORIGIN`, overriding the `DENY` that
`SecurityHeadersListener` sets. The server has the last word.

## Security headers

`App\EventListener\SecurityHeadersListener` sets `X-Content-Type-Options:
nosniff`, `Referrer-Policy: strict-origin-when-cross-origin` and
`X-Frame-Options: DENY` on every main response. No CSP — all JS is inline and
would need nonces.

## Code conventions

- **Comments, identifiers and this documentation are English.** Visible site
  content is German, with correct German quotation marks „…“.
- **Identifiers are language-neutral and stable.** They follow the domain, not
  the display: `hobbies.json` and the icon name `wrench` stay put even though
  the section is called „Leidenschaft“.
- **No hex color values in templates** — use the design tokens. Standalone
  asset files such as `favicon.svg` may carry hex.
- **The hex values themselves are fixed in the workspace `CLAUDE.md`**, not
  derived here. The brand colors sit outside sRGB, so conversions disagree by
  a step. Look them up, do not recompute them.

## Environment variables

| Variable | Description |
|----------|-------------|
| `APP_ENV` | Environment (`dev` / `prod`) |
| `APP_SECRET` | Symfony secret (also keys CSRF and the anti-spam timestamp) |
| `MAILER_DSN` | Mail delivery (dev: ddev Mailpit via `.env.local`) |
| `CONTACT_TO` | Recipient of form submissions |
| `CONTACT_FROM` | Sender address of form mail |
| `DEFAULT_URI` | Base URL for absolute URLs generated in CLI (sitemap) |
| `APP_SHARE_DIR` | Symfony shared-state directory |

## Open points

None.
