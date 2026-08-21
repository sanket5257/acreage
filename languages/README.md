# Translation files

Drop `.po`, `.mo` and the `acreage.pot` template here.

`acreage.pot` is generated in Phase M5 with:

    wp i18n make-pot . languages/acreage.pot --domain=acreage

A translator opens the `.pot` in Poedit, saves `acreage-fr_FR.po` / `.mo` here,
and WordPress loads it automatically when the site language is French.
