# The printed curriculum vitae

`GET /lebenslauf` renders the document from the same content files the page reads. **Not a second document**—a hand-kept PDF drifts against the site, and then there are two truths about one career. `ContentRepository` makes that structural: one reader, two outputs.

Delivered **inline**, so it opens in the browser; the file name travels along for whoever saves it. `X-Robots-Tag: noindex`, because the file duplicates Profil and carries the postal address—the indexed truth stays the page. A `Disallow` would be worse than useless: a path never fetched is a header never read.

**Two pages is a requirement, not a preference**, and a test asserts it. The layout sits close enough to the limit that one longer station description pushes it over, and nothing else would notice.

**Merge with `pdfunite`, never with Preview.** An application is the cover letter plus this document in one file. Preview rewrites the embedded fonts when it saves and loses the character map of the mPDF subsets: the pages still look right and print right, but the text extracts as garbage, so search, copying the address and an applicant tracking system all fail. Dragging the PDF into Pages is worse still—Pages fits the page into its text column and scales it to 93 %. `pdfunite anschreiben.pdf lebenslauf.pdf bewerbung.pdf` keeps the fonts, the text and the logo position untouched.

**It replaces a print stylesheet**, which cannot hold a letterhead grid to the millimeter while every browser sets its own margins and running heads.

## Page geometry, the measured stationery

| # | | |
| --- | --- | --- |
| 1 | Top, every page | 55 mm—20 mm paper edge + 15 mm logo + 20 mm clearance |
| 2 | Left / right | 20 mm |
| 3 | Bottom | 20 mm—the stationery fixes only the top and the sides |
| 4 | First column | 30.5 mm, half the logo width (61.03 ÷ 2) |
| 5 | Text column | starts at 50.5 mm, in every section |
| 6 | Right column of the head | starts at 129 mm, under the left edge of the logo |

The lockup lives in the page header, which is what makes it repeat without the body knowing about it.

## Two scales, and nothing outside them

**Type**, five steps: 24 pt title, 12 pt byline, 10 pt names, 9 pt running text and contact values, 8 pt the technical layer. The fifth step earns its place—with names and running text sharing 10 pt the document runs to three pages.

**Spacing**, four steps, all multiples of 1.5 mm: 0 inside an entry (the lines sit flush, as on the page), 1.5 mm between contact rows, 3 mm between siblings, 9 mm between blocks. The one value outside both scales is 0.5 mm above the period column—an optical nudge, not a distance between things.

## The head, and what it leaves out

The right column carries Datum, Anschrift, Telefon, Verfügbar, E-Mail and `Homepage`, then the QR code. The label names the page and not one of its tabs, since the tabs may trade places; the one address behind it is where the portfolio and the other profiles are, which is why there is **no LinkedIn row and no second QR**. Two codes side by side cannot be told apart without reading their labels, and a reader who scans the wrong one blames the document. One code is a device; two are a pattern that invites a third.

**No photograph.** The page carries the portrait one scan away, and a 4:5 image at any usable size costs 40 to 60 mm of a first page that has 23 mm to spare. Adding it means a third page or a shorter profile, and the profile is the only part of the document that says in Marcel's own words who he is.

A quiet page number closes each page, centered in the bottom margin—the band runs 277 to 297 mm, so the figure sits at 287 mm. Two pages do not need it to find their way back together; a sheet that falls out of a folder does.

## What the document does differently from the page

* **No section numbers**, as on the page.
* **No color but the logo's.** The byline, the positions and the square before a section are the document's ink, `neutral-900` (`#171717`) like the title and the company names—not the hub's `--color-ink`, since a second near-black a few shades apart would be noise on paper. The purple stays in the logo alone, and a test asserts it; no station carries a brand color.
* **No technology tags.** They are a device of the page.
* **No years in the competences.** The document is read in one sitting; the figures only crowd a line that is already dense.
* **Three blocks instead of one timeline**—Berufserfahrung, Selbstständigkeit, Ausbildung—grouped on the `marker` field, which needs no extra data.
* **A short profile** that exists nowhere on the page; the cover card carries the claim, which is a different thing. It lives in `config/services.yaml` because a content file would only be ignored by the page.

## mPDF has two rules that shape the whole template

1. **Every distance is cell padding or a spacer element.** Block margins inside a table cell are dropped, and an empty spacer renders only sometimes.
2. **Every area is an image.** Neither a background nor a border on a block inside a cell is drawn. The square before an eyebrow is `marker-ink.svg`; the rail beside the nested roles is a cell border.

`shrink_tables_to_fit` is **off**. It silently rescales individual tables mPDF thinks too wide, which produces two different body sizes on one page. Every column here has an explicit width, so nothing needs rescuing.

## Assets

`logo.svg`, `qr-code.svg` and `marker-ink.svg` under `public/images/`, the template's own stylesheet and the footer in `CurriculumVitaeRenderer` carry literal hex, as `favicon.svg` does: mPDF parses its own stylesheet and never sees the theme, so there is no token to bind to. The values are the ones the tokens resolve to and have to move with them.

**The QR code has no quiet zone of its own**—the view box is cropped to the 25 data modules, because the white paper around it is the quiet zone and the built-in border only pushes the ink off the column edge. It carries `width`/`height`, for the same reason `favicon.svg` must.

**Two typefaces, each with one job:** Aller carries names, headings and every sentence, JetBrains Mono the technical layer—eyebrows, periods, locations, tags, labels and the dateline. Aller is a static TrueType file and comes straight from the web font directory; it is embedded in two weights, so the claim is bold rather than semi-bold.

**The nested roles share one rail**, a single bordered cell: a rail with gaps would say the roles do not belong to the company above.

**JetBrains Mono is embedded from `assets/fonts/`**, two static instances derived from the variable web font—mPDF can read neither a variable font nor Brotli. They are never served; `assets/README.md` carries the command that rebuilds them.
