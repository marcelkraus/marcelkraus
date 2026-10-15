# marcelkraus

## Overview

Personal website of Marcel Kraus (https://www.marcelkraus.de). It does two jobs, each on a page of its own:

1. **A curriculum vitae for job applications**—Profil, at `/`. Career, competences, passion. No client work and no references—those live on krausgebaut.
2. **The hub of the brand family**—Portfolio, at `/portfolio`. krausgebaut and krausgedruckt as two towers, everything else Marcel runs in one card below them.

Kontakt, at `/kontakt`, carries the one contact form. German only, one color scheme (light). Three pages plus the two legal pages. Guiding rule for every case of doubt: **keep the site simple.**

## Stack notes

* **No print stylesheet.** The document is generated, not printed from the browser: a stylesheet cannot hold a letterhead grid to the millimeter, and every browser decides its own margins and running heads. `/lebenslauf` is the printed version; the browser's own print output is not supported.
* **The typography plugin is loaded but unused** (no `prose` in the templates). It stays because both siblings carry it and the stack is meant to be identical—if it goes, it goes in all three at once.

## Partials

Every link with an arrow goes through `_link.html.twig`, so `↗`, `→` and `↓` mean the same on every page. A career station's marker color and a tower's colors are the stylesheet's, keyed on a `data-marker` or `data-brand` attribute rather than on a class assembled in the template.

## Design

**marcelkraus stands in front of the family, not inside it**, with a design system of its own; what it still shares with the two siblings is in `../docs/BRAND_FAMILY.md`. No dark mode.

* **Tokens and scales live in the Tailwind theme, the layout measures in `:root`; the recurring parts are component classes**—frame, card, cells, cover card, tabs, tower, work, button, link—used by the partials. A utility is for the single case. No `!important`.
* **Nothing outside the scales.** The frame is 1104 px on twelve columns of 48 with a gutter of 48; spacing runs on 8, 16, 24, 48 and 96 only; type on four steps of Aller and two of mono. The spacing scale binds distances, not the size of a thing—a portrait, the logo, a field. The one type size outside the scales is a tower's name, which shrinks with its tower below the full frame; the two optical values outside them are the station marker, centered on its hairline, and the button's 1.5 px border, a step heavier than a card's hairline. **One breakpoint**, at 1000 px, where the page margin, the gutter and the padding drop from 48 to 24 and the four Aller steps shrink.
* **One section is one card**—white, a 1 px hairline, never a card inside a card. The ground is the facade of hairlines, drawn by the stylesheet without a script. **Nothing moves**: not the facade, the cards, the towers or the arrows.
* **On the page, a square marker stands only on a career station**—not before a heading, not in the tabs, not before the availability.
* **The tabs are real tabs** on the lower edge of the header, the active one white and open into the card below. The list is `app.pages` in `config/services.yaml`, which the sitemap reads too; the active tab follows the path rather than the route, so the contact page re-rendered by a failed POST keeps its tab, and a legal page matches none. On a phone the tabs run at full width under the logo, 48 px high. The header does not stick.
* **The cover card is the same person card on all three pages**—portrait in black and white, name, job title, claim. On Portfolio “Nachricht schreiben →” stands on the right behind a hairline; on Kontakt there is no button, the form being the page; on Profil the name's column carries the introduction instead—lead, key facts, both buttons. No location, no founding year, no further channels. **The availability stands on Profil alone.**
* **“Lebenslauf als PDF ↗” stands once on Portfolio**, in the closing cell, and twice on Profil—in the cover card and in the closing card; Kontakt carries none. It opens in a new tab—the one case besides another website—because the document is served inline and would otherwise replace the page. `.button` and `.button-solid` are the two tones of one component.
* **The footer carries the tridents and one line**—copyright and the legal links. No contact rows: the channels stand beside the form on `/kontakt` and nowhere else; LinkedIn is among them because it belongs on a curriculum vitae and not on a sales page.
* **The legal pages carry the same header with no tab active**, no cover card, and their text in one card.

## The pages

### Profil

Five cards: the cover card with the introduction, Werdegang, Kompetenzen, Leidenschaft, and a narrow closing card with the document and the message once more. No section numbers, no section navigation, no contact card.

* **The career is one single vertical list**, at every width, with a hairline beside each station and one square marker on it. The three trades carry the color of their brand, every other station `station` (`neutral-700`), so the timeline says at a glance which stations are the self-employment. **No carousel:** it shows less on a desktop than on a phone and keeps hidden cards in the tab order.
* **Several roles at one employer are nested, not listed side by side.** Ten years at Chefkoch is the strongest single statement in the document, and two stations next to each other read as two jobs rather than one development. The employer line carries the whole span, the roles stand below it. Chefkoch is the only case.
* **The availability names one month.** “Ab Dezember” stands as a literal in the page and in the printed document. **No year:** the document carries its own date three rows above the line, and the page is read the day it is served. The word *freigestellt* deliberately appears nowhere: it answers a question the reader did not ask and raises one they did not have. A date answers the only question there is—when?
* **Competences carry a year where a year says something, and never a rating.** A year is checkable; self-awarded scales are an anti-pattern.
* **Voice:** first person singular (“ich”), the visitor addressed formally (“Sie”)—the audience is a hiring company, and it matches krausgebaut.
* **The station texts follow one build.** Responsibility and scope are nominal (“Mitverantwortlich für…”); what Marcel personally achieved is a full sentence in the first person (“Ich habe … begleitet”); the trades are purely descriptive, because a business describes itself and does not claim an achievement.
* **No selling on this site, not even in a trade's description.** A trade says what it does, in what process, and stops. Sales copy belongs on krausgebaut.
* **Jurassic Jeep sits in the career**, with the two other trades. A curriculum vitae listing two of three registered trades invites the question what else is missing, and an omission weighs more than the thing omitted.
* **The passion section carries four cells**, two by two, three of which link out under “Daraus wurde”, the link carrying the name alone. Two of them point at a self-written tool: not “I built this for a client” but “for this problem I write the software myself.” The Maker cell points at krausgedruckt, a business, so the section carries both kinds of link. The destinations live in `hobbies.json`.

### Portfolio

The two businesses as towers, then one card, “Außerdem von mir.” No introductory line.

* **Two square towers**, krausgedruckt on the right and higher, krausgebaut 144 px lower. The markup carries them in reverse brand order, so the higher one comes first for a keyboard and a screen reader. On a phone krausgedruckt stands first, both 88 % wide and still offset.
* **A tower carries its name, its claim and its domain**, and no header line. The domain is the button (“krausgebaut.de ↗”), and the button stretches over the whole tower, so the tower is the link. Type stays white on either brand.
* **The mark is the favicon glyph as a tone-on-tone wallpaper**, in the brand's deep step. Both glyphs share one outline, so one rule places either: 60 % of the tile high (34 % on a phone), a sixth out at the top and at the right. The two files are copied into `assets/marks/`; a project reads nothing from its siblings (`assets/README.md`).
* **“Außerdem von mir” is a 3 × 2 grid of cells**: the five works and a closing cell, a list on a phone. A work carries a framed screenshot, its name, its kind, one sentence and a link naming its destination without “Zu”; the link stretches over the cell. No color stripe, no categories.
* **The closing cell carries name, postal address and the document.** Contact lives in the cover card alone.

### Kontakt

The cover card without a button, then one card: the channels on the left—address, e-mail, LinkedIn, WhatsApp, response time—and the form on the right.

## Brand mark and favicons

`_logo.html.twig` is the three squares and the wordmark as one lockup, the squares running left to right in the order the brands came into being. Colors come from `fill-*` classes.

The favicons are the three brand squares on a white tile with a `neutral-300` hairline, because at 16 px `neutral-200` disappears. The squares carry the verbatim master colors, which are exactly what the tokens resolve to; the values are in `../docs/BRAND_FAMILY.md`.

**`tridents-hanging.svg` and `tridents-standing.svg` carry the hex of the `hair` token as a literal**, as `favicon.svg` carries the brand colors: a background image never sees the theme. They move with the token.

`portrait.jpg` is a 4:5 crop of a square original.

## Content

`config/content/*.json` is read by `ContentRepository` (missing or malformed → empty list), one reader for the page and the printed document. Editing content needs no code change.

### milestones.json

The rendered order is the file order. **Employment runs newest first; each business sits directly below the employment it started under.** krausgedruckt (11/2023) and Jurassic Jeep (05/2021) follow the Chefkoch station, krausgebaut (01/2002) follows the ASB. Sorting purely by date would push the current employment below a business that started in 2002, and the first line of a curriculum vitae has to be the current job.

**Six stations, and the list is not a chronicle.** Entries a reader skips are left out; the vocational school already says where the career started. The gaps that leaves—07 to 10/2002 and 10/2003 to 07/2004—are covered by krausgebaut, which runs through the whole timeline from 01/2002.

**Every period is month-precise**, which exists so that gaps are visible—so the dates have to be right rather than merely plausible. krausgebaut starts on the date of the trade license, 01/2002. The ASB employment starts 10/2004, with the freelance work from 04/2004 named in the text rather than folded into the dates.

**`location` reads `Ort · Zusatz`**, the second part optional: `In Köln`, `In Kerpen · Abschluss mit Fachhochschulreife`, `In Erftstadt · selbstständig`. The place is always spelled `In <Ort>`. The field is mono without `uppercase`, so it prints exactly as it stands and the addition stays lowercase where German asks for it.

**It carries no duration.** The period already stands in the date column to the left; a second figure only invites the reader to check one against the other, and the two rarely agree because counting whole months and counting elapsed time give different answers.

* **`division`** is a second line under the company name, for a unit such as `Bundesverband`.
* **`marker`** takes `krausgebaut`, `krausgedruckt`, `jurassic-jeep` or `secondary`. On the page the stylesheet reads it as `data-marker`: a trade's value selects its brand color, and every other station takes the gray; the printed document groups its three blocks on the field, `secondary` being Ausbildung.
* **`url`** hangs the business homepage in the muted location line—label `Homepage` with `↗`. The label is identical on all three, so a screen reader hears the business named after it; otherwise it announces the same word three times without a subject.
* **`roles[]`** turns one entry into an employer with several roles, each with its own `years`, `position`, `description` and `tags[]`; the entry then omits its own `position`. Giving every entry a `roles[]` would spare the branch in the template and inflate six single-role entries instead.
* An entry **without `description`** renders as a condensed one-liner, which is what keeps the vocational school from taking as much room as twelve years at the ASB.
* **No employment entry carries an open end.** The only statement about availability lives in the cover card on Profil. The businesses are open by design (`seit 11/2023`, `seit 05/2021`, `seit 01/2002`).

### skills.json

Groups in order of intensity—the order *is* the statement—entries alphabetical inside each group: `group`, `entries[]` with `name` and optional `since`.

* **`Täglich`** carries what is in daily use, **`Regelmäßig`** everything else still worked with, **`Arbeitsweise`** the methods, **`Schwerpunkte`** what the other three cannot say.
* **Which group carries years is itself the statement.** `Täglich` carries them, because “in daily use since 2017” is a claim worth checking. `Regelmäßig` carries none: “since 2014, now and then” tells a reader nothing and only invites arithmetic. `Arbeitsweise` carries them for the dated methods. `Schwerpunkte` never—a focus is not something one has “since,” and its entries are qualities and activities rather than technologies.
* Inside `Täglich`, a missing year means no year is claimed. That covers the operating systems, listed as a user rather than as a developer (`iOS` next to `Swift seit 2017` would otherwise read as years of unrecorded Apple development), and `Git`, where a guess is worth less than silence.
* Around twenty-five entries; tools are left out, methods stay. The years are Marcel's own figures.
* **A station tag is not a competence.** Tags say what a station worked with, the groups here say what Marcel can do today, so the two lists stay independent. A technology that is neither a competence nor a defining part of a station appears in neither list.
* **A year that cannot be defended in an interview is worse than none.** No year names when a framework shipped; it names when Marcel started with it.

### brands.json

The two towers on Portfolio, in the order the brands came into being: `name`, `claim`, `url`, `domain` (the label of the button), `mark` (a file in `assets/marks/`) and `colorToken`, which the tower hands to the stylesheet as `data-brand` and which selects the brand tokens there.

### works.json

The cells of “Außerdem von mir,” in the rendered order: `name`, `kind`, `description` (one sentence), `url`, `urlLabel` and `image`.

* **`urlLabel` names the destination**—the domain, or `App Store`—never an invitation such as “Zu …”.
* **`image` is a file under `public/images/works/`**, a square screenshot of 640 × 640. The screenshots are made once and committed; the page never fetches anything from a work at runtime, and a test asserts it.

### Structured data

`knowsAbout` in the JSON-LD is **built from the content files**, not hardcoded: the skills carry the professional terms, the hobbies the private ones. Sorted and deduplicated, so reordering a content file does not change the output.

## Contact form

The mechanism is in `../../docs/WEB_STACK.md`. Specific here:

* Fields: name, e-mail, company, phone, message; required are name, e-mail and message. **`Firma` means the sender's employer.**
* Success ⇒ mail to `CONTACT_TO` (reply-to = sender), PRG redirect to `/kontakt#kontakt` with flash `contact_success`; the confirmation replaces the form. An error renders the contact page with the input kept and status 422.
* **Exactly one contact form on the site**, on `/kontakt`, reached through the tab and through “Nachricht schreiben →.”
* **The e-mail address is validated twice**, so `Assert\Email` runs in `strict` mode: the loose default accepts addresses that `Mime\Address` then rejects with an `InvalidArgumentException`, which the transport catch does not cover and which would land as a bare error page.
* The legal mailbox is `mail+legal@marcelkraus.de`. A test asserts that no `mailto:` reaches the markup.
* **No downloadable references.** An Arbeitszeugnis carries the letterhead, function and signature of a named third party; publishing it publishes someone else's data, and once indexed it stays indexed. One sentence on the contact page says they are sent on request. No protected area, no signed links, no checkbox in the form.

## The printed curriculum vitae

`GET /lebenslauf` renders the document from the same content files the page reads. **Not a second document**—a hand-kept PDF drifts against the site, and then there are two truths about one career. `ContentRepository` makes that structural: one reader, two outputs.

Delivered **inline**, so it opens in the browser; the file name travels along for whoever saves it. `X-Robots-Tag: noindex`, because the file duplicates Profil and carries the postal address—the indexed truth stays the page. A `Disallow` would be worse than useless: a path never fetched is a header never read.

**Two pages is a requirement, not a preference**, and a test asserts it. The layout sits close enough to the limit that one longer station description pushes it over, and nothing else would notice.

**Merge with `pdfunite`, never with Preview.** An application is the cover letter plus this document in one file. Preview rewrites the embedded fonts when it saves and loses the character map of the mPDF subsets: the pages still look right and print right, but the text extracts as garbage, so search, copying the address and an applicant tracking system all fail. Dragging the PDF into Pages is worse still—Pages fits the page into its text column and scales it to 93 %. `pdfunite anschreiben.pdf lebenslauf.pdf bewerbung.pdf` keeps the fonts, the text and the logo position untouched.

### Page geometry, the measured stationery

| # | | |
| --- | --- | --- |
| 1 | Top, every page | 55 mm—20 mm paper edge + 15 mm logo + 20 mm clearance |
| 2 | Left / right | 20 mm |
| 3 | Bottom | 20 mm—the stationery fixes only the top and the sides |
| 4 | First column | 30.5 mm, half the logo width (61.03 ÷ 2) |
| 5 | Text column | starts at 50.5 mm, in every section |
| 6 | Right column of the head | starts at 129 mm, under the left edge of the logo |

The lockup lives in the page header, which is what makes it repeat without the body knowing about it.

### Two scales, and nothing outside them

**Type**, five steps: 24 pt title, 12 pt byline, 10 pt names, 9 pt running text and contact values, 8 pt the technical layer. The fifth step earns its place—with names and running text sharing 10 pt the document runs to three pages.

**Spacing**, four steps, all multiples of 1.5 mm: 0 inside an entry (the lines sit flush, as on the page), 1.5 mm between contact rows, 3 mm between siblings, 9 mm between blocks. The one value outside both scales is 0.5 mm above the period column—an optical nudge, not a distance between things.

### The head, and what it leaves out

The right column carries Datum, Anschrift, Telefon, Verfügbar, E-Mail and `Homepage`, then the QR code. The label names the page and not one of its tabs, since the tabs may trade places; the one address behind it is where the portfolio and the other profiles are, which is why there is **no LinkedIn row and no second QR**. Two codes side by side cannot be told apart without reading their labels, and a reader who scans the wrong one blames the document. One code is a device; two are a pattern that invites a third.

**No photograph.** The page carries the portrait one scan away, and a 4:5 image at any usable size costs 40 to 60 mm of a first page that has 23 mm to spare. Adding it means a third page or a shorter profile, and the profile is the only part of the document that says in Marcel's own words who he is.

A quiet page number closes each page, centered in the bottom margin—the band runs 277 to 297 mm, so the figure sits at 287 mm. Two pages do not need it to find their way back together; a sheet that falls out of a folder does.

### What the document does differently from the page

* **No section numbers**, as on the page.
* **No color but the logo's.** The byline, the positions and the square before a section are the document's ink, `neutral-900` (`#171717`) like the title and the company names—not the hub's `--color-ink`, since a second near-black a few shades apart would be noise on paper. The purple stays in the logo alone, and a test asserts it; no station carries a brand color.
* **No technology tags.** They are a device of the page.
* **No years in the competences.** The document is read in one sitting; the figures only crowd a line that is already dense.
* **Three blocks instead of one timeline**—Berufserfahrung, Selbstständigkeit, Ausbildung—grouped on the `marker` field, which needs no extra data.
* **A short profile** that exists nowhere on the page; the cover card carries the claim, which is a different thing. It lives in `config/services.yaml` because a content file would only be ignored by the page.

### mPDF has two rules that shape the whole template

1. **Every distance is cell padding or a spacer element.** Block margins inside a table cell are dropped, and an empty spacer renders only sometimes.
2. **Every area is an image.** Neither a background nor a border on a block inside a cell is drawn. The square before an eyebrow is `marker-ink.svg`; the rail beside the nested roles is a cell border.

`shrink_tables_to_fit` is **off**. It silently rescales individual tables mPDF thinks too wide, which produces two different body sizes on one page. Every column here has an explicit width, so nothing needs rescuing.

### Assets

`logo.svg`, `qr-code.svg` and `marker-ink.svg` under `public/images/` carry literal hex, as `favicon.svg` does: mPDF parses its own stylesheet and never sees the theme, so there is no token to bind to. The values are the ones the tokens resolve to and have to move with them.

**The QR code has no quiet zone of its own**—the view box is cropped to the 25 data modules, because the white paper around it is the quiet zone and the built-in border only pushes the ink off the column edge. It carries `width`/`height`, for the same reason `favicon.svg` must.

**JetBrains Mono is embedded from `assets/fonts/`**, two static instances derived from the variable web font—mPDF can read neither a variable font nor Brotli. They are never served; `assets/README.md` carries the command that rebuilds them.

## SEO / meta

Centralized in `base.html.twig`: canonical, Open Graph, Twitter card, sharing image `public/images/sharing.jpg`. `Person` JSON-LD in Profil's `structured_data` block, `sameAs` pointing at both sibling sites and LinkedIn. Profil's title: `Marcel Kraus · Senior Softwareentwickler iOS/Web aus Erftstadt bei Köln`; every other page: `{Page} · Marcel Kraus`. **Profil, Portfolio and Kontakt are indexable** and make up the sitemap, which reads `app.pages` like the tabs; the legal pages are `noindex,follow` and stay out of it.

The sharing image is one card on the facade: the portrait in black and white at full card height on the left; name, job title and the claim on two lines on the right; `www.marcelkraus.de` alone at the foot. **No logo and no place**, and nothing that expires. `bin/render-sharing-image` photographs it from `/sharing-image`, a page registered in dev alone that embeds the real cover card in the real stylesheet and fonts, so a change to the card reaches the image by running the script again. The file is a finished asset and gets committed.

**The job title has two forms.** “Senior Softwareentwickler iOS/Web” stands under the name in the cover card, in the page `<title>`, in the descriptions and on the sharing image; the `jobTitle` of the JSON-LD and the profile of the printed document carry “Senior Softwareentwickler” alone. The claim (“Nicht nur mit Hut. Mit Köpfchen.”) stands below the job title, broken after its first sentence on the page, on the sharing image and in the printed document.

## Deployment

Server directory `~/www/html/marcelkraus`, on the account `krswrk`, host `nix`. Mechanism, deploy keys and the mailer are in `../../docs/DEPLOYMENT.md`, including the rule that **`marcelkraus.de` must never be registered as a mail domain on this account**—its MX points at `deimos` while the site runs on `nix`.

| # | | |
| --- | --- | --- |
| 1 | Canonical host | `https://www.marcelkraus.de` |
| 2 | The other addresses | The bare apex, and `marcel-kraus.de` with and without `www`, answer 301 to it and carry the path along. The hyphenated spelling is what somebody types who has only heard the name; it is not a second site and never serves content of its own. The rules sit in `public/.htaccess`, host-scoped rather than as a catch-all, so ddev and any host not named there stay untouched |
| 3 | Elsewhere | Nothing. The measurement this site is counted in answers at `analytics.krausgebaut.de`—another domain on another account, named by the tracking code and by the privacy policy and run by neither this repository nor this host |

**Each of the four hostnames needs a web domain of its own on the server.** A rule in `public/.htaccess` is never read for a host Apache does not serve—without `uberspace web domain add`, the hyphenated spelling keeps showing whatever the registrar parks and no redirect happens.

## Agent skills

### Issue tracker

Issues and specs live as local markdown files under `.scratch/`. See `docs/agents/issue-tracker.md`.

### Triage labels

The five canonical roles, each label equal to its name. See `docs/agents/triage-labels.md`.

### Domain docs

Single-context: one `CONTEXT.md` and `docs/adr/` at the repository root. See `docs/agents/domain.md`.
