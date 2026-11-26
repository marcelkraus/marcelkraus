# Design

The decisions behind the site's look that the templates and the stylesheet cannot explain. What the family shares stands in `../../../docs/BRAND_FAMILY.md`.

## The design system

* **marcelkraus has a design system of its own**, outside the family's. No dark mode.
* **Nothing outside the scales**: spacing on 8, 16, 24, 48 and 96, type on four steps of Aller and one of mono, one breakpoint at 1000 px. The exceptions—a tower's name and the button's 1.5 px border, which are optical, and the header's height of 80 px on every width, as on krausgedruckt—stay the only ones. No `!important`.
* **Nothing moves**: not the facade, the cards, the towers or the arrows.
* **A color keyed to data** (a tower's brand) is selected in the stylesheet by a `data-*` attribute, never by a class assembled in the template.
* **The header holds the logo alone**, linking to `/`; one page needs no navigation.
* **The portrait turns black and white in the stylesheet**, never in the photograph, and is never mirrored.
* **GitHub and LinkedIn stand in the footer under the copyright line**, in alphabetical order, as plain text links, opening in a new tab, without icons. Their addresses live once in `app.profiles` and feed the JSON-LD `sameAs` too.

## Copy and content

* **No selling on this site itself**: it points to the two brands and may invite an inquiry there. A trade says what it does, in what process, and stops.
* **The station texts follow one build**, in the printed curriculum vitae. Responsibility and scope are nominal (“Mitverantwortlich für…”); what Marcel personally achieved is a full sentence in the first person (“Ich habe … begleitet”); the trades are purely descriptive, because a business describes itself and does not claim an achievement.
* **Every registered trade sits in the career**, Jurassic Jeep included: a curriculum vitae listing two of three invites the question what else is missing.
* **Several roles at one employer are nested**, because two stations side by side read as two jobs rather than one development.
* **The availability names one month and no year**, and stands in the printed curriculum vitae alone, next to the date it carries. The word *freigestellt* appears nowhere: it answers a question the reader did not ask.
* **A link names its destination**—the name or the domain—except the link of a work, which invites by kind (see `docs/CONTENT.md`).

## Assets

* **krausgebaut, the main trade, stands higher, on the right; krausgedruckt lower, on the left**, by two gaps. The markup keeps brand order, so the higher tower comes first for a keyboard and a screen reader; below 1000 px the two stack in that order at full width, without an offset.
* **The homepage fills the cover card's `more` slot** with a short introduction under the claim; the sharing image fills it with the domain.
* **The favicon tile carries a `neutral-300` hairline**, because at 16 px `neutral-200` disappears.
* **`tridents-hanging.svg` and `tridents-standing.svg` carry the hex of the `hair` token as a literal**: a background image never sees the theme. They move with the token.
* **The brand marks are copied into `assets/marks/`**; a project reads nothing from its siblings.
* **The sharing image carries no logo, no place and nothing that expires.** `bin/render-sharing-image` photographs it from `/sharing-image`, so a change to the cover card reaches it by running the script again; the file is committed. The trades and the claim sit there one step down the scale, on `t4`, so the trades fit on two lines.
* **The job title differs by place**: “Softwareentwickler iOS/Web” and “Internet-, IT- & 3D-Druck-Dienstleister” on two lines under the name in the cover card, “Softwareentwickler iOS/Web und Internet-, IT- & 3D-Druck-Dienstleister” in the JSON-LD `jobTitle`, and “Senior Softwareentwickler” in the printed document, which serves applications.
* **The JSON-LD is a lean `Person`**: name, job title, `sameAs` for GitHub and LinkedIn, and the two brands as `brand`, built from `brands.json`.
