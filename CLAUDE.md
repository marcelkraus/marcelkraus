# marcelkraus

Personal website of Marcel Kraus (https://www.marcelkraus.de), German only, light only. **Keep the site simple**—that settles every case of doubt.

| # | Page | Path | Job |
| --- | --- | --- | --- |
| 1 | Homepage | `/` | The hub of the brand family: krausgebaut and krausgedruckt as two towers, everything else in one card |
| 2 | Lebenslauf | `/lebenslauf` | The printed curriculum vitae, generated from the content files and linked nowhere: Marcel hands the link out himself |

Plus imprint and privacy policy. The site sells nothing itself; client work and references live on krausgebaut.

## Rules

* **The content lives in `config/content/*.json`**; editing it needs no code change.
* **The printed curriculum vitae has exactly two pages.** A test holds it; run it after any change to the content files.
* **No print stylesheet**—`/lebenslauf` is the printed version.
* **The typography plugin stays loaded though unused**, because both siblings carry it; it goes in all three at once or not at all.
* **The homepage speaks in the first person and addresses the reader as “Sie”**, and it sells nothing itself—it points to the two brands and may invite an inquiry there; the sales pitch belongs on the two brands.

## Documents

| # | Document | Read before |
| --- | --- | --- |
| 1 | `docs/DESIGN.md` | touching a template, the stylesheet, a partial, the logo, a favicon, the sharing image or the head data |
| 2 | `docs/CONTENT.md` | editing a content file |
| 3 | `docs/CURRICULUM_VITAE.md` | touching `/lebenslauf`, its template or its assets |
| 4 | `docs/DEPLOYMENT.md` | touching the server, a hostname or `public/.htaccess` |

## Agent skills

Issues and specs live as local markdown files under `.scratch/` (`docs/agents/issue-tracker.md`). Triage uses the five canonical roles (`docs/agents/triage-labels.md`). Domain docs are single-context: one `GLOSSARY.md` and `docs/adr/` at the repository root (`docs/agents/domain.md`).
