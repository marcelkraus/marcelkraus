# assets

Build inputs that are **not** served to the browser. `public/` is the document
root, so anything here is reachable only from PHP.

## fonts

`jetbrains-mono-regular.ttf` and `jetbrains-mono-bold.ttf` are static
instances of `public/fonts/jetbrains-mono-latin-wght-normal.woff2` at weights
400 and 700. The web font is variable and compressed with Brotli; mPDF can
embed neither, so the printed curriculum vitae needs these two files.

They are derived, never edited. To rebuild them after a font update:

```bash
python3 - <<'PY'
from fontTools.ttLib import TTFont
from fontTools.ttLib.woff2 import decompress
from fontTools.varLib import instancer

decompress('public/fonts/jetbrains-mono-latin-wght-normal.woff2', '/tmp/variable.ttf')
for weight, name in ((400, 'regular'), (700, 'bold')):
    font = TTFont('/tmp/variable.ttf')
    instancer.instantiateVariableFont(font, {'wght': weight}, inplace=True, updateFontNames=True)
    font.save('assets/fonts/jetbrains-mono-%s.ttf' % name)
PY
```

JetBrains Mono is licensed under the SIL Open Font License, which permits the
derivation.
