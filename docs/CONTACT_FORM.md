# Contact form

The mechanism is in `../../../docs/WEB_STACK.md`. Specific here:

* Fields: name, e-mail, company, phone, message; required are name, e-mail and message. **`Firma` means the sender's employer.**
* Success ⇒ mail to `CONTACT_TO` (reply-to = sender), PRG redirect to `/kontakt` with flash `contact_success`; the confirmation replaces the form. An error renders the contact page with the input kept and status 422.
* **Exactly one contact form on the site**, on `/kontakt`, reached through the tab and through “Nachricht schreiben →.”
* **The e-mail address is validated twice**, so `Assert\Email` runs in `strict` mode: the loose default accepts addresses that `Mime\Address` then rejects with an `InvalidArgumentException`, which the transport catch does not cover and which would land as a bare error page.
* The legal mailbox is `mail+legal@marcelkraus.de`.
* **No downloadable references.** An Arbeitszeugnis carries the letterhead, function and signature of a named third party; publishing it publishes someone else's data, and once indexed it stays indexed. One sentence on the contact page says they are sent on request. No protected area, no signed links, no checkbox in the form.

