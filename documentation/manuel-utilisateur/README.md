# Manuel utilisateur VRP Plan (LaTeX)

Manuel « book report » au format PDF, orienté **parcours nouvel utilisateur**, avec captures d'écran et encadrés workflow.

## Contenu

| Chapitre | Sujet |
|----------|--------|
| 1 | Introduction et contextes métier |
| 2 | Première connexion (landing, demande d'accès, login) |
| 3 | Interface et navigation |
| 4 | Mise en place (programmes → école → cours → groupes) |
| 5 | Agenda et planning |
| 6 | Facturation par école (`#billing`) |
| 7 | Trésorerie et rapprochement |
| 8 | Annexes (captures, compilation, dépannage) |

## Prérequis

- **XeLaTeX** (`xelatex` ou `latexmk -xelatex`)
- **PDFLaTeX** + Palatino (`pdflatex`, inclus dans TeX Live / MacTeX)
- Pour les captures : Google Chrome + serveur local `php artisan serve`

## Compiler le PDF

```bash
cd documentation/manuel-utilisateur
make
```

Le fichier produit est `manuel-utilisateur-vrp.pdf`.

## Captures d'écran

Trois captures publiques sont fournies (landing, login, demande d'accès). Pour les écrans authentifiés :

```bash
cd documentation/manuel-utilisateur
npm install puppeteer-core
node scripts/capture-screenshots.mjs
make
```

Variables optionnelles : `VRP_SCREENSHOT_BASE`, `VRP_SCREENSHOT_EMAIL`, `VRP_SCREENSHOT_PASSWORD`, `CHROME_PATH`.

## Structure

```
manuel-utilisateur/
├── main.tex
├── macros.tex
├── metadata.tex
├── Makefile
├── chapters/
├── screenshots/
└── scripts/capture-screenshots.mjs
```

## Voir aussi

- `documentation/fr/v2-interface-utilisateur.md`
- `documentation/fr/README.md`
