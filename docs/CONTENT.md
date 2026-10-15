# Content

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

The two towers on Portfolio, in the order the brands came into being: `name`, `claim` (one line per sentence), `url`, `domain` (the label of the button), `mark` (a file in `assets/marks/`) and `colorToken`, which the tower hands to the stylesheet as `data-brand` and which selects the brand tokens there.

### works.json

The cells of “Außerdem von mir,” in the rendered order: `name`, `kind`, `description` (one sentence), `url`, `urlLabel` and `image`.

* **`urlLabel` names the destination**—the domain, or `App Store`—never an invitation such as “Zu …”.
* **`image` is a file under `public/images/works/`**, a square screenshot of 640 × 640. The screenshots are made once and committed; the page never fetches anything from a work at runtime, and a test asserts it.

### Structured data

`knowsAbout` in the JSON-LD is **built from the content files**, not hardcoded: the skills carry the professional terms, the hobbies the private ones. Sorted and deduplicated, so reordering a content file does not change the output.
