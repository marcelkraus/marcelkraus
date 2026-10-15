# Design

The pages, their parts and the site's look.

## Partials

Every link with an arrow goes through `_link.html.twig`, so `↗`, `→` and `↓` mean the same on every page. A link inside a mono line keeps the line's weight. A career station's marker color and a tower's colors are the stylesheet's, keyed on a `data-marker` or `data-brand` attribute rather than on a class assembled in the template.

## The design system

**marcelkraus stands in front of the family, not inside it**, with a design system of its own; what it still shares with the two siblings is in `../../docs/BRAND_FAMILY.md`. No dark mode.

* **Tokens and scales live in the Tailwind theme, the layout measures in `:root`; the recurring parts are component classes**—frame, card, cells, cover card, tabs, tower, work, button, link—used by the partials. A utility is for the single case. No `!important`.
* **Nothing outside the scales.** The frame is 1104 px on twelve columns of 48 with a gutter of 48; spacing runs on 8, 16, 24, 48 and 96 only; type on four steps of Aller and two of mono. The spacing scale binds distances, not the size of a thing—a portrait, the logo, a field. The one type size outside the scales is a tower's name, which shrinks with its tower below the full frame; the two optical values outside them are the station marker, centered on its hairline, and the button's 1.5 px border, a step heavier than a card's hairline. **One breakpoint**, at 1000 px, where the page margin, the gutter and the padding drop from 48 to 24 and the four Aller steps shrink.
* **One section is one card**—white, a 1 px hairline, never a card inside a card. The ground is the facade of hairlines, drawn by the stylesheet without a script. **Nothing moves**: not the facade, the cards, the towers or the arrows.
* **On the page, a square marker stands only on a career station**—not before a heading, not in the tabs, not before the availability.
* **The tabs are real tabs** on the lower edge of the header, the active one white and open into the card below. The list is `app.pages` in `config/services.yaml`, which the sitemap reads too; the active tab follows the path rather than the route, so the contact page re-rendered by a failed POST keeps its tab, and a legal page matches none. On a phone the tabs run at full width under the logo, 48 px high. The header does not stick.
* **The cover card is the same person card on all three pages**—portrait in black and white—by the stylesheet, never by editing the photograph, and never mirrored—name, job title, claim. On Portfolio “Nachricht schreiben →” stands on the right behind a hairline; on Kontakt there is no button, the form being the page; on Profil the name's column carries the introduction instead—lead, key facts, both buttons. No location, no founding year, no further channels. **The availability stands on Profil alone.**
* **“Lebenslauf als PDF ↗” stands once on Portfolio**, in the closing cell, and twice on Profil—in the cover card and in the closing card; Kontakt carries none. It opens in a new tab—the one case besides another website—because the document is served inline and would otherwise replace the page. `.button` and `.button-solid` are the two tones of one component.
* **The footer carries the tridents and one line**—copyright and the legal links. No contact rows: the channels stand beside the form on `/kontakt` and nowhere else; LinkedIn is among them because it belongs on a curriculum vitae and not on a sales page.
* **The legal pages carry the same header with no tab active**, no cover card, and their text in one card. Both open with `_legal_head.html.twig`, and their first block sits 48 px below it.

## The pages

### Profil

Five cards: the cover card with the introduction, Werdegang, Kompetenzen, Leidenschaft, and a narrow closing card with the document and the message once more. No section numbers, no section navigation, no contact card.

* **The career is one single vertical list**, at every width, with a hairline beside each station and one square marker on it. The three trades carry the color of their brand, every other station `station` (`neutral-700`), so the timeline says at a glance which stations are the self-employment. **No carousel:** it shows less on a desktop than on a phone and keeps hidden cards in the tab order.
* **Several roles at one employer are nested, not listed side by side.** Ten years at Chefkoch is the strongest single statement in the document, and two stations next to each other read as two jobs rather than one development. The employer line carries the whole span, the roles stand below it. Chefkoch is the only case.
* **The availability names one month.** “Ab Dezember” stands as a literal in the page and in the printed document. **No year:** the document carries its own date three rows above the line, and the page is read the day it is served. The word *freigestellt* deliberately appears nowhere: it answers a question the reader did not ask and raises one they did not have. A date answers the only question there is—when?
* **Competences carry a year where a year says something, and never a rating.** A year is checkable; self-awarded scales are an anti-pattern. The year stands on its own line under the name, at every width.
* **The station texts follow one build.** Responsibility and scope are nominal (“Mitverantwortlich für…”); what Marcel personally achieved is a full sentence in the first person (“Ich habe … begleitet”); the trades are purely descriptive, because a business describes itself and does not claim an achievement.
* **No selling on this site, not even in a trade's description.** A trade says what it does, in what process, and stops.
* **Jurassic Jeep sits in the career**, with the two other trades. A curriculum vitae listing two of three registered trades invites the question what else is missing, and an omission weighs more than the thing omitted.
* **The passion section carries four cells**, two by two, three of which link out under “Daraus wurde”, the link carrying the name alone. Two of them point at a self-written tool: not “I built this for a client” but “for this problem I write the software myself.” The Maker cell points at krausgedruckt, a business, so the section carries both kinds of link. The destinations live in `hobbies.json`.

### Portfolio

The two businesses as towers, then one card, “Außerdem von mir.” No introductory line.

* **Two square towers**, krausgedruckt on the right and higher, krausgebaut 144 px lower. The markup carries them in reverse brand order, so the higher one comes first for a keyboard and a screen reader. On a phone krausgedruckt stands first, both 88 % wide and still offset.
* **A tower carries its name, its claim and its domain**, and no header line. The claim breaks after its first sentence, as the cover card's does. The domain is the button (“krausgebaut.de ↗”), and the button stretches over the whole tower, so the tower is the link. Type stays white on either brand.
* **The mark is the favicon glyph as a tone-on-tone wallpaper**, in the brand's deep step. Both glyphs share one outline, so one rule places either: 60 % of the tile high (34 % on a phone), a sixth out at the top and at the right. The two files are copied into `assets/marks/`; a project reads nothing from its siblings (`assets/README.md`).
* **“Außerdem von mir” is a 3 × 2 grid of cells**: the five works and a closing cell, a list on a phone. A work carries a framed screenshot, its name, its kind, one sentence and a link naming its destination without “Zu”; the link stretches over the cell. No color stripe, no categories.
* **The closing cell carries name, postal address and the document.** Contact lives in the cover card alone.

### Kontakt

The cover card without a button, then one card: the channels on the left—address, e-mail, LinkedIn, WhatsApp, response time—and the form on the right.

## Brand mark and favicons

`_logo.html.twig` is the three squares and the wordmark as one lockup, the squares running left to right in the order the brands came into being. Colors come from `fill-*` classes.

The favicons are the three brand squares on a white tile with a `neutral-300` hairline, because at 16 px `neutral-200` disappears. The squares carry the verbatim master colors, which are exactly what the tokens resolve to; the values are in `../../docs/BRAND_FAMILY.md`.

**`tridents-hanging.svg` and `tridents-standing.svg` carry the hex of the `hair` token as a literal**, as `favicon.svg` carries the brand colors: a background image never sees the theme. They move with the token.

`portrait.jpg` is a 4:5 crop of a square original.

## SEO / meta

Centralized in `base.html.twig`: canonical, Open Graph, Twitter card, sharing image `public/images/sharing.jpg`. `Person` JSON-LD in Profil's `structured_data` block, `sameAs` pointing at both sibling sites and LinkedIn. Profil's title: `Marcel Kraus · Senior Softwareentwickler iOS/Web aus Erftstadt bei Köln`; every other page: `{Page} · Marcel Kraus`. **Profil, Portfolio and Kontakt are indexable** and make up the sitemap, which reads `app.pages` like the tabs; the legal pages are `noindex,follow` and stay out of it.

The sharing image is one card on the facade: the portrait in black and white at full card height on the left; name, job title and the claim on two lines on the right; `www.marcelkraus.de` alone at the foot. **No logo and no place**, and nothing that expires. `bin/render-sharing-image` photographs it from `/sharing-image`, a page registered in dev alone that embeds the real cover card in the real stylesheet and fonts, so a change to the card reaches the image by running the script again. The file is a finished asset and gets committed.

**The job title has two forms.** “Senior Softwareentwickler iOS/Web” stands under the name in the cover card, in the page `<title>`, in the descriptions and on the sharing image; the `jobTitle` of the JSON-LD and the profile of the printed document carry “Senior Softwareentwickler” alone. The claim (“Nicht nur mit Hut. Mit Köpfchen.”) stands below the job title, broken after its first sentence on the page, on the sharing image and in the printed document.
