# Design

The decisions behind the site's look that the templates and the stylesheet cannot explain. What the family shares stands in `../../../docs/BRAND_FAMILY.md`.

## The design system

* **marcelkraus has a design system of its own**, outside the family's. No dark mode.
* **Nothing outside the scales**: spacing on 8, 16, 24, 48 and 96, type on four steps of Aller and two of mono, one breakpoint at 1000 px. The exceptions—a tower's name, the station marker, the button's 1.5 px border—are optical and stay the only ones. No `!important`.
* **Nothing moves**: not the facade, the cards, the towers or the arrows.
* **A color keyed to data** (a station's marker, a tower's brand) is selected in the stylesheet by a `data-*` attribute, never by a class assembled in the template.
* **The active tab follows the path, not the route**, so the contact page re-rendered by a failed POST keeps its tab.
* **The portrait turns black and white in the stylesheet**, never in the photograph, and is never mirrored.
* **“Lebenslauf als PDF ↗” opens in a new tab**—the one case besides another website—because the document is served inline and would otherwise replace the page.
* **The contact channels stand on `/kontakt` alone**; LinkedIn is among them because it belongs on a curriculum vitae and not on a sales page.

## Copy and content

* **No selling on this site**, not even in a trade's description. A trade says what it does, in what process, and stops.
* **The station texts follow one build.** Responsibility and scope are nominal (“Mitverantwortlich für…”); what Marcel personally achieved is a full sentence in the first person (“Ich habe … begleitet”); the trades are purely descriptive, because a business describes itself and does not claim an achievement.
* **Every registered trade sits in the career**, Jurassic Jeep included: a curriculum vitae listing two of three invites the question what else is missing.
* **Several roles at one employer are nested**, because two stations side by side read as two jobs rather than one development.
* **No carousel for the career**: it shows less on a desktop than on a phone and keeps hidden cards in the tab order.
* **The availability names one month and no year**, and stands on Profil alone. The document carries its own date, the page is read the day it is served. The word *freigestellt* appears nowhere: it answers a question the reader did not ask.
* **Competences carry a year where a year says something, and never a rating.** A year is checkable; self-awarded scales are an anti-pattern.
* **A link names its destination**—the name or the domain—never an invitation such as “Zu …”.

## Assets

* **The towers stand in reverse brand order in the markup**, so the higher one comes first for a keyboard and a screen reader.
* **The favicon tile carries a `neutral-300` hairline**, because at 16 px `neutral-200` disappears.
* **`tridents-hanging.svg` and `tridents-standing.svg` carry the hex of the `hair` token as a literal**: a background image never sees the theme. They move with the token.
* **The brand marks are copied into `assets/marks/`**; a project reads nothing from its siblings.
* **The sharing image carries no logo, no place and nothing that expires.** `bin/render-sharing-image` photographs it from `/sharing-image`, so a change to the cover card reaches it by running the script again; the file is committed.
* **The job title has two forms**: “Senior Softwareentwickler iOS/Web” wherever a reader sees it, “Senior Softwareentwickler” alone in the JSON-LD `jobTitle` and the printed document.
